<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Group Chat - Enhanced</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.3s ease-in',
                        'slide-up': 'slideUp 0.3s ease-out',
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        slideUp: {
                            '0%': { transform: 'translateY(10px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        .message-bubble {
            max-width: 75%;
            padding: 0.75rem 1rem;
            border-radius: 1rem;
            word-wrap: break-word;
            animation: slideUp 0.3s ease-out;
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
        }
        
        .user-badge:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        #chat-box::-webkit-scrollbar {
            width: 8px;
        }
        
        #chat-box::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
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
    </style>
</head>
<body class="bg-gradient-to-br from-gray-100 to-gray-200 min-h-screen flex items-center justify-center font-sans p-4">

    <div id="app-container" class="w-full max-w-6xl bg-white shadow-2xl rounded-2xl flex flex-col md:flex-row h-[90vh] overflow-hidden">
        
        <!-- Sidebar - Online Users -->
        <aside class="w-full md:w-64 bg-gradient-to-b from-indigo-50 to-white border-r border-gray-200 flex flex-col">
            <div class="p-4 border-b border-gray-200 bg-white">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Online Users
                </h2>
                <p class="text-xs text-gray-500 mt-1">
                    <span id="online-count" class="font-semibold text-indigo-600">0</span> active
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
            <header class="p-4 gradient-bg text-white shadow-lg flex justify-between items-center">
                <div>
                    <h1 class="text-xl md:text-2xl font-bold">Live Group Chat</h1>
                    <p class="text-xs md:text-sm opacity-90">Real-time messaging with PHP & SQL</p>
                </div>
                <span id="user-display" class="text-sm bg-white/20 backdrop-blur-sm py-2 px-4 rounded-full hidden font-medium"></span>
            </header>

            <!-- Name Modal -->
            <div id="name-modal" class="p-8 flex flex-col items-center justify-center flex-grow bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50">
                <div class="bg-white p-8 md:p-10 rounded-2xl shadow-2xl border border-gray-100 w-full max-w-md">
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full mx-auto mb-4 flex items-center justify-center">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                        </div>
                        <h2 class="text-3xl font-bold mb-2 bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Welcome!</h2>
                        <p class="text-gray-600">Enter your name to join the conversation</p>
                    </div>
                    
                    <input type="text" id="name-input" placeholder="Your Name" maxlength="20"
                           class="w-full p-4 mb-4 border-2 border-indigo-200 rounded-xl focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition duration-200 text-center text-lg font-medium">
                    
                    <button id="set-name-btn"
                            class="w-full gradient-bg hover:opacity-90 text-white font-semibold py-4 rounded-xl transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        Start Chatting →
                    </button>
                    
                    <p class="text-xs text-gray-500 text-center mt-4">
                        Your messages will be visible to all online users
                    </p>
                </div>
            </div>

            <!-- Chat Area -->
            <main id="chat-area" class="flex-grow flex flex-col hidden">
                
                <!-- Status Bar -->
                <div class="px-4 py-2 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                    <span id="auth-status" class="text-xs text-gray-600 status-badge flex items-center">
                        <span class="inline-block w-2 h-2 bg-yellow-400 rounded-full mr-2 animate-pulse"></span>
                        Connecting...
                    </span>
                    <span class="text-xs text-gray-500">
                        Auto-refresh every 2s
                    </span>
                </div>

                <!-- Messages -->
                <div id="chat-box" class="flex-grow overflow-y-auto p-4 space-y-3 bg-gradient-to-b from-gray-50 to-white">
                    <!-- Messages will appear here -->
                </div>

                <!-- Input Area -->
                <div class="p-4 bg-white border-t border-gray-200 shadow-lg">
                    <div class="flex items-center space-x-2">
                        <input type="text" id="message-input" placeholder="Type your message..."
                               class="flex-grow p-4 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition duration-200 text-sm">
                        <button id="send-btn"
                                class="gradient-bg hover:opacity-90 text-white p-4 rounded-xl font-semibold transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center shadow-lg min-w-[60px]"
                                disabled>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                            </svg>
                        </button>
                    </div>
                    <p class="text-xs text-gray-400 mt-2 text-center">
                        Press Enter to send • Inactive users removed after 5 minutes
                    </p>
                </div>
            </main>
        </div>
    </div>

    <script src="assets/js/chat.js"></script>
</body>
</html>