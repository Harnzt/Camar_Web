<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'company_name',
        'industry',
        'position',
        'job_title',
        'role',
        'account_category',
        'status',
        'verified_by',
        'verified_at',
        'rejection_reason',
        'suspended_at',
        'suspension_reason',
        'documents',
        'profile_photo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'documents'         => 'array',   
        'verified_at'       => 'datetime',
        'suspended_at'      => 'datetime',
    ];

    // =========================================================
    // ACCESSORS
    // =========================================================
    public function getProfilePhotoUrlAttribute(): string 
    {
        if ($this->profile_photo && Storage::disk('public')->exists($this->profile_photo)) {
            return url('/storage/' . ltrim($this->profile_photo, '/'));
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=67C090&color=fff&size=200';
    }

    public function getDocumentPath(string $type): ?string 
    {
        if (is_array($this->documents) && isset($this->documents[$type])) {
            return $this->documents[$type];
        }
        return null;
    }

    public function hasDocument(string $type): bool 
    {
        return $this->getDocumentPath($type) !== null;
    }

    // =========================================================
    // STATUS HELPERS
    // =========================================================
    public function isPending(): bool { return $this->status === 'pending'; }
    public function isVerified(): bool { return $this->status === 'verified'; }
    public function isRejected(): bool { return $this->status === 'rejected'; }
    public function isSuspended(): bool { return $this->status === 'suspended'; }

    // =========================================================
    // ROLE HELPERS
    // =========================================================
    public function isBuyer(): bool { return $this->role === 'buyer'; }
    public function isSeller(): bool { return $this->role === 'seller'; }
    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isSuperAdmin(): bool { return $this->role === 'super_admin'; }
    public function isAdministrator(): bool { return in_array($this->role, ['admin', 'super_admin'], true); }
    public function isCompany(): bool { return $this->account_category === 'company'; }
    public function isPersonal(): bool { return $this->account_category === 'personal'; }

    public function getCartCount()
    {
        return \App\Models\CartItem::where('user_id', $this->id)->count();
    }

    public function getRoleLabelAttribute(): string 
    {
        return match($this->role) {
            'buyer'  => 'Buyer',
            'seller' => 'Seller',
            'admin' => 'Admin',
            'super_admin' => 'Super Admin',
            default  => 'User',
        };
    }

    public function getCategoryLabelAttribute(): string 
    {
        return match($this->account_category) {
            'company'  => 'Perusahaan',
            'personal' => 'Individu',
            default    => '-',
        };
    }

    public function getStatusLabelAttribute(): string 
    {
        return match($this->status) {
            'pending'  => 'Menunggu Verifikasi',
            'verified' => 'Terverifikasi',
            'rejected' => 'Ditolak',
            'suspended' => 'Dinonaktifkan',
            default    => '-',
        };
    }

    public function getStatusColorAttribute(): string 
    {
        return match($this->status) {
            'pending'  => 'warning',
            'verified' => 'success',
            'rejected' => 'danger',
            'suspended' => 'dark',
            default    => 'secondary',
        };
    }

    // =========================================================
    // RELATIONS (RELASI MODEL BARU)
    // =========================================================

    /**
     * User sebagai Buyer memiliki banyak pesanan/pembelian (Order)
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id', 'id');
    }

    /**
     * Riwayat kalkulasi emisi milik buyer.
     */
    public function emissionCalculations(): HasMany
    {
        return $this->hasMany(EmissionCalculation::class, 'user_id', 'id');
    }

    public function hasEmissionCalculation(): bool
    {
        return $this->emissionCalculations()->exists();
    }

    /**
     * User sebagai Seller memiliki banyak portofolio proyek carbon
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'seller_id');
    }

    public function documentVerifications(): HasMany
    {
        return $this->hasMany(DocumentVerification::class);
    }

    public function reviewedDocuments(): HasMany
    {
        return $this->hasMany(DocumentVerification::class, 'reviewed_by');
    }

    public function adminActivityLogs(): HasMany
    {
        return $this->hasMany(AdminActivityLog::class, 'admin_id');
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return Permission::query()
            ->where('slug', $permission)
            ->whereHas('roles', fn ($query) => $query->where('slug', $this->role))
            ->exists();
    }
}
