/**
 * Authentication JavaScript
 */

// Real-time form validation
document.addEventListener('DOMContentLoaded', function() {
    // Login form
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const email = this.querySelector('[name="email"]');
            const password = this.querySelector('[name="password"]');
            
            if (!validateEmail(email.value)) {
                e.preventDefault();
                showFieldError(email, 'Email không hợp lệ');
                return;
            }
            
            if (password.value.length < 6) {
                e.preventDefault();
                showFieldError(password, 'Mật khẩu phải có ít nhất 6 ký tự');
                return;
            }
        });
    }
    
    // Register form
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        const password = registerForm.querySelector('[name="password"]');
        const confirmPassword = registerForm.querySelector('[name="confirm_password"]');
        
        // Real-time password match check
        if (confirmPassword) {
            confirmPassword.addEventListener('input', function() {
                if (this.value !== password.value) {
                    this.classList.add('error');
                } else {
                    this.classList.remove('error');
                }
            });
        }
        
        registerForm.addEventListener('submit', function(e) {
            const hoTen = this.querySelector('[name="ho_ten"]');
            const email = this.querySelector('[name="email"]');
            
            if (hoTen.value.trim().length < 2) {
                e.preventDefault();
                showFieldError(hoTen, 'Họ tên phải có ít nhất 2 ký tự');
                return;
            }
            
            if (!validateEmail(email.value)) {
                e.preventDefault();
                showFieldError(email, 'Email không hợp lệ');
                return;
            }
            
            if (password.value.length < 6) {
                e.preventDefault();
                showFieldError(password, 'Mật khẩu phải có ít nhất 6 ký tự');
                return;
            }
            
            if (password.value !== confirmPassword.value) {
                e.preventDefault();
                showFieldError(confirmPassword, 'Mật khẩu xác nhận không khớp');
                return;
            }
        });
    }
});

function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function showFieldError(field, message) {
    field.classList.add('error');
    
    let errorEl = field.parentElement.querySelector('.form-error');
    if (!errorEl) {
        errorEl = document.createElement('div');
        errorEl.className = 'form-error';
        field.parentElement.appendChild(errorEl);
    }
    errorEl.textContent = message;
    
    field.focus();
    
    // Remove error on input
    field.addEventListener('input', function() {
        this.classList.remove('error');
        if (errorEl) errorEl.textContent = '';
    }, { once: true });
}

// Password strength indicator
function checkPasswordStrength(password) {
    let strength = 0;
    
    if (password.length >= 6) strength++;
    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
    if (/\d/.test(password)) strength++;
    if (/[^a-zA-Z0-9]/.test(password)) strength++;
    
    return strength;
}
