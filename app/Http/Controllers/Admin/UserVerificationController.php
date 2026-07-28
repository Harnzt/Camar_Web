<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentVerification;
use App\Models\User;
use App\Services\AdminAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserVerificationController extends Controller
{
    public function __construct(private readonly AdminAuditService $audit)
    {
    }

    public function index(Request $request)
    {
        $query = User::query()
            ->whereIn('role', ['buyer', 'seller'])
            ->withCount([
                'documentVerifications',
                'documentVerifications as pending_documents_count' => fn ($q) => $q->where('status', 'pending'),
            ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('company_name', 'like', "%{$search}%"));
        }

        $users = $query->latest()->paginate(12)->withQueryString();

        return view('main_page.admin-panel.users.index', compact('users'));
    }

    public function show(User $user)
    {
        abort_if($user->isAdministrator(), 404);

        $user->load(['documentVerifications.reviewer']);

        return view('main_page.admin-panel.users.show', compact('user'));
    }

    public function updateStatus(Request $request, User $user)
    {
        abort_if($user->isAdministrator(), 403);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'verified', 'rejected', 'suspended'])],
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        if (in_array($validated['status'], ['rejected', 'suspended'], true) && blank($validated['reason'])) {
            return back()->withErrors(['reason' => 'Alasan wajib diisi untuk penolakan atau penonaktifan.']);
        }

        if ($validated['status'] === 'verified') {
            $unapproved = $user->documentVerifications()
                ->where('status', '!=', 'approved')
                ->count();

            if ($unapproved > 0) {
                return back()->withErrors([
                    'status' => 'Semua dokumen harus disetujui sebelum akun dapat diverifikasi.',
                ]);
            }
        }

        $old = $user->only([
            'status', 'verified_by', 'verified_at', 'rejection_reason',
            'suspended_at', 'suspension_reason',
        ]);

        $user->forceFill([
            'status' => $validated['status'],
            'verified_by' => $validated['status'] === 'verified' ? auth()->id() : $user->verified_by,
            'verified_at' => $validated['status'] === 'verified' ? now() : $user->verified_at,
            'rejection_reason' => $validated['status'] === 'rejected' ? $validated['reason'] : null,
            'suspended_at' => $validated['status'] === 'suspended' ? now() : null,
            'suspension_reason' => $validated['status'] === 'suspended' ? $validated['reason'] : null,
        ])->save();

        $this->audit->log(
            'user.status.updated',
            "Status akun {$user->email} diubah menjadi {$validated['status']}.",
            $user,
            $old,
            $user->only(array_keys($old))
        );

        return back()->with('success', 'Status akun berhasil diperbarui.');
    }

    public function updateDocument(Request $request, DocumentVerification $document)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'approved', 'rejected', 'revision_required'])],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        if (in_array($validated['status'], ['rejected', 'revision_required'], true) && blank($validated['notes'])) {
            return back()->withErrors(['notes' => 'Catatan wajib diisi untuk dokumen yang ditolak atau perlu revisi.']);
        }

        $old = [];
        $userStatusChange = null;

        DB::transaction(function () use ($document, $validated, &$old, &$userStatusChange) {
            $document->loadMissing('user');

            $old = $document->only(['status', 'reviewed_by', 'reviewed_at', 'rejection_reason', 'notes']);

            $document->update([
                'status' => $validated['status'],
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'rejection_reason' => $validated['status'] === 'rejected' ? $validated['notes'] : null,
                'notes' => $validated['notes'],
            ]);

            $document->refresh()->load('user');
            $userStatusChange = $this->syncUserStatusFromDocuments($document->user);
        });

        $this->audit->log(
            'document.reviewed',
            "Dokumen {$document->document_type} milik {$document->user->email} ditinjau.",
            $document,
            $old,
            $document->only(array_keys($old))
        );

        if ($userStatusChange) {
            $this->audit->log(
                'user.status.synced_from_documents',
                "Status akun {$document->user->email} disesuaikan berdasarkan hasil verifikasi dokumen.",
                $document->user,
                $userStatusChange['old'],
                $userStatusChange['new']
            );
        }

        return back()->with('success', 'Status dokumen berhasil diperbarui.');
    }

    private function syncUserStatusFromDocuments(?User $user): ?array
    {
        if (! $user || $user->isAdministrator() || $user->isSuspended()) {
            return null;
        }

        $documents = $user->documentVerifications()
            ->get(['id', 'status', 'reviewed_at', 'rejection_reason', 'notes', 'updated_at']);

        if ($documents->isEmpty()) {
            return null;
        }

        $nextStatus = null;
        $reason = null;

        if ($documents->contains(fn (DocumentVerification $item) => $item->status === 'rejected')) {
            $latestRejected = $documents
                ->where('status', 'rejected')
                ->sortByDesc(fn (DocumentVerification $item) => $item->reviewed_at?->timestamp ?? $item->updated_at?->timestamp ?? 0)
                ->first();

            $nextStatus = 'rejected';
            $reason = $latestRejected?->rejection_reason ?: $latestRejected?->notes;
        } elseif ($documents->every(fn (DocumentVerification $item) => $item->status === 'approved')) {
            $nextStatus = 'verified';
        } elseif (in_array($user->status, ['verified', 'rejected'], true)) {
            $nextStatus = 'pending';
        }

        if (! $nextStatus || $user->status === $nextStatus) {
            return null;
        }

        $old = $user->only([
            'status', 'verified_by', 'verified_at', 'rejection_reason',
            'suspended_at', 'suspension_reason',
        ]);

        $user->forceFill([
            'status' => $nextStatus,
            'verified_by' => $nextStatus === 'verified' ? auth()->id() : $user->verified_by,
            'verified_at' => $nextStatus === 'verified' ? now() : $user->verified_at,
            'rejection_reason' => $nextStatus === 'rejected' ? $reason : null,
            'suspended_at' => null,
            'suspension_reason' => null,
        ])->save();

        return [
            'old' => $old,
            'new' => $user->only(array_keys($old)),
        ];
    }

    public function download(DocumentVerification $document)
    {
        abort_unless(Storage::disk('private')->exists($document->document_path), 404);

        return Storage::disk('private')->download(
            $document->document_path,
            basename($document->document_path)
        );
    }
}
