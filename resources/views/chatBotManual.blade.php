@php
    // url testting untuk chatbot manual execute workflow satu persatu
    $chatbotUrl = 'https://n8n-2.djoin.id/webhook-test/89674385-06a2-421e-8496-0fdf9696719a';
    // url proudction untuk chatbot active
    //$chatbotUrl = 'https://n8n-2.djoin.id/webhook/89674385-06a2-421e-8496-0fdf9696719a';
@endphp

<style>
    #chat-messages a {
    color: blue;
    text-decoration: underline;
}

</style>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Chat Bot') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __("ini chat bot manual!") }}
                </div>
            </div>
        </div>
    </div>
    <!-- Tombol bulat Chatbot -->
    <button id="chat-toggle" 
            class="fixed bottom-4 right-4 w-14 h-14 rounded-full bg-blue-600 text-white shadow-lg flex items-center justify-center hover:bg-blue-700">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8-1.31 0-2.55-.19-3.67-.53L3 20l1.5-3.09A7.96 7.96 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
        </svg>
    </button>

    <!-- Chatbot Widget (disembunyikan dulu) -->
    <div id="chat-widget" 
        class="hidden fixed bottom-20 right-4 w-96 bg-white rounded-xl shadow-lg border border-gray-200">
        <div class="bg-blue-600 text-white px-4 py-2 rounded-t-xl flex justify-between items-center">
            <span>Chatbot Coopmax</span>
            <button id="chat-close" class="text-white font-bold">×</button>
        </div>
        <div id="chat-messages" class="h-80 overflow-y-auto p-4 space-y-2 text-sm text-gray-800"></div>
        <div class="p-2 border-t flex">
            <input id="chat-input" type="text" placeholder="Tulis pesan..." 
                class="flex-1 border rounded px-2 py-1 text-sm focus:outline-none">
            <button id="chat-send" 
                    class="ml-2 bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">
                Kirim
            </button>
        </div>
    </div>
</x-app-layout>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const webhookUrl = "{{ $chatbotUrl }}";
        const input = document.getElementById('chat-input');
        const sendBtn = document.getElementById('chat-send');
        const messages = document.getElementById('chat-messages');
        const toggleBtn = document.getElementById('chat-toggle');
        const chatWidget = document.getElementById('chat-widget');
        const closeBtn = document.getElementById('chat-close');

        const sessionId = "{{ auth()->id() ?? 'guest-' . Str::random(8) }}";
        const userName  = "{{ auth()->user()->name ?? 'Guest' }}";
        const now = new Date();
const time = `${now.getFullYear()}-${(now.getMonth()+1).toString().padStart(2,'0')}-${now.getDate().toString().padStart(2,'0')} ` + 
                  `${now.getHours().toString().padStart(2,'0')}:${now.getMinutes().toString().padStart(2,'0')}:${now.getSeconds().toString().padStart(2,'0')}`;

        // Toggle Chat
        toggleBtn.addEventListener('click', function() {
            chatWidget.classList.toggle('hidden');
        });
        closeBtn.addEventListener('click', function() {
            chatWidget.classList.add('hidden');
        });

        // Tambah pesan ke area chat
        function addMessage(text, sender) {
            const div = document.createElement('div');
            div.className = sender === 'user' ? 'text-right' : 'text-left text-gray-600';
            div.innerHTML = `<div class="inline-block px-3 py-2 rounded-lg ${sender==='user' ? 'bg-green-100' : 'bg-gray-100'}">${text}</div>`;
            messages.appendChild(div);
            messages.scrollTop = messages.scrollHeight;
        }

        // Kirim pesan ke bot
        async function sendMessage() {
            const msg = input.value.trim();
            if (!msg) return;
            addMessage(msg, 'user');
            input.value = '';

            try {
                const res = await fetch(webhookUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: msg, sessionId: sessionId, userName: userName, time: time })
                });

                const raw = await res.text();
                let reply = '';

                try {
                const parsed = JSON.parse(raw); // bisa array atau object
                if (Array.isArray(parsed) && parsed[0]?.output) {
                    reply = parsed[0].output;
                } else if (parsed.reply) {
                    reply = parsed.reply;
                } else {
                    reply = JSON.stringify(parsed);
                }
                } catch (e) {
                // fallback kalau bukan JSON valid
                reply = raw.trim() || 'Tidak ada balasan';
                }

                addMessage(reply, 'bot');


            } catch (err) {
                addMessage('Error: tidak bisa terhubung ke bot', 'bot');
                console.error(err);
            }
        }

        sendBtn.addEventListener('click', sendMessage);
        input.addEventListener('keypress', e => {
            if (e.key === 'Enter') sendMessage();
        });

        // Klik di luar untuk nutup chat
        document.addEventListener('click', function(e) {
            if (!chatWidget.contains(e.target) && !toggleBtn.contains(e.target)) {
                chatWidget.classList.add('hidden');
            }
        });
    });
</script>
