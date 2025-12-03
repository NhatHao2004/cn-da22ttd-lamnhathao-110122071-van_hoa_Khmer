/**
 * Main JavaScript - Frontend User
 * Văn Hóa Khmer Nam Bộ
 */

// ===== DOM Ready =====
document.addEventListener('DOMContentLoaded', function() {
    initMobileMenu();
    initSearchModal();
    initUserMenu();
    initBackToTop();
    initScrollAnimations();
});

// ===== Mobile Menu =====
function initMobileMenu() {
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const mobileMenu = document.getElementById('mobileMenu');
    const mobileOverlay = document.getElementById('mobileOverlay');
    const closeMobileMenu = document.getElementById('closeMobileMenu');
    
    if (mobileMenuToggle && mobileMenu) {
        mobileMenuToggle.addEventListener('click', function() {
            mobileMenu.style.display = 'block';
            mobileOverlay.style.display = 'block';
            setTimeout(() => {
                mobileMenu.style.right = '0';
            }, 10);
            document.body.style.overflow = 'hidden';
        });
        
        const closeMenu = function() {
            mobileMenu.style.right = '-100%';
            mobileOverlay.style.display = 'none';
            setTimeout(() => {
                mobileMenu.style.display = 'none';
            }, 400);
            document.body.style.overflow = '';
        };
        
        if (closeMobileMenu) {
            closeMobileMenu.addEventListener('click', closeMenu);
        }
        
        // Close on overlay click
        if (mobileOverlay) {
            mobileOverlay.addEventListener('click', closeMenu);
        }
        
        // Close on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && mobileMenu.style.right === '0px') {
                closeMenu();
            }
        });
    }
}

// ===== Search Modal =====
function initSearchModal() {
    const searchBtn = document.getElementById('searchBtn');
    const searchModal = document.getElementById('searchModal');
    const closeSearch = document.getElementById('closeSearch');
    const searchInput = document.getElementById('searchInput');
    
    if (searchBtn && searchModal) {
        searchBtn.addEventListener('click', function(e) {
            e.preventDefault();
            searchModal.style.display = 'flex';
            setTimeout(() => {
                if (searchInput) searchInput.focus();
            }, 100);
        });
        
        const closeSearchModal = function() {
            searchModal.style.display = 'none';
            if (searchInput) searchInput.value = '';
            const searchResults = document.getElementById('searchResults');
            if (searchResults) {
                searchResults.innerHTML = '';
                searchResults.style.display = 'none';
            }
        };
        
        if (closeSearch) {
            closeSearch.addEventListener('click', closeSearchModal);
        }
        
        // Close on outside click
        searchModal.addEventListener('click', function(e) {
            if (e.target === searchModal) {
                closeSearchModal();
            }
        });
        
        // Close on ESC key & Keyboard shortcut Ctrl+K
        document.addEventListener('keydown', function(e) {
            // Ctrl+K to open search
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                if (searchModal.style.display === 'flex') {
                    closeSearchModal();
                } else {
                    searchModal.style.display = 'flex';
                    setTimeout(() => {
                        if (searchInput) searchInput.focus();
                    }, 100);
                }
            }
            // ESC to close
            if (e.key === 'Escape' && searchModal.style.display === 'flex') {
                closeSearchModal();
            }
        });
        
        // Search functionality
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const query = this.value.trim();
                
                if (query.length >= 2) {
                    searchTimeout = setTimeout(() => {
                        performSearch(query);
                    }, 300);
                } else {
                    document.getElementById('searchResults').innerHTML = '';
                }
            });
        }
    }
}

// ===== Perform Search =====
function performSearch(query) {
    const resultsContainer = document.getElementById('searchResults');
    
    // Show loading
    resultsContainer.innerHTML = '<div class="search-loading">Đang tìm kiếm...</div>';
    
    // AJAX search request
    fetch(`search.php?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            if (data.results && data.results.length > 0) {
                let html = '<div class="search-results-list">';
                data.results.forEach(result => {
                    html += `
                        <a href="${result.url}" class="search-result-item">
                            <div class="search-result-icon">
                                <i class="${result.icon}"></i>
                            </div>
                            <div class="search-result-content">
                                <div class="search-result-title">${result.title}</div>
                                <div class="search-result-type">${result.type}</div>
                            </div>
                        </a>
                    `;
                });
                html += '</div>';
                resultsContainer.innerHTML = html;
            } else {
                resultsContainer.innerHTML = '<div class="search-no-results">Không tìm thấy kết quả</div>';
            }
        })
        .catch(error => {
            console.error('Search error:', error);
            resultsContainer.innerHTML = '<div class="search-error">Có lỗi xảy ra khi tìm kiếm</div>';
        });
}

// ===== User Menu =====
function initUserMenu() {
    const userMenuBtn = document.getElementById('userMenuBtn');
    const userDropdown = document.getElementById('userDropdown');
    
    if (userMenuBtn && userDropdown) {
        userMenuBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdown.classList.toggle('active');
        });
        
        // Close on outside click
        document.addEventListener('click', function() {
            userDropdown.classList.remove('active');
        });
        
        userDropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
}

// ===== Back to Top =====
function initBackToTop() {
    const backToTop = document.getElementById('backToTop');
    
    if (backToTop) {
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTop.classList.add('show');
            } else {
                backToTop.classList.remove('show');
            }
        });
        
        backToTop.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
}

// ===== Scroll Animations =====
function initScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    // Observe elements
    const animateElements = document.querySelectorAll('.feature-card, .article-card, .temple-card, .festival-card, .lesson-card');
    animateElements.forEach(el => {
        observer.observe(el);
    });
}

// ===== Enhanced Navbar Scroll Effect =====
let lastScrollTop = 0;
window.addEventListener('scroll', function() {
    const navbar = document.querySelector('.navbar');
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    
    if (scrollTop > 100) {
        navbar.style.background = 'rgba(255, 255, 255, 0.95)';
        navbar.style.boxShadow = '0 8px 32px rgba(102, 126, 234, 0.15)';
        navbar.style.transform = 'translateY(0)';
    } else {
        navbar.style.background = 'rgba(255, 255, 255, 0.85)';
        navbar.style.boxShadow = '0 4px 16px rgba(0, 0, 0, 0.08)';
    }
    
    // Hide navbar on scroll down, show on scroll up
    if (scrollTop > lastScrollTop && scrollTop > 200) {
        navbar.style.transform = 'translateY(-100%)';
    } else {
        navbar.style.transform = 'translateY(0)';
    }
    
    lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
}, false);

// ===== Smooth Scroll for Anchor Links =====
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        const href = this.getAttribute('href');
        if (href !== '#' && href !== '') {
            e.preventDefault();
            const target = document.querySelector(href);
            if (target) {
                const offsetTop = target.offsetTop - 80;
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        }
    });
});

// ===== Image Lazy Loading Fallback =====
if ('loading' in HTMLImageElement.prototype) {
    // Browser supports lazy loading
    const images = document.querySelectorAll('img[loading="lazy"]');
    images.forEach(img => {
        img.src = img.dataset.src || img.src;
    });
} else {
    // Fallback for browsers that don't support lazy loading
    const script = document.createElement('script');
    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.3.2/lazysizes.min.js';
    document.body.appendChild(script);
}

// ===== Toast Notification =====
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <div class="toast-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('show');
    }, 100);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, 3000);
}

// ===== Form Validation Helper =====
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('error');
            isValid = false;
        } else {
            input.classList.remove('error');
        }
    });
    
    return isValid;
}

// ===== AJAX Helper =====
function ajaxRequest(url, method = 'GET', data = null) {
    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        xhr.open(method, url, true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    resolve(response);
                } catch (e) {
                    resolve(xhr.responseText);
                }
            } else {
                reject(new Error(`Request failed with status ${xhr.status}`));
            }
        };
        
        xhr.onerror = function() {
            reject(new Error('Network error'));
        };
        
        if (data) {
            xhr.send(JSON.stringify(data));
        } else {
            xhr.send();
        }
    });
}

// ===== Local Storage Helper =====
const storage = {
    set: function(key, value) {
        try {
            localStorage.setItem(key, JSON.stringify(value));
            return true;
        } catch (e) {
            console.error('Storage error:', e);
            return false;
        }
    },
    
    get: function(key) {
        try {
            const item = localStorage.getItem(key);
            return item ? JSON.parse(item) : null;
        } catch (e) {
            console.error('Storage error:', e);
            return null;
        }
    },
    
    remove: function(key) {
        try {
            localStorage.removeItem(key);
            return true;
        } catch (e) {
            console.error('Storage error:', e);
            return false;
        }
    }
};

// ===== Export for use in other scripts =====
window.KhmerApp = {
    showToast,
    validateForm,
    ajaxRequest,
    storage
};
