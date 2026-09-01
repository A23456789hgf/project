document.addEventListener('DOMContentLoaded', function() {
    // Check if the chat widget exists on the page
    const chatWidget = document.getElementById('chat-widget');
    if (!chatWidget) return;

    // Elements
    const toggleBtn = document.getElementById('chat-widget-toggle');
    const chatWindow = document.getElementById('chat-window');
    const globalUnreadBadge = document.getElementById('chat-global-unread');
    const closeBtn = document.getElementById('chat-close-btn');
    const backBtn = document.getElementById('chat-back-btn');
    
    // Views
    const usersView = document.getElementById('chat-users-view');
    const messagesView = document.getElementById('chat-messages-view');
    const usersListEl = document.getElementById('chat-users-list');
    const messagesListEl = document.getElementById('chat-messages-list');
    const chatHeaderTitle = document.getElementById('chat-header-title');
    
    // Inputs
    const searchInput = document.getElementById('chat-search-input');
    const messageInput = document.getElementById('chat-message-input');
    const sendBtn = document.getElementById('chat-send-btn');

    // State
    let isOpen = false;
    let activeChatUserId = null;
    let lastMessageId = 0;
    let pollingTimer = null;
    let isFetching = false;
    let currentUserId = window.chatConfig ? window.chatConfig.userId : null;
    let isFirstPoll = true;

    // --- Adaptive Polling Constants ---
    const POLL_INTERVAL_OPEN = 2000;   // 2 seconds when chat window is open
    const POLL_INTERVAL_CLOSED = 8000; // 8 seconds when chat window is closed

    // Initialize
    startPolling();
    loadUsers();

    // --- Event Listeners ---
    toggleBtn.addEventListener('click', toggleChat);
    closeBtn.addEventListener('click', toggleChat);
    
    backBtn.addEventListener('click', function() {
        showUsersView();
        loadUsers();
    });

    searchInput.addEventListener('input', debounce(function(e) {
        loadUsers(e.target.value);
    }, 500));

    sendBtn.addEventListener('click', sendMessage);
    messageInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    // --- Core Functions ---

    function toggleChat() {
        isOpen = !isOpen;
        if (isOpen) {
            chatWindow.classList.add('open');
            if (activeChatUserId) {
                // We are already in a chat
                scrollToBottom();
            } else {
                // Load users fresh when opened
                loadUsers();
            }
        } else {
            chatWindow.classList.remove('open');
        }
        
        // Adjust polling interval immediately
        adjustPollingInterval();
    }

    function showUsersView() {
        usersView.classList.remove('hidden-left');
        messagesView.classList.remove('active');
        backBtn.style.display = 'none';
        chatHeaderTitle.innerHTML = '<i class="ri-message-3-line"></i> المحادثات';
        activeChatUserId = null;
    }

    function showMessagesView(user) {
        usersView.classList.add('hidden-left');
        messagesView.classList.add('active');
        backBtn.style.display = 'block';
        chatHeaderTitle.innerHTML = user.name;
        activeChatUserId = user.id;
        
        // Clear global unread badge if we open the chat and there are unread
        loadConversation(user.id);
    }

    // --- API Calls ---

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    }

    function loadUsers(searchQuery = '') {
        usersListEl.innerHTML = '<div class="chat-loader"><i class="ri-loader-4-line ri-spin"></i> جاري التحميل...</div>';
        
        fetch(`/chat/users?search=${encodeURIComponent(searchQuery)}`)
            .then(res => res.json())
            .then(data => {
                usersListEl.innerHTML = '';
                
                const allUsers = [...data.recent, ...data.users];
                
                // Remove duplicates if any (due to recent vs all split)
                const uniqueUsers = Array.from(new Map(allUsers.map(u => [u.id, u])).values());

                if (uniqueUsers.length === 0) {
                    usersListEl.innerHTML = '<div class="chat-empty-state"><i class="ri-user-search-line"></i><span>لا يوجد مستخدمين</span></div>';
                    return;
                }

                uniqueUsers.forEach(user => {
                    const item = document.createElement('div');
                    item.className = 'chat-user-item';
                    
                    const avatarContent = user.signature_path 
                        ? `<img src="/storage/${user.signature_path}" alt="User">` 
                        : user.name.charAt(0);

                    const unreadBadge = user.unread_count > 0 
                        ? `<span class="chat-user-badge" id="badge-user-${user.id}">${user.unread_count}</span>` 
                        : `<span class="chat-user-badge" id="badge-user-${user.id}" style="display:none">0</span>`;

                    item.innerHTML = `
                        <div class="chat-user-avatar">${avatarContent}</div>
                        <div class="chat-user-info">
                            <div class="chat-user-name">${user.name}</div>
                            <div class="chat-user-id">${user.user_id}</div>
                        </div>
                        ${unreadBadge}
                    `;
                    
                    item.addEventListener('click', () => showMessagesView(user));
                    usersListEl.appendChild(item);
                });
            })
            .catch(err => {
                console.error('Error loading users:', err);
                usersListEl.innerHTML = '<div class="chat-loader" style="color:red">خطأ في التحميل</div>';
            });
    }

    function loadConversation(userId) {
        messagesListEl.innerHTML = '<div class="chat-loader"><i class="ri-loader-4-line ri-spin"></i> جاري تحميل الرسائل...</div>';
        
        fetch(`/chat/messages/${userId}`)
            .then(res => res.json())
            .then(messages => {
                messagesListEl.innerHTML = '';
                
                if (messages.length === 0) {
                    messagesListEl.innerHTML = '<div class="chat-empty-state"><i class="ri-chat-3-line"></i><span>لا توجد رسائل سابقة. ابدأ المحادثة الآن!</span></div>';
                    lastMessageId = 0;
                    return;
                }

                messages.forEach(msg => {
                    appendMessage(msg, false);
                    if (msg.id > lastMessageId) {
                        lastMessageId = msg.id;
                    }
                });
                
                scrollToBottom();
                
                // Hide badge for this user since we read them
                const userBadge = document.getElementById(`badge-user-${userId}`);
                if (userBadge) {
                    userBadge.style.display = 'none';
                    userBadge.textContent = '0';
                }
            })
            .catch(err => console.error('Error loading messages:', err));
    }

    function sendMessage() {
        const text = messageInput.value.trim();
        if (!text || !activeChatUserId) return;
        
        messageInput.value = '';
        messageInput.focus();
        sendBtn.disabled = true;

        // Optimistic UI update
        const tempMsg = {
            id: 'temp-' + Date.now(),
            sender_id: currentUserId,
            receiver_id: activeChatUserId,
            message: text,
            created_at: new Date().toISOString()
        };
        appendMessage(tempMsg, true);
        
        // Remove empty state if present
        const emptyState = messagesListEl.querySelector('.chat-empty-state');
        if (emptyState) emptyState.remove();

        fetch('/chat/messages', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                receiver_id: activeChatUserId,
                message: text
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Update temp ID with real ID to prevent polling duplication
                const tempEl = document.getElementById('msg-' + tempMsg.id);
                if (tempEl) tempEl.id = 'msg-' + data.message.id;
                
                if (data.message.id > lastMessageId) {
                    lastMessageId = data.message.id;
                }
            }
        })
        .catch(err => console.error('Error sending message:', err))
        .finally(() => {
            sendBtn.disabled = false;
        });
    }

    // --- Real-time Polling ---

    function startPolling() {
        stopPolling();
        pollServer(); // Initial call
        
        const interval = isOpen ? POLL_INTERVAL_OPEN : POLL_INTERVAL_CLOSED;
        pollingTimer = setInterval(pollServer, interval);
        console.log(`[Chat] Adaptive Polling Started - Interval: ${interval}ms`);
    }

    function stopPolling() {
        if (pollingTimer) {
            clearInterval(pollingTimer);
            pollingTimer = null;
        }
    }

    function adjustPollingInterval() {
        // Restart polling with the new interval based on window state
        startPolling();
    }

    function pollServer() {
        if (isFetching) return;
        isFetching = true;

        const url = new URL(window.location.origin + '/chat/poll');
        url.searchParams.append('last_id', lastMessageId);
        if (activeChatUserId) {
            url.searchParams.append('active_user_id', activeChatUserId);
        }

        fetch(url)
            .then(res => res.json())
            .then(data => {
                // 1. Update Global Unread Badge
                if (data.total_unread > 0) {
                    globalUnreadBadge.textContent = data.total_unread;
                    globalUnreadBadge.style.display = 'block';
                } else {
                    globalUnreadBadge.style.display = 'none';
                }

                // 2. Update specific user badges in the user list view
                for (const [senderId, count] of Object.entries(data.unread_by_sender || {})) {
                    const badge = document.getElementById(`badge-user-${senderId}`);
                    if (badge && activeChatUserId != senderId) {
                        badge.textContent = count;
                        badge.style.display = 'block';
                    }
                }

                // 3. Inject new messages if we are currently chatting with the sender
                if (data.new_messages && data.new_messages.length > 0) {
                    let scrolled = false;
                    let playedSound = false;
                    
                    const soundKey = 'played_chat_sounds_' + (currentUserId || 'guest');
                    let playedSounds = [];
                    try {
                        playedSounds = JSON.parse(localStorage.getItem(soundKey) || '[]');
                        if (!Array.isArray(playedSounds)) playedSounds = [];
                    } catch (e) {
                        playedSounds = [];
                    }

                    data.new_messages.forEach(msg => {
                        // Keep track of the highest ID we've seen globally
                        if (msg.id > lastMessageId) {
                            lastMessageId = msg.id;
                        }
                        
                        // Play sound if there's a new message from someone else
                        if (msg.sender_id != currentUserId) {
                            if (!isFirstPoll && !playedSounds.includes(msg.id) && !playedSound) {
                                playNotificationSound();
                                playedSound = true;
                            }
                            
                            // Mark this message ID as played
                            if (!playedSounds.includes(msg.id)) {
                                playedSounds.push(msg.id);
                            }
                        }
                        
                        // If the message is from the active user, inject it
                        if (msg.sender_id == activeChatUserId && !document.getElementById('msg-' + msg.id)) {
                            // Remove empty state if present
                            const emptyState = messagesListEl.querySelector('.chat-empty-state');
                            if (emptyState) emptyState.remove();
                            
                            appendMessage(msg, false);
                            scrolled = true;
                        } else if (!activeChatUserId && isOpen) {
                            // If chat list is open, we should refresh the users list to move recent chat up
                            loadUsers(searchInput.value);
                        }
                    });
                    
                    // Save updated played sounds to localStorage (limit size to 100 to avoid bloat)
                    if (playedSounds.length > 100) {
                        playedSounds = playedSounds.slice(-100);
                    }
                    try {
                        localStorage.setItem(soundKey, JSON.stringify(playedSounds));
                    } catch (e) {
                        console.error('Failed to save played_chat_sounds to localStorage', e);
                    }
                    
                    if (scrolled) scrollToBottom();
                }
                isFirstPoll = false;
            })
            .catch(err => console.error('[Chat Polling Error]:', err))
            .finally(() => {
                isFetching = false;
            });
    }

    // --- Helpers ---

    function appendMessage(msg, scroll = true) {
        const isSent = (msg.sender_id == currentUserId);
        const div = document.createElement('div');
        div.id = 'msg-' + msg.id;
        div.className = `chat-message ${isSent ? 'sent' : 'received'}`;
        
        const date = new Date(msg.created_at);
        const timeStr = date.getHours() + ':' + date.getMinutes().toString().padStart(2, '0');
        
        div.innerHTML = `
            <div class="chat-message-body">${escapeHTML(msg.message)}</div>
            <div class="chat-message-time">${timeStr}</div>
        `;
        
        messagesListEl.appendChild(div);
        if (scroll) scrollToBottom();
    }

    function scrollToBottom() {
        messagesListEl.scrollTop = messagesListEl.scrollHeight;
    }

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    function escapeHTML(str) {
        return str.replace(/[&<>'"]/g, 
            tag => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                "'": '&#39;',
                '"': '&quot;'
            }[tag] || tag)
        );
    }

    function playNotificationSound() {
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) return;
            
            const audioCtx = new AudioContext();
            const oscillator = audioCtx.createOscillator();
            const gainNode = audioCtx.createGain();

            // Create a short, pleasant "pop" sound
            oscillator.type = 'sine';
            oscillator.frequency.setValueAtTime(600, audioCtx.currentTime);
            oscillator.frequency.exponentialRampToValueAtTime(1200, audioCtx.currentTime + 0.08);

            // Volume envelope
            gainNode.gain.setValueAtTime(0, audioCtx.currentTime);
            gainNode.gain.linearRampToValueAtTime(0.2, audioCtx.currentTime + 0.02);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.2);

            oscillator.connect(gainNode);
            gainNode.connect(audioCtx.destination);

            oscillator.start(audioCtx.currentTime);
            oscillator.stop(audioCtx.currentTime + 0.25);
        } catch (e) {
            console.warn('Web Audio API not supported', e);
        }
    }
});
