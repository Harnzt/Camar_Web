(function () {
    'use strict';

    const toggleBtn = document.getElementById('cami-toggle-btn');
    const closeBtn = document.getElementById('cami-close-btn');
    const panel = document.getElementById('cami-panel');
    const messagesEl = document.getElementById('cami-messages');
    const typingEl = document.getElementById('cami-typing');
    const form = document.getElementById('cami-input-form');
    const input = document.getElementById('cami-input');
    const sendBtn = document.getElementById('cami-send-btn');
    const notifDot = document.getElementById('cami-notif-dot');

    const ENDPOINT = window.CAMI_CHAT_ENDPOINT;
    const CSRF_TOKEN = window.CAMI_CSRF_TOKEN;

    const STORAGE_KEY = 'cami_chat_history';
    const SESSION_KEY = 'cami_session_id';

    let isOpen = false;
    let isSending = false;

    // ---------- Sesi percakapan (dipakai agar Gemini punya konteks) ----------
    function getSessionId() {
        let id = sessionStorage.getItem(SESSION_KEY);
        if (!id) {
            id = 'cami-' + Date.now() + '-' + Math.random().toString(36).slice(2, 9);
            sessionStorage.setItem(SESSION_KEY, id);
        }
        return id;
    }

    function loadHistory() {
        try {
            const raw = sessionStorage.getItem(STORAGE_KEY);
            return raw ? JSON.parse(raw) : [];
        } catch (e) {
            return [];
        }
    }

    function saveHistory(history) {
        sessionStorage.setItem(STORAGE_KEY, JSON.stringify(history));
    }

    // ---------- UI ----------
    function openPanel() {
        panel.classList.remove('cami-hidden');
        isOpen = true;
        notifDot.style.display = 'none';
        input.focus();
        scrollToBottom();
    }

    function closePanel() {
        panel.classList.add('cami-hidden');
        isOpen = false;
    }

    toggleBtn.addEventListener('click', function () {
        isOpen ? closePanel() : openPanel();
    });
    closeBtn.addEventListener('click', closePanel);

    function scrollToBottom() {
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function appendMessage(role, text, isError) {
        const wrap = document.createElement('div');
        wrap.className = 'cami-msg ' + (role === 'user' ? 'cami-msg-user' : 'cami-msg-bot') + (isError ? ' cami-msg-error' : '');

        if (role !== 'user') {
            const avatar = document.createElement('img');
            avatar.className = 'cami-msg-avatar';
            avatar.src = (document.getElementById('cami-header-avatar') || {}).src || '';
            avatar.alt = '';
            wrap.appendChild(avatar);
        }

        const bubble = document.createElement('div');
        bubble.className = 'cami-msg-bubble';
        bubble.textContent = text;
        wrap.appendChild(bubble);

        messagesEl.appendChild(wrap);
        scrollToBottom();
    }

    function setTyping(show) {
        typingEl.classList.toggle('cami-hidden', !show);
        if (show) scrollToBottom();
    }

    // ---------- Render ulang riwayat saat halaman dimuat ----------
    (function restoreHistory() {
        const history = loadHistory();
        if (history.length === 0) return;
        // Hapus pesan sambutan default, lalu render dari riwayat
        messagesEl.innerHTML = '';
        history.forEach(function (item) {
            appendMessage(item.role, item.text);
        });
    })();

    // ---------- Kirim pesan ----------
    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        const text = input.value.trim();
        if (!text || isSending) return;

        appendMessage('user', text);

        const history = loadHistory();
        history.push({ role: 'user', text: text });
        saveHistory(history);

        input.value = '';
        isSending = true;
        sendBtn.disabled = true;
        setTyping(true);

        try {
            const response = await fetch(ENDPOINT, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    message: text,
                    session_id: getSessionId(),
                }),
            });

            const data = await response.json();

            setTyping(false);

            if (!response.ok || !data.reply) {
                throw new Error(data.message || 'Terjadi kesalahan pada server.');
            }

            appendMessage('bot', data.reply);
            const historyAfter = loadHistory();
            historyAfter.push({ role: 'bot', text: data.reply });
            saveHistory(historyAfter);

        } catch (err) {
            setTyping(false);
            appendMessage('bot', 'Error: ' + err.message, true); 
            console.error('Cami chatbot error:', err);
        } finally {
            isSending = false;
            sendBtn.disabled = false;
            input.focus();
        }
    });

})();
