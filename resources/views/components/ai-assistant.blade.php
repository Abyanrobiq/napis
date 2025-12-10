<!-- AI Assistant Floating -->
<div id="ai-assistant" class="fixed bottom-6 right-6 z-[999999] flex items-end">

    <!-- CHAT WINDOW -->
    <div id="assistant-chat"
         class="hidden w-96 bg-white rounded-2xl shadow-xl overflow-hidden z-[999999]">

        <!-- HEADER -->
        <div class="p-4 border-b flex items-center gap-3">
            <div class="w-10 h-10 flex items-center justify-center">
                <lottie-player 
                    src="/animations/ayu.json"
                    background="transparent"
                    speed="1"
                    style="width:56px;height:56px;"
                    loop autoplay>
                </lottie-player>
            </div>

            <div class="flex-1">
                <div class="font-semibold">Ayu — AI Assistant</div>
                <div class="text-xs text-gray-500">Klik untuk chat atau minta rekomendasi</div>
            </div>

            <button id="new-session" class="text-blue-600 text-xs mr-2 hover:text-blue-800" title="Session Baru">🔄</button>
            <button id="close-assistant" class="text-gray-500">✕</button>
        </div>

        <!-- BODY -->
        <div id="assistant-body" class="p-4 h-64 overflow-auto">
            <div id="assistant-messages"></div>
        </div>

        <!-- INPUT -->
        <div class="p-3 border-t">
            <form id="assistant-form" class="flex gap-2">
                <input id="assistant-input" class="flex-1 border rounded-lg px-3 py-2"
                       placeholder="Tanya Ayu..." />
                <button class="bg-blue-600 text-white px-4 py-2 rounded-lg">Kirim</button>
            </form>
        </div>
    </div>

    <!-- FLOATING BUTTON -->
    <button id="assistant-toggle"
            class="w-16 h-16 rounded-full bg-white shadow-2xl flex items-center justify-center z-[999999]">

        <lottie-player 
            src="/animations/ayu.json"
            background="transparent"
            speed="1"
            style="width:56px;height:56px;"
            loop autoplay>
        </lottie-player>
    </button>
</div>

<!-- LOTTIE SCRIPT -->
<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>

<style>
#assistant-messages {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.message-row {
    width: 100%;
    display: flex;
}

.message-row.user { justify-content: flex-end; }
.message-row.assistant { justify-content: flex-start; }

.user-bubble, .assistant-bubble {
    display: inline-block;
    max-width: 75%;
    padding: 8px 12px;
    border-radius: 14px;
    line-height: 1.45;
    font-size: 14px;
    white-space: normal;
    overflow-wrap: break-word;
}

.user-bubble {
    background: #2563eb;
    color: white;
}

.assistant-bubble {
    background: #eef2ff;
    color: #111;
}

</style>

<script>
document.addEventListener('DOMContentLoaded', () => {

    // ========================================
// LOCAL STORAGE — SAVE / LOAD CHAT HISTORY
// ========================================

function saveChat() {
    const html = document.getElementById('assistant-messages').innerHTML;
    localStorage.setItem("ayu_chat_history", html);
}

function loadChat() {
    const saved = localStorage.getItem("ayu_chat_history");
    if (saved) {
        document.getElementById('assistant-messages').innerHTML = saved;
    }
}


    const toggle = document.getElementById('assistant-toggle');
    const chat = document.getElementById('assistant-chat');
    const closeBtn = document.getElementById('close-assistant');
    const form = document.getElementById('assistant-form');
    const input = document.getElementById('assistant-input');
    const messages = document.getElementById('assistant-messages');

    // SHOW/HIDE
    toggle.addEventListener('click', () => {
    chat.classList.toggle('hidden');

    // Load chat history saat buka chat
    if (!chat.classList.contains('hidden')) {
        loadChat();
    }

    setTimeout(() => input.focus(), 100);
});

    closeBtn.addEventListener('click', () => chat.classList.add('hidden'));

    // NEW SESSION
    const newSessionBtn = document.getElementById('new-session');
    newSessionBtn.addEventListener('click', async () => {
        if (confirm('Mulai session baru? Chat history akan dihapus.')) {
            try {
                const res = await fetch("{{ route('ai.newSession') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || "{{ csrf_token() }}"
                    }
                });

                if (!res.ok) {
                    const errorText = await res.text();
                    console.error('New session error:', errorText);
                    appendMessage("assistant", `Gagal memulai session baru: ${res.statusText}`);
                    return;
                }

                const contentType = res.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const responseText = await res.text();
                    console.error('Non-JSON response:', responseText);
                    appendMessage("assistant", "Server error saat memulai session baru");
                    return;
                }

                const data = await res.json();
                
                // Clear chat UI
                messages.innerHTML = '';
                localStorage.removeItem("ayu_chat_history");
                
                // Show success message
                appendMessage("assistant", data.message || "Session baru dimulai! 🎉");
                
            } catch (err) {
                console.error('New session error:', err);
                appendMessage("assistant", "Gagal memulai session baru: " + err.message);
            }
        }
    });

    // Markdown
    function renderMarkdown(text) {
        return text
            .replace(/\*\*(.*?)\*\*/g, "<strong>$1</strong>")
            .replace(/\*(.*?)\*/g, "<em>$1</em>");
    }

    // Add bubble
    function appendMessage(role, text) {
        text = renderMarkdown(String(text ?? ""));
        const paragraphs = text.split(/\n+/);

        paragraphs.forEach(p => {
            if (!p.trim()) return;

            const row = document.createElement("div");
            row.className = "message-row " + role;

            row.innerHTML = `
                <div class="${role}-bubble">${p}</div>
            `;

            messages.appendChild(row);
        });

        messages.scrollTop = messages.scrollHeight;
        saveChat(); // <-- simpan setelah bubble masuk
    }

    // ============================
    // AYU AUTO POPUP (for overspend)
    // ============================
    window.ayuAutoMessage = function(text) {
        chat.classList.remove("hidden");
        appendMessage("assistant", text);
    };

    // SUBMIT
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const text = input.value.trim();
        if (!text) return;

        appendMessage("user", text);
        input.value = "";

        // typing
        const typing = document.createElement("div");
        typing.className = "message-row assistant";
        typing.innerHTML = `<div class="assistant-bubble">...menulis...</div>`;
        messages.appendChild(typing);

        try {
            const res = await fetch("{{ route('ai.chat') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || "{{ csrf_token() }}"
                },
                body: JSON.stringify({ message: text })
            });

            typing.remove();

            // Check if response is OK
            if (!res.ok) {
                const errorText = await res.text();
                console.error('Server error:', errorText);
                appendMessage("assistant", `Server error (${res.status}): ${res.statusText}`);
                return;
            }

            // Check content type
            const contentType = res.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const responseText = await res.text();
                console.error('Non-JSON response:', responseText);
                appendMessage("assistant", "Server mengembalikan response yang tidak valid. Coba refresh halaman.");
                return;
            }

            const data = await res.json();

            if (data.reply) {
                if (Array.isArray(data.reply)) {
                    data.reply.forEach(r => appendMessage("assistant", r));
                } else {
                    appendMessage("assistant", data.reply);
                }
            } else if (data.error) {
                appendMessage("assistant", "Maaf, ada masalah: " + data.error);
            } else {
                appendMessage("assistant", "Response tidak valid dari server");
            }

        } catch (err) {
            typing.remove();
            console.error('Chat error:', err);
            appendMessage("assistant", "Error koneksi: " + err.message);
        }
    });

});
</script>
