/**
 * Chatbot Widget - Văn hóa Khmer Nam Bộ
 * Tích hợp Cerebras AI với xác thực và lưu lịch sử
 */

class ChatbotWidget {
    constructor() {
        this.isOpen = false;
        this.conversationHistory = [];
        this.apiEndpoint = 'api/chatbot.php';
        this.userAvatar = null;
        this.userName = null;
        this.isLoggedIn = false;
        this.init();
    }

    async init() {
        await this.checkLoginStatus();
        this.createWidget();
        this.attachEventListeners();
        
        if (this.isLoggedIn) {
            await this.loadChatHistory();
        } else {
            this.showLoginRequired();
        }
    }

    async checkLoginStatus() {
        // Kiểm tra session PHP
        try {
            const response = await fetch('api/get-user-info.php');
            const data = await response.json();
            
            if (data.success && data.user) {
                this.isLoggedIn = true;
                this.userName = data.user.name;
                this.userAvatar = data.user.avatar || null;
            }
        } catch (error) {
            console.log('Chưa đăng nhập');
        }
    }

    async loadChatHistory() {
        try {
            const response = await fetch(this.apiEndpoint, {
                method: 'GET'
            });
            const data = await response.json();
            
            if (data.success && data.history && data.history.length > 0) {
                // Hiển thị lịch sử chat
                data.history.forEach(item => {
                    this.addMessage(item.message, item.sender, false);
                });
            } else {
                // Không có lịch sử, hiển thị welcome message
                this.addWelcomeMessage();
            }
        } catch (error) {
            console.error('Lỗi tải lịch sử:', error);
            this.addWelcomeMessage();
        }
    }

    createWidget() {
        const widget = document.createElement('div');
        widget.className = 'chatbot-container';
        widget.innerHTML = `
            <button class="chatbot-toggle" id="chatbotToggle" aria-label="Mở chatbot" title="Chat với AI">
                💬
            </button>
            <div class="chatbot-window" id="chatbotWindow">
                <div class="chatbot-header">
                    <div class="chatbot-header-content">
                        <div class="chatbot-avatar">🤖</div>
                        <div class="chatbot-title">
                            <h3>Trợ lý AI Khmer</h3>
                            <p>Luôn sẵn sàng hỗ trợ bạn</p>
                        </div>
                    </div>
                    <div class="chatbot-header-actions">
                        <button class="chatbot-clear" id="chatbotClear" aria-label="Xóa lịch sử" title="Xóa lịch sử chat">
                            🗑️
                        </button>
                        <button class="chatbot-close" id="chatbotClose" aria-label="Đóng chatbot" title="Đóng">×</button>
                    </div>
                </div>
                <div class="chatbot-messages" id="chatbotMessages"></div>
                <div class="chatbot-input-area">
                    <form class="chatbot-input-form" id="chatbotForm">
                        <input 
                            type="text" 
                            class="chatbot-input" 
                            id="chatbotInput" 
                            placeholder="Hỏi tôi về văn hóa Khmer..."
                            autocomplete="off"
                            maxlength="500"
                            ${!this.isLoggedIn ? 'disabled' : ''}
                        />
                        <button type="submit" class="chatbot-send" id="chatbotSend" title="Gửi" ${!this.isLoggedIn ? 'disabled' : ''}>
                            ➤
                        </button>
                    </form>
                </div>
            </div>
        `;
        document.body.appendChild(widget);
    }

    attachEventListeners() {
        const toggle = document.getElementById('chatbotToggle');
        const close = document.getElementById('chatbotClose');
        const clear = document.getElementById('chatbotClear');
        const form = document.getElementById('chatbotForm');

        toggle.addEventListener('click', () => this.toggleChat());
        close.addEventListener('click', () => this.toggleChat());
        clear.addEventListener('click', () => this.clearHistory());
        form.addEventListener('submit', (e) => this.handleSubmit(e));
    }

    showLoginRequired() {
        const messagesContainer = document.getElementById('chatbotMessages');
        messagesContainer.innerHTML = `
            <div style="text-align: center; padding: 40px 20px;">
                <div style="font-size: 64px; margin-bottom: 20px;">🔒</div>
                <h3 style="color: #2c3e50; margin-bottom: 10px;">Vui lòng đăng nhập</h3>
                <p style="color: #7f8c8d; margin-bottom: 20px;">Bạn cần đăng nhập để sử dụng chatbot AI</p>
                <a href="login.php" style="display: inline-block; padding: 12px 24px; background: linear-gradient(135deg, #FF6B6B 0%, #FF8E53 100%); color: white; text-decoration: none; border-radius: 24px; font-weight: 600;">
                    Đăng nhập ngay
                </a>
            </div>
        `;
    }

    async clearHistory() {
        if (!confirm('Bạn có chắc muốn xóa toàn bộ lịch sử chat?')) {
            return;
        }

        try {
            const response = await fetch(this.apiEndpoint, {
                method: 'DELETE'
            });
            const data = await response.json();
            
            if (data.success) {
                document.getElementById('chatbotMessages').innerHTML = '';
                this.conversationHistory = [];
                this.addWelcomeMessage();
            } else {
                alert('Không thể xóa lịch sử chat');
            }
        } catch (error) {
            console.error('Lỗi xóa lịch sử:', error);
            alert('Đã xảy ra lỗi khi xóa lịch sử');
        }
    }

    toggleChat() {
        this.isOpen = !this.isOpen;
        const window = document.getElementById('chatbotWindow');
        const toggle = document.getElementById('chatbotToggle');
        
        if (this.isOpen) {
            window.classList.add('active');
            toggle.classList.add('active');
            toggle.innerHTML = '✕';
            setTimeout(() => {
                document.getElementById('chatbotInput').focus();
            }, 300);
        } else {
            window.classList.remove('active');
            toggle.classList.remove('active');
            toggle.innerHTML = '💬';
        }
    }

    addWelcomeMessage() {
        const welcomeText = `Xin chào! 👋 Tôi là trợ lý AI về văn hóa Khmer Nam Bộ. 

Tôi có thể giúp bạn:
• Tìm hiểu về văn hóa, lịch sử Khmer
• Giới thiệu các lễ hội và chùa chiền
• Học tiếng Khmer
• Khám phá truyện dân gian

Bạn muốn hỏi gì?`;
        
        this.addMessage(welcomeText, 'bot');
        this.addQuickActions();
    }

    addQuickActions() {
        const messagesContainer = document.getElementById('chatbotMessages');
        const quickActionsDiv = document.createElement('div');
        quickActionsDiv.className = 'quick-actions';
        quickActionsDiv.innerHTML = `
            <button class="quick-action-btn" data-question="Giới thiệu về văn hóa Khmer Nam Bộ">🏛️ Văn hóa Khmer</button>
            <button class="quick-action-btn" data-question="Các lễ hội Khmer nổi tiếng">🎉 Lễ hội</button>
            <button class="quick-action-btn" data-question="Dạy tôi tiếng Khmer cơ bản">📚 Học tiếng Khmer</button>
            <button class="quick-action-btn" data-question="Kể một truyện dân gian Khmer">📖 Truyện dân gian</button>
        `;
        
        messagesContainer.appendChild(quickActionsDiv);
        
        // Add click handlers
        quickActionsDiv.querySelectorAll('.quick-action-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const question = e.target.dataset.question;
                document.getElementById('chatbotInput').value = question;
                document.getElementById('chatbotForm').dispatchEvent(new Event('submit'));
                quickActionsDiv.remove();
            });
        });
    }

    addMessage(text, sender = 'bot', showTime = true) {
        const messagesContainer = document.getElementById('chatbotMessages');
        const messageDiv = document.createElement('div');
        messageDiv.className = `chatbot-message ${sender}`;
        
        const time = new Date().toLocaleTimeString('vi-VN', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });

        // Avatar người dùng hoặc bot
        let avatarHtml;
        if (sender === 'bot') {
            avatarHtml = '<div class="message-avatar bot">🤖</div>';
        } else {
            if (this.userAvatar) {
                avatarHtml = `<div class="message-avatar user"><img src="${this.userAvatar}" alt="Avatar" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;"></div>`;
            } else {
                avatarHtml = '<div class="message-avatar user">👤</div>';
            }
        }
        
        messageDiv.innerHTML = `
            ${avatarHtml}
            <div>
                <div class="message-content">${this.formatMessage(text)}</div>
                ${showTime ? `<div class="message-time">${time}</div>` : ''}
            </div>
        `;
        
        messagesContainer.appendChild(messageDiv);
        this.scrollToBottom();
    }

    formatMessage(text) {
        // Chuyển đổi line breaks thành <br>
        return text.replace(/\n/g, '<br>');
    }

    showTypingIndicator() {
        const messagesContainer = document.getElementById('chatbotMessages');
        const typingDiv = document.createElement('div');
        typingDiv.className = 'chatbot-message bot';
        typingDiv.id = 'typingIndicator';
        typingDiv.innerHTML = `
            <div class="message-avatar bot">🤖</div>
            <div class="chatbot-typing">
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
            </div>
        `;
        messagesContainer.appendChild(typingDiv);
        this.scrollToBottom();
    }

    removeTypingIndicator() {
        const indicator = document.getElementById('typingIndicator');
        if (indicator) {
            indicator.remove();
        }
    }

    scrollToBottom() {
        const messagesContainer = document.getElementById('chatbotMessages');
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    async handleSubmit(e) {
        e.preventDefault();
        
        if (!this.isLoggedIn) {
            alert('Vui lòng đăng nhập để sử dụng chatbot');
            return;
        }

        const input = document.getElementById('chatbotInput');
        const sendBtn = document.getElementById('chatbotSend');
        const message = input.value.trim();
        
        if (!message) return;

        // Hiển thị tin nhắn người dùng
        this.addMessage(message, 'user');
        input.value = '';
        sendBtn.disabled = true;

        // Hiển thị typing indicator
        this.showTypingIndicator();

        try {
            const response = await fetch(this.apiEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    message: message
                })
            });

            const data = await response.json();

            this.removeTypingIndicator();

            if (data.requireLogin) {
                // Session hết hạn
                this.isLoggedIn = false;
                this.showLoginRequired();
                return;
            }

            if (data.success && data.reply) {
                this.addMessage(data.reply, 'bot');
            } else {
                this.addMessage('Xin lỗi, đã có lỗi xảy ra. Vui lòng thử lại sau.', 'bot');
            }
        } catch (error) {
            console.error('Lỗi chatbot:', error);
            this.removeTypingIndicator();
            this.addMessage('Không thể kết nối đến server. Vui lòng kiểm tra kết nối và thử lại.', 'bot');
        } finally {
            sendBtn.disabled = false;
            input.focus();
        }
    }
}

// Khởi tạo chatbot khi trang load xong
document.addEventListener('DOMContentLoaded', () => {
    new ChatbotWidget();
});
