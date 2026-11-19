<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\User;
use App\Services\AdminAuditService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    private const ROLE_LABELS = [
        'super_admin' => 'Super Admin',
        'admin' => 'Admin',
        'auditor' => 'Auditor',
    ];

    public function __construct(private readonly AdminAuditService $audit) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $filters = $this->validatedFilters($request);

        $logs = AdminActivityLog::with('admin')
            ->visibleTo($user)
            ->filter($filters)
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('main_page.admin-panel.audit.index', [
            'logs' => $logs,
            'filters' => $filters,
            'actors' => $this->actorOptions($user),
            'roleOptions' => $this->roleOptions($user),
            'targetOptions' => AdminActivityLog::TARGET_LABELS,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $user = $request->user();
        $filters = $this->validatedFilters($request);

        $query = AdminActivityLog::with('admin')
            ->visibleTo($user)
            ->filter($filters)
            ->latest();

        $this->audit->log(
            'audit.exported',
            'Audit log diekspor ke CSV.',
            null,
            [],
            ['filters' => array_filter($filters), 'rows' => (clone $query)->count()]
        );

        $fileName = 'audit-log-camar-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM so Excel renders Indonesian characters correctly.
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [
                'Waktu', 'Pelaku', 'Email Pelaku', 'Role Pelaku', 'Tindakan', 'Target',
                'Deskripsi', 'IP Address', 'User Agent',
            ], ';');

            $query->lazy(500)->each(function (AdminActivityLog $log) use ($handle) {
                fputcsv($handle, array_map([$this, 'sanitizeCsvCell'], [
                    $log->created_at?->format('Y-m-d H:i:s'),
                    $log->admin?->name ?? 'Sistem',
                    $log->admin?->email ?? '-',
                    $log->admin?->role_label ?? '-',
                    $log->action,
                    $log->target_label,
                    $log->description,
                    $log->ip_address,
                    $log->user_agent,
                ]), ';');
            });

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Prevent spreadsheet formula injection from user-controlled text.
     */
    private function sanitizeCsvCell(mixed $value): string
    {
        $value = (string) ($value ?? '');

        return preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }

    private function validatedFilters(Request $request): array
    {
        $validated = $request->validate([
            'action' => ['nullable', 'string', 'max:100'],
            'admin_id' => ['nullable', 'integer'],
            'role' => ['nullable', Rule::in(array_keys(self::ROLE_LABELS))],
            'target_type' => ['nullable', Rule::in(array_keys(AdminActivityLog::TARGET_LABELS))],
            'target_id' => ['nullable', 'integer', 'min:1'],
            'date_from' => ['nullable', 'date'],
            'date_to' => array_filter([
                'nullable',
                'date',
                $request->filled('date_from') ? 'after_or_equal:date_from' : null,
            ]),
        ]);

        return array_map(
            fn ($value) => $value === null ? null : (string) $value,
            array_merge(array_fill_keys(
                ['action', 'admin_id', 'role', 'target_type', 'target_id', 'date_from', 'date_to'],
                null
            ), $validated)
        );
    }

    private function visibleRoles(User $user): array
    {
        return $user->isSuperAdmin() ? array_keys(self::ROLE_LABELS) : ['auditor'];
    }

    private function roleOptions(User $user): array
    {
        return array_intersect_key(self::ROLE_LABELS, array_flip($this->visibleRoles($user)));
    }

    private function actorOptions(User $user)
    {
        return User::withTrashed()
            ->whereIn('role', $this->visibleRoles($user))
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role', 'deleted_at']);
    }
}
