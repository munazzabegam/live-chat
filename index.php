<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, maximum-scale=1">
    <title>Live Group Chat - Enhanced</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        * {
            -webkit-tap-highlight-color: transparent;
        }
        
        html, body {
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        
        body {
            position: fixed;
        }
        
        .message-bubble {
            display: inline-block;
            padding: 0.75rem 1rem;
            border-radius: 1rem;
            word-wrap: break-word;
            animation: slideUp 0.3s ease-out;
            font-size: clamp(0.875rem, 2vw, 1rem);
            line-height: 1.4;
            min-height: 2.5rem;
            display: flex;
            align-items: center;
            width: auto;
        }
        
        @media (max-width: 768px) {
            .message-bubble {
                max-width: 90vw;
                min-height: 2.25rem;
                padding: 0.625rem 0.875rem;
            }
        }
        
        @media (min-width: 769px) {
            .message-bubble {
                max-width: 60%;
            }
        }
        
        .online-indicator {
            width: 8px;
            height: 8px;
            background: #22c55e;
            border-radius: 50%;
            display: inline-block;
            margin-right: 6px;
            box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.2);
        }
        
        .user-badge {
            transition: all 0.2s ease;
            padding: 0.5rem 0.75rem;
            border-radius: 0.75rem;
            cursor: pointer;
        }
        
        .user-badge:active {
            transform: scale(0.95);
            background-color: rgba(99, 102, 241, 0.1);
        }
        
        #chat-box::-webkit-scrollbar {
            width: 6px;
        }
        
        #chat-box::-webkit-scrollbar-track {
            background: transparent;
        }
        
        #chat-box::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
        
        #chat-box::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .typing-indicator {
            display: inline-flex;
            gap: 4px;
        }
        
        .typing-dot {
            width: 6px;
            height: 6px;
            background: #94a3b8;
            border-radius: 50%;
            animation: typing 1.4s infinite;
        }
        
        .typing-dot:nth-child(2) {
            animation-delay: 0.2s;
        }
        
        .typing-dot:nth-child(3) {
            animation-delay: 0.4s;
        }
        
        @keyframes typing {
            0%, 60%, 100% {
                transform: translateY(0);
            }
            30% {
                transform: translateY(-10px);
            }
        }
        
        .status-badge {
            animation: fade-in 0.3s ease-in;
        }
        
        /* Mobile optimizations */
        #app-container {
            display: flex;
            flex-direction: column;
            height: 100vh;
            height: 100dvh;
        }
        
        #chat-area {
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        #chat-box {
            flex: 1;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        .input-container {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            border-top: 1px solid #e5e7eb;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
            z-index: 50;
        }
        
        @media (max-width: 768px) {
            .input-container {
                padding: max(0.5rem, env(safe-area-inset-bottom, 0)) 1rem 0.75rem;
            }
            
            #app-container {
                flex-direction: column;
            }
            
            #sidebar {
                display: none;
                position: absolute;
                width: 100%;
                height: 100%;
                z-index: 40;
                background: white;
            }
            
            #sidebar.active {
                display: flex;
            }
            
            #chat-area {
                padding-bottom: 4.5rem;
            }
            
            .message-bubble {
                max-width: 85%;
                font-size: 0.95rem;
            }
        }
        
        @media (min-width: 769px) {
            #app-container {
                flex-direction: row;
            }
            
            #sidebar {
                display: flex;
                flex-direction: column;
                width: 16rem;
                border-right: 1px solid #e5e7eb;
            }
            
            .input-container {
                position: relative;
                padding: 1rem;
                bottom: auto;
                left: auto;
                right: auto;
            }
            
            #chat-area {
                flex: 1;
                padding-bottom: 0;
            }
        }
        
        #message-input {
            font-size: 16px;
        }
        
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
            display: flex;
            align-items: flex-end;
            z-index: 50;
            padding: 1rem;
        }
        
        @media (min-width: 768px) {
            .modal-overlay {
                align-items: center;
                justify-content: center;
            }
        }
        
        .modal-content {
            background: white;
            border-radius: 1.5rem 1.5rem 0 0;
            width: 100%;
            max-width: 28rem;
            padding: 1.5rem;
            animation: slideUp 0.3s ease-out;
        }
        
        @media (min-width: 768px) {
            .modal-content {
                border-radius: 1.5rem;
            }
        }
        
        #send-btn {
            min-height: 2.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        #send-btn svg {
            width: 1.25rem;
            height: 1.25rem;
        }
        
        .sidebar-toggle {
            display: none;
            flex-direction: column;
            gap: 4px;
            cursor: pointer;
            padding: 0.5rem;
        }
        
        @media (max-width: 768px) {
            .sidebar-toggle {
                display: flex;
            }
        }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <div id="app-container" class="bg-white">
        
        <!-- Sidebar - Online Users -->
        <aside id="sidebar" class="w-full md:w-64 bg-gradient-to-b from-indigo-50 to-white border-r border-gray-200 flex flex-col">
            <div class="p-4 border-b border-gray-200 bg-white flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Online Users
                </h2>
                <button id="close-sidebar" class="md:hidden p-1 hover:bg-gray-100 rounded">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <div class="px-4 py-2 bg-gradient-to-r from-indigo-100 to-purple-100 border-b border-indigo-200">
                <p class="text-sm font-semibold text-indigo-700">
                    <span id="online-count" class="text-lg">0</span> active
                </p>
            </div>
            
            <div id="online-users-list" class="flex-grow overflow-y-auto p-3 space-y-2">
                <div class="text-center text-sm text-gray-400 py-8">
                    Loading users...
                </div>
            </div>
        </aside>

        <!-- Main Chat Area -->
        <div class="flex-grow flex flex-col">
            
            <!-- Header -->
            <header class="p-4 gradient-bg text-white shadow-lg flex justify-between items-center md:flex-row flex-row">
                <div class="flex-1">
                    <h1 class="text-lg md:text-2xl font-bold truncate">Live Group Chat</h1>
                    <p class="text-xs md:text-sm opacity-90 hidden sm:block">Real-time messaging</p>
                </div>
                <div class="flex items-center gap-3">
                    <span id="user-display" class="text-sm bg-white/20 backdrop-blur-sm py-2 px-3 rounded-full hidden font-medium truncate max-w-32 md:max-w-none"></span>
                    <button id="open-sidebar" class="md:hidden p-2 hover:bg-white/20 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </header>

            <!-- Name Modal -->
            <div id="name-modal" class="hidden fixed inset-0 modal-overlay">
                <div class="modal-content">
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full mx-auto mb-4 flex items-center justify-center">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                        </div>
                        <h2 class="text-2xl md:text-3xl font-bold mb-2 bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Welcome!</h2>
                        <p class="text-gray-600 text-sm md:text-base">Enter your name to join</p>
                    </div>
                    
                    <input type="text" id="name-input" placeholder="Your Name" maxlength="20"
                           class="w-full p-4 mb-4 border-2 border-indigo-200 rounded-xl focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition duration-200 text-center text-lg font-medium">
                    
                    <button id="set-name-btn"
                            class="w-full gradient-bg hover:opacity-90 text-white font-semibold py-4 rounded-xl transition duration-200 shadow-lg hover:shadow-xl active:scale-95">
                        Start Chatting →
                    </button>
                    
                    <p class="text-xs text-gray-500 text-center mt-4">
                        Your messages will be visible to all online users
                    </p>
                </div>
            </div>

            <!-- Chat Area -->
            <main id="chat-area" class="hidden flex-grow flex flex-col">
                
                <!-- Status Bar -->
                <div class="px-4 py-2 bg-gray-50 border-b border-gray-200 flex justify-between items-center text-xs md:text-sm gap-2">
                    <span id="auth-status" class="status-badge flex items-center">
                        <span class="inline-block w-2 h-2 bg-yellow-400 rounded-full mr-2 animate-pulse"></span>
                        <span class="hidden sm:inline">Connecting...</span>
                        <span class="sm:hidden">Online</span>
                    </span>
                    <span class="text-gray-500 hidden sm:inline">
                        Auto-refresh every 2s
                    </span>
                </div>

                <!-- Messages -->
                <div id="chat-box" class="flex-grow overflow-y-auto p-3 md:p-4 space-y-3 bg-gradient-to-b from-gray-50 to-white">
                    <!-- Messages will appear here -->
                </div>
            </main>
        </div>
    </div>

    <!-- Fixed Input Area - Mobile Fixed Bottom -->
    <div class="input-container" id="input-container">
        <div class="flex items-center gap-2">
            <input type="text" id="message-input" placeholder="Type message..."
                   class="flex-grow p-3 md:p-4 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition duration-200 text-sm md:text-base">
            <button id="send-btn"
                    class="gradient-bg hover:opacity-90 text-white rounded-xl font-semibold transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center shadow-lg min-w-12 md:min-w-16 px-3 md:px-4 py-3 md:py-4 active:scale-95"
                    disabled>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 md:w-6 md:h-6">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                </svg>
            </button>
        </div>
    </div>

    <script>
        // Keyboard and viewport handling for mobile
        let viewportHeight = window.innerHeight;
        let isKeyboardOpen = false;

        window.addEventListener('resize', () => {
            const newHeight = window.innerHeight;
            if (newHeight < viewportHeight * 0.75) {
                isKeyboardOpen = true;
            } else {
                isKeyboardOpen = false;
            }
            viewportHeight = newHeight;
        });

        // Sidebar toggle
        const sidebar = document.getElementById('sidebar');
        const openSidebarBtn = document.getElementById('open-sidebar');
        const closeSidebarBtn = document.getElementById('close-sidebar');

        if (openSidebarBtn) {
            openSidebarBtn.addEventListener('click', () => {
                sidebar.classList.add('active');
            });
        }

        if (closeSidebarBtn) {
            closeSidebarBtn.addEventListener('click', () => {
                sidebar.classList.remove('active');
            });
        }

        sidebar.addEventListener('click', (e) => {
            if (e.target === sidebar) {
                sidebar.classList.remove('active');
            }
        });

        // Smooth scrolling
        document.addEventListener('touchmove', (e) => {
            if (e.target.closest('#chat-box')) {
                e.preventDefault();
                e.preventDefault = () => {};
            }
        }, { passive: true });

        // IST Timezone formatter (UTC +05:30)
        window.formatTimeIST = function(dateString) {
            try {
                const date = new Date(dateString);
                const istTime = new Date(date.getTime() + (5.5 * 60 * 60 * 1000));
                return istTime.toLocaleTimeString('en-IN', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });
            } catch (e) {
                return 'N/A';
            }
        };

        // Override any existing time formatting in chat.js
        const originalFormatTime = window.formatTime || function() {};
        window.formatTime = function(dateString) {
            return window.formatTimeIST(dateString);
        };
    </script>
    <script src="assets/js/chat.js"></script>
</body>
</html>