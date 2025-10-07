// KingLang Chat Widget Core Functionality
class KingLangChat {
    constructor() {
        this.conversationId = null;
        this.isOpen = false;
        this.isAdminConnected = false;
        this.conversationEnded = false;
        this.pollingInterval = null;
        
        this.init();
    }
    
    init() {
        this.createWidget();
        this.bindEvents();
        this.loadStoredState();
        this.initializeConversation();
        this.startPolling();
    }
    
    createWidget() {
        const widgetHTML = `
            <div class="chat-widget-container" id="chat-widget">
                <div class="chat-bubble" id="chat-bubble">
                    <i class="fas fa-comments"></i>
                    <div class="unread-badge" id="unread-badge">0</div>
                </div>
                
                <div class="chat-panel" id="chat-panel">
                    <div class="chat-container">
                        <div class="chat-header">
                            <h4><i class="fas fa-bus"></i> KingLang Support</h4>
                            <div class="header-actions">
                                <button class="btn btn-assistance" onclick="chatWidget.requestHumanAssistance()">
                                    <i class="fas fa-user-headset"></i> Get Help
                                </button>
                                <button class="btn btn-end-chat" onclick="chatWidget.endConversation()" id="end-chat-btn" style="display: none;">
                                    <i class="fas fa-sign-out-alt"></i> End Chat
                                </button>
                                <div class="chat-close" onclick="chatWidget.toggleChat()">
                                    <i class="fas fa-times"></i>
                                </div>
                            </div>
                        </div>

                        <div class="connection-status" id="connection-status">
                            <span class="status-indicator"></span>
                            <span class="status-text">Connected to customer service agent</span>
                        </div>

                        <div class="quick-questions">
                            <h5>Quick Questions</h5>
                            <div class="question-buttons">
                                <button class="btn-question" onclick="chatWidget.askPredefinedQuestion('What are your rental rates?')">
                                    <i class="fas fa-dollar-sign"></i> Pricing
                                </button>
                                <button class="btn-question" onclick="chatWidget.askPredefinedQuestion('How do I make a booking?')">
                                    <i class="fas fa-calendar-check"></i> Booking
                                </button>
                                <button class="btn-question" onclick="chatWidget.askPredefinedQuestion('What is your cancellation policy?')">
                                    <i class="fas fa-times-circle"></i> Cancellation
                                </button>
                                <button class="btn-question" onclick="chatWidget.askPredefinedQuestion('How can I contact you?')">
                                    <i class="fas fa-phone"></i> Contact
                                </button>
                                <button class="btn-question" onclick="chatWidget.askPredefinedQuestion('What types of buses do you have?')">
                                    <i class="fas fa-bus"></i> Fleet
                                </button>
                            </div>
                        </div>
                        
                        <div class="chat-messages" id="chat-messages"></div>
                        
                        <div class="message-input-area">
                            <div class="input-group">
                                <input type="text" id="message-input" placeholder="Type your message..." onkeypress="chatWidget.handleKeyPress(event)">
                                <button type="button" class="send-button" onclick="chatWidget.sendMessage()">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', widgetHTML);
    }
    
    bindEvents() {
        const chatBubble = document.getElementById('chat-bubble');
        chatBubble.addEventListener('click', () => this.toggleChat());
    }
    
    async initializeConversation() {
        try {
            const response = await fetch('/api/chat/conversation', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin'
            });
            
            if (response.ok) {
                const data = await response.json();
                this.conversationId = data.conversation_id;
                localStorage.setItem('kinglang_conversation_id', this.conversationId);
                this.loadMessages();
            }
        } catch (error) {
            console.error('Error initializing conversation:', error);
        }
    }
    
    async loadMessages() {
        if (!this.conversationId) return;
        
        try {
            const response = await fetch(`/api/chat/messages/${this.conversationId}`, {
                credentials: 'same-origin'
            });
            
            if (response.ok) {
                const data = await response.json();
                const chatMessages = document.getElementById('chat-messages');
                chatMessages.innerHTML = '';
                
                data.messages.forEach(message => this.displayMessage(message));
                this.scrollToBottom();
            }
        } catch (error) {
            console.error('Error loading messages:', error);
        }
    }
    
    toggleChat() {
        this.isOpen = !this.isOpen;
        const chatPanel = document.getElementById('chat-panel');
        
        if (this.isOpen) {
            chatPanel.classList.add('active');
            this.hideUnreadBadge();
            this.scrollToBottom();
        } else {
            chatPanel.classList.remove('active');
        }
        
        localStorage.setItem('kinglang_chat_open', this.isOpen.toString());
    }
    
    displayMessage(message) {
        const chatMessages = document.getElementById('chat-messages');
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${message.sender_type}-message`;
        
        let senderName = '';
        switch(message.sender_type) {
            case 'client': senderName = 'You'; break;
            case 'admin': senderName = 'Customer Service'; break;
            case 'bot': senderName = 'KingLang Assistant'; break;
            case 'system': senderName = 'System'; break;
        }
        
        const timestamp = new Date(message.sent_at).toLocaleTimeString('en-US', {
            hour: '2-digit', minute: '2-digit', hour12: true
        });
        
        messageDiv.innerHTML = `
            <div class="message-content">
                <div class="message-meta">
                    <strong>${senderName}   </strong><small class="text-muted"> • ${timestamp}</small>
                </div>
                <div class="message-text">${message.message}</div>
            </div>
        `;
        
        chatMessages.appendChild(messageDiv);
        
        // Handle admin connection
        if (message.sender_type === 'admin') {
            this.isAdminConnected = true;
            this.showAdminConnected();
        }
    }
    
    async sendMessage() {
        const input = document.getElementById('message-input');
        const message = input.value.trim();
        
        if (!message || !this.conversationId) return;
        
        // Display immediately
        this.displayMessage({
            id: Date.now(),
            sender_type: 'client',
            message: message,
            sent_at: new Date().toISOString()
        });
        
        input.value = '';
        this.scrollToBottom();
        
        try {
            const response = await fetch('/api/chat/send', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({
                    conversation_id: this.conversationId,
                    message: message
                })
            });
            
            if (response.ok) {
                const data = await response.json();
                if (data.bot_response) {
                    this.displayMessage(data.bot_response);
                    this.scrollToBottom();
                }
            }
        } catch (error) {
            console.error('Error sending message:', error);
        }
    }
    
    handleKeyPress(event) {
        if (event.key === 'Enter') {
            this.sendMessage();
        }
    }
    
    askPredefinedQuestion(question) {
        document.getElementById('message-input').value = question;
        this.sendMessage();
    }
    
    async requestHumanAssistance() {
        try {
            await fetch('/api/chat/request-human', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ conversation_id: this.conversationId })
            });
            this.loadMessages();
        } catch (error) {
            console.error('Error requesting human assistance:', error);
        }
    }
    
    async endConversation() {
        if (!this.conversationId) {
            console.error('No active conversation to end');
            return;
        }
        
        // Show confirmation dialog
        if (!confirm('Are you sure you want to end this conversation? This action cannot be undone.')) {
            return;
        }
        
        try {
            console.log('🔚 Ending conversation:', this.conversationId);
            
            const response = await fetch('/api/chat/end', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ conversation_id: this.conversationId })
            });
            
            if (response.ok) {
                const data = await response.json();
                
                if (data.success) {
                    console.log('✅ Conversation ended successfully');
                    
                    // Mark conversation as ended
                    this.conversationEnded = true;
                    this.isAdminConnected = false;
                    
                    // Hide the end chat button and show assistance button
                    document.getElementById('end-chat-btn').style.display = 'none';
                    document.querySelector('.btn-assistance').style.display = 'flex';
                    
                    // Update connection status
                    document.getElementById('connection-status').classList.remove('admin-connected');
                    document.querySelector('.status-text').textContent = 'Connected to KingLang Assistant';
                    
                    // Display system message about conversation ending
                    this.displayMessage({
                        id: Date.now(),
                        sender_type: 'system',
                        message: data.message || 'This conversation has been ended. Thank you for contacting KingLang Support!',
                        sent_at: new Date().toISOString()
                    });
                    
                    // Disable message input temporarily
                    const messageInput = document.getElementById('message-input');
                    const sendButton = document.querySelector('.send-button');
                    messageInput.disabled = true;
                    messageInput.placeholder = 'Conversation ended. Starting new conversation...';
                    sendButton.disabled = true;
                    
                    this.scrollToBottom();
                    
                    // Auto-restart conversation after 3 seconds (like the original AI chatbot)
                    setTimeout(() => {
                        this.restartConversation();
                    }, 3000);
                    
                } else {
                    console.error('Failed to end conversation:', data.message);
                    alert('Failed to end conversation. Please try again.');
                }
            } else {
                throw new Error('Failed to end conversation');
            }
            
        } catch (error) {
            console.error('Error ending conversation:', error);
            alert('An error occurred while ending the conversation. Please try again.');
        }
    }
    
    async restartConversation() {
        console.log('🔄 Restarting conversation...');
        
        // Clear conversation state
        this.conversationId = null;
        this.conversationEnded = false;
        this.isAdminConnected = false;
        
        // Clear stored conversation ID
        localStorage.removeItem('kinglang_conversation_id');
        
        // Re-enable message input
        const messageInput = document.getElementById('message-input');
        const sendButton = document.querySelector('.send-button');
        messageInput.disabled = false;
        messageInput.placeholder = 'Type your message...';
        sendButton.disabled = false;
        
        // Display welcome message
        this.displayMessage({
            id: Date.now(),
            sender_type: 'system',
            message: 'New conversation started. How can we help you today?',
            sent_at: new Date().toISOString()
        });
        
        // Initialize new conversation
        await this.initializeConversation();
        
        console.log('✅ New conversation started');
    }
    
    showAdminConnected() {
        document.getElementById('connection-status').classList.add('admin-connected');
        document.querySelector('.btn-assistance').style.display = 'none';
        document.getElementById('end-chat-btn').style.display = 'flex';
    }
    
    scrollToBottom() {
        const chatMessages = document.getElementById('chat-messages');
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    hideUnreadBadge() {
        document.getElementById('unread-badge').style.display = 'none';
    }
    
    loadStoredState() {
        this.conversationId = localStorage.getItem('kinglang_conversation_id');
        this.isOpen = localStorage.getItem('kinglang_chat_open') === 'true';
        
        if (this.isOpen) {
            document.getElementById('chat-panel').classList.add('active');
        }
    }
    
    startPolling() {
        this.pollingInterval = setInterval(() => {
            if (this.conversationId) this.checkForNewMessages();
        }, 5000);
    }
    
    async checkForNewMessages() {
        try {
            const response = await fetch(`/api/chat/messages/${this.conversationId}`, {
                credentials: 'same-origin'
            });
            
            if (response.ok) {
                const data = await response.json();
                const currentMessages = document.querySelectorAll('.message').length;
                
                if (data.messages.length > currentMessages) {
                    const newMessages = data.messages.slice(currentMessages);
                    newMessages.forEach(message => {
                        this.displayMessage(message);
                        if (!this.isOpen && message.sender_type !== 'client') {
                            this.showUnreadBadge();
                        }
                    });
                    this.scrollToBottom();
                }
            }
        } catch (error) {
            console.error('Error checking for new messages:', error);
        }
    }
    
    showUnreadBadge() {
        const badge = document.getElementById('unread-badge');
        let count = parseInt(badge.textContent) || 0;
        badge.textContent = ++count;
        badge.style.display = 'flex';
    }
}

// Initialize chat widget
let chatWidget;
document.addEventListener('DOMContentLoaded', function() {
    if (typeof userLoggedIn !== 'undefined' && userLoggedIn) {
        chatWidget = new KingLangChat();
    }
});