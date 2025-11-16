// --- Configuration and Global State ---
const POLLING_INTERVAL = 2000; // Poll every 2 seconds
const SEND_URL = 'api/send_message.php';
const GET_URL = 'api/get_messages.php';

// Simple function to generate a GUID/UUID-like string (Fix for browser compatibility)
function generateUserId() {
    return 'xxxx-xxxx-4xxx-yxxx-xxxx'.replace(/[xy]/g, function(c) {
        var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
        return v.toString(16);
    });
}

let userId = localStorage.getItem('chatUserId') || generateUserId(); // Unique ID for user tracking
let userName = localStorage.getItem('chatUserName') || ''; 
let isChatActive = false;
let pollingTimer = null;

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

// --- Utility Functions ---

function scrollToBottom() {
    chatBox.scrollTop = chatBox.scrollHeight;
}

function updateUserNameDisplay() {
    if (userName) {
        userDisplay.textContent = `User: ${userName}`;
        userDisplay.classList.remove('hidden');
    }
}

function setStatus(text, isError = false) {
    authStatus.textContent = text;
    authStatus.classList.toggle('text-red-500', isError);
    authStatus.classList.toggle('text-gray-600', !isError);
}

// --- UI State Management ---

function showNameModal() {
    nameModal.classList.remove('hidden');
    chatArea.classList.add('hidden');
    nameInput.focus();
    setStatus('Ready to start chat.');
}

function showChat() {
    nameModal.classList.add('hidden');
    chatArea.classList.remove('hidden');
    sendBtn.disabled = false;
    messageInput.focus();
    updateUserNameDisplay();
    setStatus('Chat connected (polling).');
    scrollToBottom();
    isChatActive = true;
    startPolling(); // Start polling only when chat is visible
}

// --- AJAX and Polling Logic ---

function renderMessages(messages) {
    let messagesHtml = '';
    const shouldScroll = chatBox.scrollHeight - chatBox.scrollTop < chatBox.offsetHeight + 100;

    messages.forEach(data => {
        const isCurrentUser = String(data.user_id) === String(userId);
        const time = data.timestamp; 

        let messageClass = isCurrentUser
            ? 'bg-indigo-500 text-white ml-auto text-right'
            : 'bg-gray-200 text-gray-800 mr-auto text-left';

        let nameTag = isCurrentUser ? `<span class="font-bold">You</span>` : `<span class="font-bold">${data.user_name}</span>`;

        messagesHtml += `
            <div class="flex ${isCurrentUser ? 'justify-end' : 'justify-start'}">
                <div class="flex flex-col ${isCurrentUser ? 'items-end' : 'items-start'}">
                    <span class="text-xs text-gray-500 mb-1 px-3">
                        ${nameTag} at ${time}
                    </span>
                    <div class="message-bubble ${messageClass} shadow-md">
                        ${data.message_text}
                    </div>
                </div>
            </div>
        `;
    });

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
        
        const result = await response.json();

        if (result.success && result.messages) {
            if (result.messages.error) {
                 // Database error returned from PHP
                 setStatus(`DB Error: ${result.messages.error}`, true);
            } else {
                 renderMessages(result.messages);
                 setStatus('Chat updated successfully.', false);
            }
        } else {
            setStatus(`Error fetching messages: ${result.message || 'Unknown API error'}`, true);
        }
    } catch (error) {
        console.error("Fetch Error:", error);
        setStatus(`Connection Error: Check PHP server status/paths.`, true);
    }
}

async function sendMessage() {
    if (!isChatActive || !userName) return;

    const messageText = messageInput.value.trim();
    if (messageText === "") return;

    sendBtn.disabled = true; 

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
            messageInput.value = ''; // Clear input
            messageInput.focus();
            fetchMessages(); // Trigger a manual fetch immediately
        } else {
            setStatus(`Failed to send message: ${result.message}`, true);
        }

    } catch (e) {
        console.error("Send Error:", e);
        setStatus(`Send Connection Error: ${e.message}`, true);
    } finally {
        sendBtn.disabled = false;
    }
}

function startPolling() {
    if (pollingTimer) clearInterval(pollingTimer);
    fetchMessages(); // Fetch immediately
    pollingTimer = setInterval(fetchMessages, POLLING_INTERVAL);
}

// --- Event Listeners and Initialization ---

setNameBtn.addEventListener('click', () => {
    const newName = nameInput.value.trim();
    if (newName.length > 0) {
        userName = newName;
        localStorage.setItem('chatUserName', userName);
        localStorage.setItem('chatUserId', userId);
        showChat(); // Saves name, switches view, and starts polling
    } else {
        nameInput.placeholder = "Please enter a valid name!";
    }
});

sendBtn.addEventListener('click', sendMessage);

messageInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter' && !sendBtn.disabled) {
        sendMessage();
    }
});

// Initial check when window loads
window.onload = function() {
    if (userName) {
        showChat();
    } else {
        showNameModal();
    }
    localStorage.setItem('chatUserId', userId);
}