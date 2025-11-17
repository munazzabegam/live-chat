// --- Configuration and Global State ---
const POLLING_INTERVAL = 2000; // Poll every 2 seconds
const HEARTBEAT_INTERVAL = 10000; // Send heartbeat every 10 seconds

// IMPORTANT: Update these URLs to match your actual server path
const SEND_URL = 'api/send_message.php';
const GET_URL = 'api/get_messages.php';
const HEARTBEAT_URL = 'api/heartbeat.php';
const ONLINE_USERS_URL = 'api/get_online_users.php';

// Generate unique user ID
function generateUserId() {
    return 'xxxx-xxxx-4xxx-yxxx-xxxx'.replace(/[xy]/g, function(c) {
        var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
        return v.toString(16);
    });
}

let userId = localStorage.getItem('chatUserId') || generateUserId();
let userName = localStorage.getItem('chatUserName') || ''; 
let isChatActive = false;
let pollingTimer = null;
let heartbeatTimer = null;
let onlineUsers = [];

// --- DOM Elements ---
const chatBox = document.getElementById('chat-box');
const messageInput = document.getElementById('message-input');
const sendBtn = document.getElementById('send-btn');
const nameModal = document.getElementById('name-modal');
const nameInput = document.getElementById('name-input');
const setNameBtn = document.getElementById('set-name-btn');
const chatArea = document.getElementById('chat-area');
const userDisplay = document.getElementById('user-display');
const authStatus = document.getElementById('auth-status');
const onlineUsersList = document.getElementById('online-users-list');
const onlineCount = document.getElementById('online-count');

// --- Utility Functions ---

function scrollToBottom() {
    chatBox.scrollTop = chatBox.scrollHeight;
}

function updateUserNameDisplay() {
    if (userName) {
        userDisplay.textContent = `👤 ${userName}`;
        userDisplay.classList.remove('hidden');
    }
}

function setStatus(text, type = 'info') {
    const statusColors = {
        'info': 'text-gray-600',
        'success': 'text-green-600',
        'error': 'text-red-600',
        'warning': 'text-yellow-600'
    };
    
    const statusIcons = {
        'info': '⚡',
        'success': '✓',
        'error': '✗',
        'warning': '⚠'
    };
    
    authStatus.innerHTML = `
        <span class="inline-block w-2 h-2 ${type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-yellow-400'} rounded-full mr-2 ${type === 'info' ? 'animate-pulse' : ''}"></span>
        ${statusIcons[type]} ${text}
    `;
    authStatus.className = `text-xs ${statusColors[type]} status-badge flex items-center`;
}

// --- UI State Management ---

function showNameModal() {
    nameModal.classList.remove('hidden');
    chatArea.classList.add('hidden');
    nameInput.focus();
    setStatus('Ready to start chat', 'info');
}

function showChat() {
    nameModal.classList.add('hidden');
    chatArea.classList.remove('hidden');
    sendBtn.disabled = false;
    messageInput.focus();
    updateUserNameDisplay();
    setStatus('Connected', 'success');
    scrollToBottom();
    isChatActive = true;
    startPolling();
    startHeartbeat();
}

// --- Online Users Management ---

function renderOnlineUsers(users) {
    onlineUsers = users;
    onlineCount.textContent = users.length;
    
    if (users.length === 0) {
        onlineUsersList.innerHTML = `
            <div class="text-center text-sm text-gray-400 py-8">
                <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                No users online
            </div>
        `;
        return;
    }
    
    let usersHtml = '';
    users.forEach(user => {
        const isCurrentUser = String(user.user_id) === String(userId);
        const bgColor = isCurrentUser ? 'bg-indigo-100 border-indigo-300' : 'bg-white border-gray-200';
        const textColor = isCurrentUser ? 'text-indigo-700' : 'text-gray-700';
        
        usersHtml += `
            <div class="user-badge ${bgColor} border p-3 rounded-lg flex items-center justify-between">
                <div class="flex items-center space-x-2 flex-grow min-w-0">
                    <span class="online-indicator"></span>
                    <span class="${textColor} font-medium text-sm truncate">
                        ${user.user_name}${isCurrentUser ? ' (You)' : ''}
                    </span>
                </div>
                ${isCurrentUser ? '<span class="text-xs text-indigo-600">●</span>' : ''}
            </div>
        `;
    });
    
    onlineUsersList.innerHTML = usersHtml;
}

async function fetchOnlineUsers() {
    if (!isChatActive) return;
    
    try {
        const response = await fetch(ONLINE_USERS_URL);
        
        // Check if response is ok
        if (!response.ok) {
            console.error(`HTTP error! Status: ${response.status}`);
            return;
        }
        
        // Get the text first to check what we're receiving
        const text = await response.text();
        
        try {
            const result = JSON.parse(text);
            
            if (result.success && result.users) {
                renderOnlineUsers(result.users);
            }
        } catch (parseError) {
            console.error("Failed to parse JSON. Response:", text.substring(0, 200));
            setStatus('Error loading users', 'error');
        }
    } catch (error) {
        console.error("Error fetching online users:", error);
    }
}

// --- Heartbeat to keep user active ---

async function sendHeartbeat() {
    if (!isChatActive || !userName) return;
    
    try {
        const response = await fetch(HEARTBEAT_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                userId: userId,
                userName: userName
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            fetchOnlineUsers(); // Update online users after heartbeat
        }
    } catch (error) {
        console.error("Heartbeat error:", error);
    }
}

function startHeartbeat() {
    if (heartbeatTimer) clearInterval(heartbeatTimer);
    sendHeartbeat(); // Send immediately
    heartbeatTimer = setInterval(sendHeartbeat, HEARTBEAT_INTERVAL);
}

// --- Messages Management ---

function renderMessages(messages) {
    let messagesHtml = '';
    const shouldScroll = chatBox.scrollHeight - chatBox.scrollTop < chatBox.offsetHeight + 100;

    if (messages.length === 0) {
        messagesHtml = `
            <div class="text-center py-12">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <p class="text-gray-400 text-sm">No messages yet. Start the conversation!</p>
            </div>
        `;
    } else {
        messages.forEach(data => {
            const isCurrentUser = String(data.user_id) === String(userId);
            const time = data.timestamp;

            let messageClass = isCurrentUser
                ? 'bg-gradient-to-br from-indigo-500 to-purple-600 text-white ml-auto text-right'
                : 'bg-white text-gray-800 mr-auto text-left border border-gray-200';

            let nameTag = isCurrentUser 
                ? `<span class="font-semibold">You</span>` 
                : `<span class="font-semibold text-indigo-600">${data.user_name}</span>`;

            messagesHtml += `
                <div class="flex ${isCurrentUser ? 'justify-end' : 'justify-start'}">
                    <div class="flex flex-col ${isCurrentUser ? 'items-end' : 'items-start'} max-w-[85%] md:max-w-[75%]">
                        <span class="text-xs ${isCurrentUser ? 'text-gray-500' : 'text-gray-500'} mb-1 px-3">
                            ${nameTag} • ${time}
                        </span>
                        <div class="message-bubble ${messageClass} shadow-md">
                            ${data.message_text}
                        </div>
                    </div>
                </div>
            `;
        });
    }

    chatBox.innerHTML = messagesHtml;

    if (shouldScroll) {
        scrollToBottom();
    }
}

async function fetchMessages() {
    if (!isChatActive) return;

    try {
        const response = await fetch(GET_URL);
        
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        
        // Get text first to debug
        const text = await response.text();
        
        try {
            const result = JSON.parse(text);

            if (result.success && result.messages) {
                if (result.messages.error) {
                    setStatus(`DB Error: ${result.messages.error}`, 'error');
                } else {
                    renderMessages(result.messages);
                    setStatus('Connected', 'success');
                }
            } else {
                setStatus(`Error: ${result.message || 'Unknown error'}`, 'error');
            }
        } catch (parseError) {
            console.error("Failed to parse JSON. Response:", text.substring(0, 200));
            setStatus(`Parse error - check PHP files`, 'error');
        }
    } catch (error) {
        console.error("Fetch Error:", error);
        setStatus(`Connection error`, 'error');
    }
}

async function sendMessage() {
    if (!isChatActive || !userName) return;

    const messageText = messageInput.value.trim();
    if (messageText === "") return;

    sendBtn.disabled = true;
    const originalBtnContent = sendBtn.innerHTML;
    sendBtn.innerHTML = `
        <div class="typing-indicator">
            <div class="typing-dot"></div>
            <div class="typing-dot"></div>
            <div class="typing-dot"></div>
        </div>
    `;

    try {
        const payload = {
            userName: userName,
            messageText: messageText,
            userId: userId
        };

        const response = await fetch(SEND_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        const result = await response.json();

        if (result.success) {
            messageInput.value = '';
            messageInput.focus();
            fetchMessages();
            sendHeartbeat(); // Update activity after sending message
        } else {
            setStatus(`Failed to send: ${result.message}`, 'error');
        }

    } catch (e) {
        console.error("Send Error:", e);
        setStatus(`Send error: ${e.message}`, 'error');
    } finally {
        sendBtn.innerHTML = originalBtnContent;
        sendBtn.disabled = false;
    }
}

function startPolling() {
    if (pollingTimer) clearInterval(pollingTimer);
    fetchMessages();
    fetchOnlineUsers();
    pollingTimer = setInterval(() => {
        fetchMessages();
        fetchOnlineUsers();
    }, POLLING_INTERVAL);
}

// --- Event Listeners and Initialization ---

setNameBtn.addEventListener('click', () => {
    const newName = nameInput.value.trim();
    if (newName.length > 0) {
        userName = newName;
        localStorage.setItem('chatUserName', userName);
        localStorage.setItem('chatUserId', userId);
        showChat();
    } else {
        nameInput.classList.add('border-red-500', 'animate-shake');
        nameInput.placeholder = "Please enter a valid name!";
        setTimeout(() => {
            nameInput.classList.remove('border-red-500', 'animate-shake');
        }, 500);
    }
});

nameInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
        setNameBtn.click();
    }
});

sendBtn.addEventListener('click', sendMessage);

messageInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter' && !sendBtn.disabled) {
        sendMessage();
    }
});

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (pollingTimer) clearInterval(pollingTimer);
    if (heartbeatTimer) clearInterval(heartbeatTimer);
});

// Initial load
window.onload = function() {
    localStorage.setItem('chatUserId', userId);
    
    if (userName) {
        showChat();
    } else {
        showNameModal();
    }
}