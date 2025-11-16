<?php
// PHP file to serve the HTML application.
// This file is the main entry point for the PHP/SQL polling chat.
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Group Chat (PHP/SQL Polling)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>

    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center font-sans p-4">

    <div id="app-container" class="w-full max-w-lg bg-white shadow-2xl rounded-xl flex flex-col h-[85vh] md:h-[90vh]">
        <header class="p-4 bg-indigo-600 text-white rounded-t-xl shadow-md flex justify-between items-center">
            <h1 class="text-xl font-bold">Real-Time Group Chat (PHP/SQL)</h1>
            <span id="user-display" class="text-sm bg-indigo-700 py-1 px-3 rounded-full hidden"></span>
        </header>

        <div id="name-modal" class="p-8 flex flex-col items-center justify-center flex-grow">
            <div class="bg-white p-6 md:p-8 rounded-xl shadow-lg border border-gray-200 w-full max-w-sm">
                <h2 class="text-2xl font-semibold mb-4 text-center text-gray-800">Welcome</h2>
                <p class="text-gray-600 mb-6 text-center">Enter your name to start chatting instantly!</p>
                <input type="text" id="name-input" placeholder="Your Chat Name" maxlength="20"
                       class="w-full p-3 mb-4 border-2 border-indigo-300 rounded-lg focus:outline-none focus:border-indigo-500 transition duration-150 shadow-inner">
                <button id="set-name-btn"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-lg transition duration-150 shadow-md hover:shadow-lg disabled:opacity-50">
                    Start Chatting
                </button>
            </div>
        </div>

        <main id="chat-area" class="flex-grow flex flex-col hidden">
            <div id="chat-box" class="flex-grow overflow-y-auto p-4 space-y-3 bg-gray-50">
                <div class="text-center text-sm text-gray-500 pt-2 pb-1">
                    <span class="inline-block px-3 py-1 bg-gray-200 rounded-full shadow-inner">
                        <span id="auth-status">Connecting...</span>
                    </span>
                </div>
            </div>

            <div class="p-4 bg-white border-t border-gray-200 flex items-center">
                <input type="text" id="message-input" placeholder="Type a message..."
                       class="flex-grow p-3 border border-gray-300 rounded-l-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 transition duration-150">
                <button id="send-btn"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white p-3 rounded-r-xl font-semibold transition duration-150 disabled:bg-indigo-400 flex items-center justify-center shadow-md"
                        disabled>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                    </svg>
                </button>
            </div>
        </main>
    </div>

    <script src="assets/js/chat.js"></script>
</body>
</html>