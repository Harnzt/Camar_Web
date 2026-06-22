<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\AdminAuditService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjectVerificationController extends Controller
{
    public function __construct(private readonly AdminAuditService $audit)
    {
    }

    public function index(Request $request)
    {
        $query = Project::with('seller');

        if ($request->filled('status')) {
            $query->where('verification_status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('company_name', 'like', "%{$search}%")
                ->orWhere('category', 'like', "%{$search}%"));
        }

        $projects = $query->latest()->paginate(12)->withQueryString();

        return view('main_page.admin-panel.projects.index', compact('projects'));
    }

    public function show(Project $project)
    {
        $project->load(['seller', 'reviewer']);

        return view('main_page.admin-panel.projects.show', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'verification_status' => [
                'required',
                Rule::in(['pending', 'approved', 'rejected', 'revision_required']),
            ],
            'notes' => ['nullable', 'string', 'max:3000'],
        ]);

        if (in_array($validated['verification_status'], ['rejected', 'revision_required'], true)
            && blank($validated['notes'])) {
            return back()->withErrors(['notes' => 'Alasan wajib diisi untuk penolakan atau permintaan revisi.']);
        }

        $old = $project->only([
            'verification_status', 'reviewed_by', 'reviewed_at',
            'rejection_reason', 'admin_notes',
        ]);

        $project->update([
            'verification_status' => $validated['verification_status'],
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'rejection_reason' => $validated['verification_status'] === 'rejected'
                ? $validated['notes']
                : null,
            'admin_notes' => $validated['notes'],
        ]);

        $this->audit->log(
            'project.reviewed',
            "Proyek {$project->name} ditinjau dengan status {$validated['verification_status']}.",
            $project,
            $old,
            $project->only(array_keys($old))
        );

        return back()->with('success', 'Status verifikasi proyek berhasil diperbarui.');
    }
}
