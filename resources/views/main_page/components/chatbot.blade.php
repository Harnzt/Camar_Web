<link rel="stylesheet" href="{{ asset('css/chatbot.css') }}?v={{ filemtime(public_path('css/chatbot.css')) }}">

<div id="cami-chatbot" data-cami-storage-owner="{{ auth()->check() ? 'user_' . auth()->id() : 'guest' }}">
    <button id="cami-toggle-btn" type="button" aria-label="Buka chat dengan Cami" 
        style="position: fixed; bottom: 30px; right: 30px; z-index: 99999; cursor: pointer; border: none; background: transparent;">
        <img src="{{ asset('images/cami.png') }}" alt="Cami" style="width: 60px; height: 60px; display: block;">
        <span id="cami-notif-dot"></span>
    </button>

    <div id="cami-panel" class="cami-hidden">
        <div id="cami-header">
            <img src="{{ asset('images/cami.png') }}" alt="Cami" id="cami-header-avatar">
            <div id="cami-header-text">
                <p id="cami-header-name">Cami</p>
                <p id="cami-header-status"><span class="cami-dot-online"></span>CAMAR Intelligence</p>
            </div>
            <button id="cami-history-btn" type="button" aria-label="Lihat riwayat percakapan"
                aria-controls="cami-history-panel" aria-expanded="false" title="Riwayat percakapan">
                <i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i>
                <span>Riwayat</span>
            </button>
            <button id="cami-close-btn" type="button" aria-label="Tutup chat">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div id="cami-history-panel" class="cami-hidden" aria-label="Riwayat percakapan">
            <div id="cami-history-overview">
                <div class="cami-history-heading">
                    <strong>Riwayat percakapan</strong>
                    <span>Satu riwayat dibuat saat Cami ditutup dengan tombol X</span>
                </div>
                <div id="cami-history-list"></div>
            </div>
            <div id="cami-history-detail" class="cami-hidden">
                <div class="cami-history-detail-heading">
                    <button id="cami-history-back-btn" type="button">← Kembali ke daftar</button>
                    <strong id="cami-history-detail-title"></strong>
                </div>
                <div id="cami-history-messages"></div>
            </div>
        </div>
        <div id="cami-messages">
            <div class="cami-msg cami-msg-bot">
                <img src="{{ asset('images/cami.png') }}" alt="Cami" class="cami-msg-avatar">
                <div class="cami-msg-bubble cami-welcome-bubble">
                    <div class="cami-welcome-copy">
                        <strong>Apa yang ingin kamu lakukan?</strong>
                        <span>Aku bisa menghitung, menjelaskan saldo emisi, dan membantu memilih proyek CAMAR.</span>
                    </div>
                    <div class="cami-menu-list">
                        <button type="button" class="cami-response-btn cami-menu-item" data-cami-payload="hitung emisi">
                            <i class="fa-solid fa-calculator" aria-hidden="true"></i>
                            <span><strong>Hitung emisi</strong><small>Kalkulator sesuai tipe akun</small></span>
                        </button>
                        <button type="button" class="cami-response-btn cami-menu-item" data-cami-payload="berapa sisa emisi saya">
                            <i class="fa-solid fa-chart-pie" aria-hidden="true"></i>
                            <span><strong>Saldo karbon</strong><small>Emisi, offset, dan sisa saat ini</small></span>
                        </button>
                        <button type="button" class="cami-response-btn cami-menu-item" data-cami-payload="/ask_project_recommendation">
                            <i class="fa-solid fa-seedling" aria-hidden="true"></i>
                            <span><strong>Rekomendasi proyek</strong><small>Proyek sesuai profil emisi</small></span>
                        </button>
                        <button type="button" class="cami-response-btn cami-menu-item" data-cami-payload="simulasikan pembelian 1 ton kredit karbon">
                            <i class="fa-solid fa-scale-balanced" aria-hidden="true"></i>
                            <span><strong>Simulasi offset</strong><small>Perkiraan biaya dan sisa emisi</small></span>
                        </button>
                        <button type="button" class="cami-response-btn cami-menu-item" data-cami-payload="bandingkan dua proyek karbon">
                            <i class="fa-solid fa-code-compare" aria-hidden="true"></i>
                            <span><strong>Bandingkan proyek</strong><small>Harga, stok, lokasi, dan dampak</small></span>
                        </button>
                        <button type="button" class="cami-response-btn cami-menu-item" data-cami-payload="buatkan rencana pengurangan emisi saya">
                            <i class="fa-solid fa-list-check" aria-hidden="true"></i>
                            <span><strong>Carbon Coach</strong><small>Prioritas pengurangan personal</small></span>
                        </button>
                        <button type="button" class="cami-response-btn cami-menu-item" data-cami-payload="Cami bisa membantu apa saja">
                            <i class="fa-solid fa-compass" aria-hidden="true"></i>
                            <span><strong>Panduan CAMAR</strong><small>Cara memakai fitur website</small></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div id="cami-typing" class="cami-hidden">
            <img src="{{ asset('images/cami.png') }}" alt="" class="cami-msg-avatar">
            <div class="cami-typing-bubble">
                <span></span><span></span><span></span>
            </div>
        </div>

        <form id="cami-input-form" autocomplete="off">
            <input type="text" id="cami-input" placeholder="Tulis pertanyaan kamu..." maxlength="1000" required>
            <button type="submit" id="cami-send-btn" aria-label="Kirim pesan">
                <i class="fa-solid fa-paper-plane"></i>
            </button>
        </form>
    </div>
</div>

<script>
    window.CAMI_CHAT_ENDPOINT = "{{ route('chatbot.send') }}";
    window.CAMI_CSRF_TOKEN = "{{ csrf_token() }}";
    window.CAMI_AVATAR = "{{ asset('images/cami.png') }}";
    window.CAMI_PROJECTS_URL = "{{ route('projects.index') }}";
    window.CAMI_DASHBOARD_URL = "{{ route('dashboard') }}";
</script>
<script src="{{ asset('js/chatbot.js') }}?v={{ filemtime(public_path('js/chatbot.js')) }}" defer></script>