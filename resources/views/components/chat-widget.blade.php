@auth
<div id="chat-widget" class="chat-widget-container">
    <!-- Chat Button -->
    <button id="chat-widget-toggle" class="chat-widget-toggle">
        <i class="ri-chat-3-line"></i>
        <span id="chat-global-unread" class="chat-global-unread" style="display:none">0</span>
    </button>

    <!-- Chat Window -->
    <div id="chat-window" class="chat-window">
        
        <!-- Header -->
        <div class="chat-header">
            <div class="chat-header-title" id="chat-header-title">
                <i class="ri-message-3-line"></i> المحادثات
            </div>
            <div style="display: flex; align-items: center;">
                <button id="chat-back-btn" class="chat-back-btn"><i class="ri-arrow-right-line"></i> رجوع</button>
                <button id="chat-close-btn" class="chat-close-btn"><i class="ri-close-line"></i></button>
            </div>
        </div>

        <div class="chat-views-container">
            
            <!-- View 1: Users List -->
            <div id="chat-users-view" class="chat-view chat-users-view">
                <div class="chat-search-box">
                    <input type="text" id="chat-search-input" class="chat-search-input" placeholder="ابحث عن مستخدم...">
                </div>
                <div id="chat-users-list" class="chat-users-list">
                    <!-- Users injected via JS -->
                </div>
            </div>

            <!-- View 2: Conversation View -->
            <div id="chat-messages-view" class="chat-view chat-messages-view">
                <div id="chat-messages-list" class="chat-messages-list">
                    <!-- Messages injected via JS -->
                </div>
                <div class="chat-input-area">
                    <textarea id="chat-message-input" class="chat-input-box" placeholder="اكتب رسالة..." rows="1"></textarea>
                    <button id="chat-send-btn" class="chat-send-btn">
                        <i class="ri-send-plane-fill"></i>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    // Configuration for chat.js
    window.chatConfig = {
        userId: {{ auth()->id() }},
    };
</script>
@endauth
