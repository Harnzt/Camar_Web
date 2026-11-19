{{-- Kartu dokumen dengan tombol pratinjau (PDF/gambar) dan unduh. --}}
<article class="document-card">
    <div class="document-head">
        <div class="list-icon"><i aria-hidden="true" class="fas {{ $document->preview_kind === 'image' ? 'fa-file-image' : ($document->preview_kind === 'pdf' ? 'fa-file-pdf' : 'fa-file-lines') }}"></i></div>
        <div class="grow">
            <strong>{{ $title }}</strong>
            <span>{{ basename($document->document_path) }}</span>
        </div>
        @if($showStatus ?? true)
            <span class="status {{ $document->status }}">{{ str_replace('_', ' ', $document->status) }}</span>
        @endif
    </div>
    <div class="document-actions">
        @if($document->preview_kind)
            <button
                type="button"
                class="btn btn-primary btn-sm"
                data-document-preview
                data-preview-url="{{ route('admin.documents.preview', $document) }}"
                data-download-url="{{ route('admin.documents.download', $document) }}"
                data-preview-kind="{{ $document->preview_kind }}"
                data-preview-title="{{ $title }}"
            ><i aria-hidden="true" class="fas fa-eye"></i> Pratinjau</button>
        @endif
        <a href="{{ route('admin.documents.download', $document) }}" class="btn btn-light btn-sm"><i aria-hidden="true" class="fas fa-download"></i> Unduh dokumen</a>
    </div>
    @if($document->notes)
        <p class="muted document-note">{{ $document->notes }}</p>
    @endif
</article>
