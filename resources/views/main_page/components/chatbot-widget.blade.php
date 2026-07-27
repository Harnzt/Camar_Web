<div id="cami-chatbot">

    <button id="cami-toggle-btn" type="button" aria-label="Buka chat dengan Cami">
        <img src="{{ asset('images/cami.png') }}" alt="Cami">
        <span id="cami-notif-dot"></span>
    </button>

    <div id="cami-panel" class="cami-hidden">

        <div id="cami-header">
            <img src="{{ asset('images/cami.png') }}" alt="Cami" id="cami-header-avatar">
            <div id="cami-header-text">
                <p id="cami-header-name">Cami</p>
                <p id="cami-header-status"><span class="cami-dot-online"></span>CAMAR Intelligence</p>
            </div>
            <button id="cami-close-btn" type="button" aria-label="Tutup chat">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div id="cami-messages">
            <div class="cami-msg cami-msg-bot">
                <img src="{{ asset('images/cami.png') }}" alt="Cami" class="cami-msg-avatar">
                <div class="cami-msg-bubble">Halo! Aku Cami, asisten CAMAR. Ada yang bisa aku bantu seputar carbon offset, kredit karbon, atau akun kamu?
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

<link rel="stylesheet" href="{{ asset('css/chatbot.css') }}">
<script>
    window.CAMI_CHAT_ENDPOINT = "{{ route('chatbot.send') }}";
    window.CAMI_CSRF_TOKEN = "{{ csrf_token() }}";
</script>
<script src="{{ asset('js/chatbot.js') }}" defer></script>
