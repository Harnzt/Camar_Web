<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentVerification extends Model
{
    /**
     * Extensions the browser can render inline, mapped to their safe MIME type.
     * Anything else (including SVG) is download-only.
     */
    public const PREVIEWABLE_TYPES = [
        'pdf' => 'application/pdf',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'webp' => 'image/webp',
        'gif' => 'image/gif',
    ];

    protected $fillable = [
        'user_id', 'document_type', 'document_path', 'status',
        'reviewed_by', 'reviewed_at', 'rejection_reason', 'notes',
    ];

    protected $casts = ['reviewed_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isProjectDocument(): bool
    {
        return str_starts_with($this->document_type, 'project_');
    }

    public function getFileExtensionAttribute(): string
    {
        return strtolower(pathinfo($this->document_path, PATHINFO_EXTENSION));
    }

    public function getPreviewMimeTypeAttribute(): ?string
    {
        return self::PREVIEWABLE_TYPES[$this->file_extension] ?? null;
    }

    /**
     * "pdf", "image", or null when the file cannot be previewed inline.
     */
    public function getPreviewKindAttribute(): ?string
    {
        $mime = $this->preview_mime_type;

        if ($mime === null) {
            return null;
        }

        return $mime === 'application/pdf' ? 'pdf' : 'image';
    }
}
