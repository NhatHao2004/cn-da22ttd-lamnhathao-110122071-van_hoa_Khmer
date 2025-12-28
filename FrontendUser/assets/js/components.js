/**
 * Components JavaScript
 */

// Lazy loading images
document.addEventListener('DOMContentLoaded', function() {
    const lazyImages = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                observer.unobserve(img);
            }
        });
    });
    
    lazyImages.forEach(img => imageObserver.observe(img));
});

// Infinite scroll
class InfiniteScroll {
    constructor(container, loadMore) {
        this.container = container;
        this.loadMore = loadMore;
        this.loading = false;
        this.page = 1;
        this.hasMore = true;
        
        this.init();
    }
    
    init() {
        window.addEventListener('scroll', () => {
            if (this.loading || !this.hasMore) return;
            
            const scrollTop = window.scrollY;
            const windowHeight = window.innerHeight;
            const docHeight = document.documentElement.scrollHeight;
            
            if (scrollTop + windowHeight >= docHeight - 200) {
                this.load();
            }
        });
    }
    
    async load() {
        this.loading = true;
        this.page++;
        
        try {
            const result = await this.loadMore(this.page);
            if (!result || result.length === 0) {
                this.hasMore = false;
            }
        } catch (error) {
            console.error('Load more error:', error);
        }
        
        this.loading = false;
    }
}

// Tabs component
class Tabs {
    constructor(container) {
        this.container = container;
        this.tabs = container.querySelectorAll('[data-tab]');
        this.panels = container.querySelectorAll('[data-panel]');
        
        this.init();
    }
    
    init() {
        this.tabs.forEach(tab => {
            tab.addEventListener('click', () => this.activate(tab.dataset.tab));
        });
    }
    
    activate(tabId) {
        this.tabs.forEach(tab => {
            tab.classList.toggle('active', tab.dataset.tab === tabId);
        });
        
        this.panels.forEach(panel => {
            panel.classList.toggle('active', panel.dataset.panel === tabId);
            panel.style.display = panel.dataset.panel === tabId ? 'block' : 'none';
        });
    }
}

// Accordion component
class Accordion {
    constructor(container) {
        this.container = container;
        this.items = container.querySelectorAll('.accordion-item');
        
        this.init();
    }
    
    init() {
        this.items.forEach(item => {
            const header = item.querySelector('.accordion-header');
            header.addEventListener('click', () => this.toggle(item));
        });
    }
    
    toggle(item) {
        const isOpen = item.classList.contains('open');
        
        // Close all
        this.items.forEach(i => i.classList.remove('open'));
        
        // Open clicked if was closed
        if (!isOpen) {
            item.classList.add('open');
        }
    }
}

// Image gallery/lightbox
class Lightbox {
    constructor() {
        this.overlay = null;
        this.init();
    }
    
    init() {
        document.querySelectorAll('[data-lightbox]').forEach(img => {
            img.style.cursor = 'pointer';
            img.addEventListener('click', () => this.open(img.src, img.alt));
        });
    }
    
    open(src, alt = '') {
        this.overlay = document.createElement('div');
        this.overlay.className = 'lightbox-overlay';
        this.overlay.innerHTML = `
            <div class="lightbox-content">
                <button class="lightbox-close">&times;</button>
                <img src="${src}" alt="${alt}">
            </div>
        `;
        
        this.overlay.addEventListener('click', (e) => {
            if (e.target === this.overlay || e.target.classList.contains('lightbox-close')) {
                this.close();
            }
        });
        
        document.body.appendChild(this.overlay);
        document.body.style.overflow = 'hidden';
        
        setTimeout(() => this.overlay.classList.add('active'), 10);
    }
    
    close() {
        this.overlay.classList.remove('active');
        setTimeout(() => {
            this.overlay.remove();
            document.body.style.overflow = '';
        }, 300);
    }
}

// Initialize components
document.addEventListener('DOMContentLoaded', function() {
    // Init tabs
    document.querySelectorAll('.tabs-container').forEach(el => new Tabs(el));
    
    // Init accordions
    document.querySelectorAll('.accordion').forEach(el => new Accordion(el));
    
    // Init lightbox
    new Lightbox();
});


// Auto-hide alerts/flash messages
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    
    alerts.forEach(alert => {
        // Auto hide after 5 seconds
        setTimeout(() => {
            alert.style.animation = 'fadeOut 0.4s ease-out forwards';
            setTimeout(() => {
                alert.remove();
            }, 400);
        }, 5000);
        
        // Click to dismiss
        alert.style.cursor = 'pointer';
        alert.addEventListener('click', () => {
            alert.style.animation = 'fadeOut 0.4s ease-out forwards';
            setTimeout(() => {
                alert.remove();
            }, 400);
        });
    });
});
