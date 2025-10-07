<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../../../public/images/main-logo-icon.png" type="">
    <style>
        /* KingLang Chat Widget Styles */
        .chat-widget-container {
            position: fixed;
            bottom: 20px;

            right: 20px;

            z-index: 1000;

            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;

        }



        /* Chat Bubble */

        .chat-bubble {

            background: linear-gradient(135deg, #2b7de9 0%, #1e5bb8 100%);

            width: 60px;

            height: 60px;

            border-radius: 50%;

            display: flex;

            justify-content: center;

            align-items: center;

            color: white;

            cursor: pointer;

            box-shadow: 0 4px 12px rgba(43, 125, 233, 0.4);

            transition: all 0.3s ease;

            position: relative;

            z-index: 1001;

        }



        .chat-bubble:hover {

            transform: translateY(-2px) scale(1.05);

            box-shadow: 0 8px 20px rgba(43, 125, 233, 0.5);

        }



        .chat-bubble i {

            font-size: 24px;

        }



        .unread-badge {

            position: absolute;

            top: -5px;

            right: -5px;

            background: #ff4757;

            color: white;

            border-radius: 50%;

            width: 20px;

            height: 20px;

            font-size: 12px;

            font-weight: bold;

            display: none;

            align-items: center;

            justify-content: center;

            border: 2px solid white;

        }



        /* Chat Panel */

        .chat-panel {

            position: fixed;

            bottom: 90px;

            right: 20px;

            width: 380px;

            height: 550px;

            background: white;

            border-radius: 12px;

            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);

            display: flex;

            flex-direction: column;

            overflow: hidden;

            z-index: 999;

            transform: translateY(100%) scale(0.8);

            opacity: 0;

            visibility: hidden;

            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);

        }



        .chat-panel.active {

            transform: translateY(0) scale(1);

            opacity: 1;

            visibility: visible;

        }



        .chat-container {

            display: flex;

            flex-direction: column;

            height: 100%;

        }



        /* Chat Header */

        .chat-header {

            background: linear-gradient(135deg, #2b7de9 0%, #1e5bb8 100%);

            color: white;

            padding: 16px;

            display: flex;

            justify-content: space-between;

            align-items: center;

            border-radius: 12px 12px 0 0;

        }



        .chat-header h4 {

            margin: 0;

            font-size: 16px;

            font-weight: 600;

            display: flex;

            align-items: center;

            gap: 8px;

        }



        .header-actions {

            display: flex;

            align-items: center;

            gap: 8px;

        }



        .btn-assistance, .btn-end-chat {

            background: rgba(255, 255, 255, 0.2);

            color: white;

            border: none;

            padding: 6px 12px;

            border-radius: 6px;

            font-size: 12px;

            font-weight: 500;

            cursor: pointer;

            transition: all 0.2s ease;

            display: flex;

            align-items: center;

            gap: 4px;

        }



        .btn-assistance:hover, .btn-end-chat:hover {

            background: rgba(255, 255, 255, 0.3);

        }



        .btn-assistance:disabled {

            background: rgba(255, 255, 255, 0.1) !important;

            cursor: not-allowed !important;

            opacity: 0.6 !important;

        }



        .btn-assistance:disabled:hover {

            background: rgba(255, 255, 255, 0.1) !important;

            transform: none !important;

        }



        .btn-end-chat {

            background: rgba(231, 76, 60, 0.8);

        }



        .btn-end-chat:hover {

            background: rgba(231, 76, 60, 1);

        }



        .chat-close {

            background: none;

            border: none;

            color: rgba(255, 255, 255, 0.8);

            font-size: 18px;

            cursor: pointer;

            padding: 4px;

            border-radius: 4px;

            transition: all 0.2s ease;

        }



        .chat-close:hover {

            color: white;

            background: rgba(255, 255, 255, 0.1);

        }



        /* Connection Status */

        .connection-status {

            display: none;

            background: #d4edda;

            color: #155724;

            padding: 8px 16px;

            font-size: 12px;

            text-align: center;

            align-items: center;

            justify-content: center;

            gap: 6px;

            border-bottom: 1px solid #c3e6cb;

        }



        .connection-status.admin-connected {

            display: flex;

        }



        .status-indicator {

            width: 8px;

            height: 8px;

            background: #28a745;

            border-radius: 50%;

        }



        /* Quick Questions */

        .quick-questions {

            background: #f8f9fa;

            padding: 12px 16px;

            border-bottom: 1px solid #e9ecef;

        }



        .quick-questions h5 {

            margin: 0 0 8px 0;

            font-size: 12px;

            font-weight: 600;

            color: #6c757d;

            text-transform: uppercase;

            letter-spacing: 0.5px;

        }



        .question-buttons {

            display: flex;

            flex-wrap: wrap;

            gap: 6px;

        }



        .btn-question {

            background: #2b7de9;

            color: white;

            border: none;

            padding: 4px 8px;

            border-radius: 12px;

            font-size: 11px;

            font-weight: 500;

            cursor: pointer;

            transition: all 0.2s ease;

            display: flex;

            align-items: center;

            gap: 4px;

        }



        .btn-question:hover {

            background: #1e5bb8;

            transform: translateY(-1px);

        }



        /* Chat Messages */

        .chat-messages {

            flex: 1;

            overflow-y: auto;

            padding: 16px;

            background: #f8f9fa;

            display: flex;

            flex-direction: column;

            gap: 12px;

        }



        .message {

            display: flex;

            flex-direction: column;

            max-width: 85%;

            animation: fadeInUp 0.3s ease;

        }



        .message.client-message {

            align-self: flex-end;

            margin-left: auto;

        }



        .message.bot-message,

        .message.admin-message,

        .message.system-message {

            align-self: flex-start;

            margin-right: auto;

        }



        .message-content {

            padding: 10px 14px;

            border-radius: 12px;

            word-wrap: break-word;

            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);

        }



        .client-message .message-content {

            background: #2b7de9;

            color: white;

            border-radius: 12px 12px 4px 12px;

        }



        .bot-message .message-content {

            background: white;

            color: #333;

            border: 1px solid #e9ecef;

            border-left: 3px solid #2b7de9;

        }



        .admin-message .message-content {

            background: #28a745;

            color: white;

            border-radius: 12px 12px 12px 4px;

        }



        .system-message .message-content {

            background: #f8f9fa;

            color: #6c757d;

            border: 1px solid #dee2e6;

            font-style: italic;

            text-align: center;

            border-radius: 8px;

        }



        .message-meta {

            display: flex;

            align-items: center;

            justify-content: space-between;

            margin-bottom: 4px;

            font-size: 11px;

            opacity: 0.9;

        }



        .client-message .message-meta {

            color: rgba(255, 255, 255, 0.9);

        }



        .message-text {

            font-size: 14px;
            line-height: 1.4;
            margin: 0;

        }



        /* Message Input */

        .message-input-area {

            padding: 16px;

            background: white;

            border-top: 1px solid #e9ecef;

            border-radius: 0 0 12px 12px;

        }



        .input-group {

            display: flex;

            gap: 8px;

            align-items: center;

        }



        .input-group #message-input {

            flex: 1;

            padding: 10px 14px;

            border: 1px solid #ddd;

            border-radius: 20px;

            outline: none;

            font-size: 14px;

            background: #f8f9fa;

            transition: all 0.2s ease;

        }



        .input-group input:focus {

            border-color: #2b7de9;

            background: white;

            box-shadow: 0 0 0 3px rgba(43, 125, 233, 0.1);

        }



        .input-group input:disabled {

            background-color: #f1f3f4;

            color: #6c757d;

            cursor: not-allowed;

            opacity: 0.7;

            border-color: #dee2e6;

        }



        .send-button:disabled {

            background: #6c757d !important;

            cursor: not-allowed;

            opacity: 0.7;

        }



        .send-button:disabled:hover {

            background: #6c757d !important;

            transform: none;

        }



        .send-button {

            width: 40px;

            height: 40px;

            background: #2b7de9;

            color: white;

            border: none;

            border-radius: 50%;

            cursor: pointer;

            display: flex;

            align-items: center;

            justify-content: center;

            transition: all 0.2s ease;

            flex-shrink: 0;

        }



        .send-button:hover {

            background: #1e5bb8;

            transform: scale(1.05);

        }



        .send-button i {

            font-size: 16px;

        }



        /* Animations */

        @keyframes fadeInUp {

            from {

                opacity: 0;

                transform: translateY(10px);

            }

            to {

                opacity: 1;

                transform: translateY(0);

            }

        }



        /* Scrollbar */

        .chat-messages::-webkit-scrollbar {

            width: 6px;

        }



        .chat-messages::-webkit-scrollbar-track {

            background: #f1f1f1;

            border-radius: 3px;

        }



        .chat-messages::-webkit-scrollbar-thumb {

            background: #c1c1c1;

            border-radius: 3px;

        }



        .chat-messages::-webkit-scrollbar-thumb:hover {

            background: #a8a8a8;

        }



        /* Responsive Design */

        @media (max-width: 480px) {

            .chat-widget-container {

                bottom: 10px;

                right: 10px;

            }

            

            .chat-panel {

                width: calc(100vw - 20px);

                height: calc(100vh - 100px);

                right: 10px;

                bottom: 80px;

                border-radius: 12px 12px 0 0;

            }

            

            .chat-bubble {

                width: 56px;

                height: 56px;

            }

            

            .chat-bubble i {

                font-size: 20px;

            }

            

            .btn-question {

                font-size: 10px;

                padding: 3px 6px;

            }

            

            .message {

                max-width: 95%;

            }

            

            .chat-header {

                padding: 12px 16px;

            }

            

            .chat-header h4 {

                font-size: 14px;

            }

            

            .btn-assistance, .btn-end-chat {

                padding: 4px 8px;

                font-size: 11px;

            }

            

            .btn-text {

                display: none;

            }

        }



        /* Button styles in bot messages */

        .message-text .btn {

            margin: 4px 2px;

            padding: 6px 12px;

            border-radius: 16px;

            border: none;

            font-size: 12px;

            cursor: pointer;

            transition: all 0.2s ease;

        }



        .message-text .btn-primary {

            background: #2b7de9;

            color: white;

        }



        .message-text .btn-primary:hover {

            background: #1e5bb8;

        }



        .message-text .btn-outline-secondary {

            background: transparent;

            color: #6c757d;

            border: 1px solid #6c757d;

        }



        .message-text .btn-outline-secondary:hover {

            background: #6c757d;

            color: white;

        }



        .message-text .button-container {

            margin-top: 8px;

            display: flex;

            flex-direction: column;

            gap: 6px;

        }



        .message-text .mt-2 {

            margin-top: 8px;

        }
    </style>
</head>
<body>
    
<script>
    // KingLang Chat Widget Core Functionality

    class KingLangChat {

        constructor() {

            console.log('KingLangChat constructor called');
            
            this.conversationId = null;

            this.isOpen = false;

            this.isAdminConnected = false;

            this.conversationEnded = false;

            this.pollingInterval = null;

            

            this.init();

        }

        

        init() {

            console.log('Initializing KingLangChat...');
            
            this.createWidget();

            this.bindEvents();

            this.loadStoredState();

            this.initializeConversation();

            this.startPolling();
            
            console.log('KingLangChat initialization complete');

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

                                    <button type="button" class="send-button" style="border-radius: 50%;    " onclick="chatWidget.sendMessage()">

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

            console.log('Initializing conversation...');
            
            try {

                const response = await fetch('/api/chat/conversation', {

                    method: 'POST',

                    headers: { 'Content-Type': 'application/json' },

                    credentials: 'same-origin'

                });

                console.log('Conversation API response status:', response.status);

                if (response.ok) {

                    const data = await response.json();
                    console.log('Conversation data received:', data);
                    
                    this.conversationId = data.conversation_id;

                    localStorage.setItem('kinglang_conversation_id', this.conversationId);
                    
                    console.log('Conversation ID set:', this.conversationId);

                    this.loadMessages();
                    
                    // Check assistance request state after loading messages
                    this.checkAssistanceRequestState();

                } else {
                    console.error('Failed to initialize conversation, status:', response.status);
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

                        <strong>${senderName}</strong>&nbsp;<small class="text-muted"> • ${timestamp}</small>

                    </div>

                    <div class="message-text">${message.message}</div>

                </div>

            `;

            

            chatMessages.appendChild(messageDiv);
            
            // Add click handlers for buttons in bot messages
            this.attachButtonHandlers(messageDiv);
            
            // Hide quick questions when conversation becomes active
            this.hideQuickQuestionsIfActive();

            

            // Handle admin connection

            if (message.sender_type === 'admin') {

                this.isAdminConnected = true;

                this.showAdminConnected();

            }

        }

        

        async sendMessage() {

            const input = document.getElementById('message-input');

            const message = input.value.trim();

            console.log('Attempting to send message:', message);
            console.log('Conversation ID:', this.conversationId);

            if (!message) {
                console.error('No message to send');
                return;
            }
            
            if (!this.conversationId) {
                console.error('No conversation ID available for sending message');
                return;
            }

            

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

            console.log('Quick question clicked:', question);
            const messageInput = document.getElementById('message-input');
            
            if (!messageInput) {
                console.error('Message input element not found');
                return;
            }
            
            messageInput.value = question;
            
            // If no conversation ID, initialize conversation first
            if (!this.conversationId) {
                console.log('No conversation ID, initializing conversation...');
                this.initializeConversation().then(() => {
                    console.log('Conversation initialized, sending message...');
                    this.sendMessage();
                });
            } else {
                this.sendMessage();
            }

        }

        

        async requestHumanAssistance() {

            const assistanceButton = document.querySelector('.btn-assistance');

            
            // Disable the button immediately to prevent multiple clicks
            assistanceButton.disabled = true;
            assistanceButton.style.opacity = '0.6';
            assistanceButton.style.cursor = 'not-allowed';
            assistanceButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Requesting...';

            try {

                await fetch('/api/chat/request-human', {

                    method: 'POST',

                    headers: { 'Content-Type': 'application/json' },

                    credentials: 'same-origin',

                    body: JSON.stringify({ conversation_id: this.conversationId })

                });

                this.loadMessages();

                // Update button to show request was sent
                assistanceButton.innerHTML = '<i class="fas fa-check"></i> Request Sent';
                
                // Store the assistance request state in localStorage
                localStorage.setItem('kinglang_assistance_requested', 'true');
                localStorage.setItem('kinglang_assistance_conversation_id', this.conversationId);

            } catch (error) {

                console.error('Error requesting human assistance:', error);

                // Re-enable button on error
                assistanceButton.disabled = false;
                assistanceButton.style.opacity = '1';
                assistanceButton.style.cursor = 'pointer';
                assistanceButton.innerHTML = '<i class="fas fa-user-headset"></i> Get Help';
                
                // Clear the assistance request state on error
                localStorage.removeItem('kinglang_assistance_requested');
                localStorage.removeItem('kinglang_assistance_conversation_id');

            }

        }

        

        async endConversation() {

            if (!this.conversationId) {

                console.error('No active conversation to end');

                return;

            }

            

            // Show confirmation dialog

            // if (!confirm('Are you sure you want to end this conversation? This action cannot be undone.')) {

            //     return;

            // }

            

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

                        const assistanceButton = document.querySelector('.btn-assistance');
                        assistanceButton.style.display = 'flex';
                        
                        // Reset assistance button state
                        assistanceButton.disabled = false;
                        assistanceButton.style.opacity = '1';
                        assistanceButton.style.cursor = 'pointer';
                        assistanceButton.innerHTML = '<i class="fas fa-user-headset"></i> Get Help';

                        

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

            
            // Reset assistance button state
            this.resetAssistanceButton();
            
            // Show quick questions for new conversation
            this.showQuickQuestions();

            

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

            const assistanceButton = document.querySelector('.btn-assistance');
            assistanceButton.style.display = 'none';

            document.getElementById('end-chat-btn').style.display = 'flex';
            
            // Clear the assistance request state since admin is now connected
            localStorage.removeItem('kinglang_assistance_requested');
            localStorage.removeItem('kinglang_assistance_conversation_id');

        }

        

        resetAssistanceButton() {

            const assistanceButton = document.querySelector('.btn-assistance');

            if (assistanceButton) {

                assistanceButton.disabled = false;

                assistanceButton.style.opacity = '1';

                assistanceButton.style.cursor = 'pointer';

                assistanceButton.innerHTML = '<i class="fas fa-user-headset"></i> Get Help';

                assistanceButton.style.display = 'flex';

            }
            
            // Clear the assistance request state
            localStorage.removeItem('kinglang_assistance_requested');
            localStorage.removeItem('kinglang_assistance_conversation_id');

        }
        
        

        checkAssistanceRequestState() {

            const assistanceRequested = localStorage.getItem('kinglang_assistance_requested') === 'true';
            const storedConversationId = localStorage.getItem('kinglang_assistance_conversation_id');
            
            // Only apply the disabled state if it's for the same conversation
            if (assistanceRequested && storedConversationId === this.conversationId) {
                
                const assistanceButton = document.querySelector('.btn-assistance');
                
                if (assistanceButton && !this.isAdminConnected) {
                    
                    assistanceButton.disabled = true;
                    assistanceButton.style.opacity = '0.6';
                    assistanceButton.style.cursor = 'not-allowed';
                    assistanceButton.innerHTML = '<i class="fas fa-check"></i> Request Sent';
                    
                }
                
            } else if (storedConversationId !== this.conversationId) {
                
                // Clear the state if it's for a different conversation
                localStorage.removeItem('kinglang_assistance_requested');
                localStorage.removeItem('kinglang_assistance_conversation_id');
                
            }

        }
        
        

        attachButtonHandlers(messageDiv) {

            // Find all buttons within the message
            const buttons = messageDiv.querySelectorAll('.btn');
            
            buttons.forEach(button => {
                
                const buttonText = button.textContent.trim();
                
                if (buttonText.includes('Yes, connect me with an agent') || 
                    buttonText.includes('connect me with an agent')) {
                    
                    button.addEventListener('click', () => {
                        this.requestHumanAssistance();
                    });
                    
                } else if (buttonText.includes('No, I\'ll ask something else') || 
                          buttonText.includes('ask something else')) {
                    
                    button.addEventListener('click', () => {
                        this.handleNoAssistanceRequest();
                    });
                    
                }
                
            });

        }
        
        

        handleNoAssistanceRequest() {

            // Display a message indicating the user will continue with the bot
            this.displayMessage({
                id: Date.now(),
                sender_type: 'system',
                message: 'No problem! Feel free to ask me anything else. I\'m here to help.',
                sent_at: new Date().toISOString()
            });

        }
        
        

        hideQuickQuestionsIfActive() {

            const chatMessages = document.getElementById('chat-messages');
            const quickQuestions = document.querySelector('.quick-questions');
            
            // Count non-system messages (actual conversation messages)
            const conversationMessages = chatMessages.querySelectorAll('.message:not(.system-message)');
            
            // Only hide quick questions if there are more than 2 conversation messages
            // This allows users to use quick questions even after starting a conversation
            if (conversationMessages.length > 2 && quickQuestions) {
                quickQuestions.style.display = 'none';
            }

        }
        
        

        showQuickQuestions() {

            const quickQuestions = document.querySelector('.quick-questions');
            
            if (quickQuestions) {
                quickQuestions.style.display = 'block';
            }

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
            
            // Check if assistance was requested for this conversation
            this.checkAssistanceRequestState();

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

        console.log('DOM loaded, userLoggedIn:', typeof userLoggedIn !== 'undefined' ? userLoggedIn : 'undefined');
        
        if (typeof userLoggedIn !== 'undefined' && userLoggedIn) {

            console.log('Initializing chat widget...');
            chatWidget = new KingLangChat();
            console.log('Chat widget initialized:', chatWidget);

        } else {
            console.log('User not logged in or userLoggedIn variable not defined');
        }

    });
</script>
</body>
</html>