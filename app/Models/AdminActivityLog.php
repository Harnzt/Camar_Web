<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class AdminActivityLog extends Model
{
    public const TARGET_LABELS = [
        User::class => 'Akun',
        Project::class => 'Proyek',
        Order::class => 'Transaksi',
        DocumentVerification::class => 'Dokumen',
        Role::class => 'Role',
    ];

    protected $fillable = [
        'admin_id', 'action', 'target_type', 'target_id', 'description',
        'old_values', 'new_values', 'ip_address', 'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function admin()
    {
        // Deleted administrators must stay visible in the audit trail.
        return $this->belongsTo(User::class, 'admin_id')->withTrashed();
    }

    public function target()
    {
        return $this->morphTo();
    }

    public function getTargetLabelAttribute(): string
    {
        if (! $this->target_type) {
            return '-';
        }

        $label = self::TARGET_LABELS[$this->target_type] ?? class_basename($this->target_type);

        return $this->target_id ? "{$label} #{$this->target_id}" : $label;
    }

    /**
     * Super admin sees every log; auditors see auditor activity only;
     * other roles see nothing.
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        if ($user->isAuditor()) {
            return $query->whereHas('admin', fn (Builder $adminQuery) => $adminQuery->where('role', 'auditor'));
        }

        return $query->whereRaw('1 = 0');
    }

    /**
     * @param  array{action?: ?string, admin_id?: ?string, role?: ?string, target_type?: ?string, target_id?: ?string, date_from?: ?string, date_to?: ?string}  $filters
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['action'] ?? null, fn (Builder $q, string $action) => $q->where('action', 'like', "%{$action}%"))
            ->when($filters['admin_id'] ?? null, fn (Builder $q, string $adminId) => $q->where('admin_id', $adminId))
            ->when($filters['role'] ?? null, fn (Builder $q, string $role) => $q->whereHas(
                'admin',
                fn (Builder $adminQuery) => $adminQuery->where('role', $role)
            ))
            ->when($filters['target_type'] ?? null, fn (Builder $q, string $type) => $q->where('target_type', $type))
            ->when($filters['target_id'] ?? null, fn (Builder $q, string $id) => $q->where('target_id', $id))
            ->when($filters['date_from'] ?? null, fn (Builder $q, string $from) => $q->where(
                'created_at',
                '>=',
                Carbon::parse($from)->startOfDay()
            ))
            ->when($filters['date_to'] ?? null, fn (Builder $q, string $to) => $q->where(
                'created_at',
                '<=',
                Carbon::parse($to)->endOfDay()
            ));
    }
}
