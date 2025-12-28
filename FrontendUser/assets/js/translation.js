/**
 * Global Translation System for All Pages
 * Auto-translates page content based on user's language preference
 */

console.log('🚀 Translation.js loading...');

(function() {
    'use strict';
    
    console.log('✅ Translation.js IIFE started');
    
    // Translation cache
    const translationCache = new Map();
    let isTranslating = false;
    
    /**
     * Get current language from localStorage and session
     */
    function getCurrentLanguage() {
        return localStorage.getItem('language') || 'vi';
    }
    
    /**
     * Set current language
     */
    function setCurrentLanguage(lang) {
        localStorage.setItem('language', lang);
    }
    
    /**
     * Fast batch translation API function
     */
    async function translateTextBatch(texts, from, to) {
        try {
            const delimiter = '\n###SPLIT###\n';
            const combinedText = texts.join(delimiter);
            
            // Check cache
            const cacheKey = `${from}-${to}-${combinedText}`;
            if (translationCache.has(cacheKey)) {
                return translationCache.get(cacheKey);
            }
            
            const response = await fetch('translate.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    text: combinedText,
                    from: from,
                    to: to
                })
            });
            
            const result = await response.json();
            
            if (result.error) {
                console.error('Translation error:', result.message);
                return texts;
            }
            
            const translatedTexts = result.translatedText.split(delimiter);
            translationCache.set(cacheKey, translatedTexts);
            
            return translatedTexts;
        } catch (error) {
            console.error('Translation fetch error:', error);
            return texts;
        }
    }
    
    /**
     * Apply translation to entire page
     */
    async function translatePage(targetLang) {
        if (targetLang === 'vi' || isTranslating) {
            return;
        }
        
        isTranslating = true;
        
        // Show mini loading indicator
        showMiniLoader();
        
        try {
            // Select elements to translate (exclude scripts, styles, etc.)
            const elementsToTranslate = document.querySelectorAll(
                'h1, h2, h3, h4, h5, h6, p, span:not(.icon):not(.fa):not(.fas):not(.far):not(.fab), ' +
                'a:not(.logo), button:not([data-no-translate]), label, ' +
                '.card-title, .card-text, .section-title, .description, ' +
                '.info-text, .hero-title, .hero-subtitle, .feature-title, ' +
                '.btn-text, .menu-item, .nav-link, .breadcrumb-item'
            );
            
            const textsToTranslate = [];
            const elementsMap = [];
            
            elementsToTranslate.forEach(element => {
                // Skip if has child elements (except icons)
                if (element.children.length > 0) {
                    const hasNonIconChildren = Array.from(element.children).some(child => 
                        !child.classList.contains('fas') && 
                        !child.classList.contains('far') && 
                        !child.classList.contains('fab') &&
                        !child.classList.contains('icon')
                    );
                    if (hasNonIconChildren) return;
                }
                
                // Skip if marked as no-translate
                if (element.hasAttribute('data-no-translate')) return;
                
                // Skip if parent has no-translate
                if (element.closest('[data-no-translate]')) return;
                
                let text = element.textContent.trim();
                
                // Clean text - remove icons
                text = text.replace(/[\uF000-\uF8FF]/g, '').trim();
                
                // Skip empty, very short, or very long texts
                if (!text || text.length < 2 || text.length > 500) return;
                
                // Skip if only numbers or special chars
                if (/^[\d\s\-_.,;:!?()]+$/.test(text)) return;
                
                // Store original text
                if (!element.hasAttribute('data-original-text')) {
                    element.setAttribute('data-original-text', text);
                }
                
                textsToTranslate.push(text);
                elementsMap.push(element);
            });
            
            // Batch translate in groups of 15
            const batchSize = 15;
            const allTranslatedTexts = [];
            
            for (let i = 0; i < textsToTranslate.length; i += batchSize) {
                const batch = textsToTranslate.slice(i, i + batchSize);
                const translatedBatch = await translateTextBatch(batch, 'vi', targetLang);
                allTranslatedTexts.push(...translatedBatch);
                
                // Update progress
                updateMiniLoader(Math.round(((i + batchSize) / textsToTranslate.length) * 100));
            }
            
            // Apply translations
            allTranslatedTexts.forEach((translatedText, index) => {
                const element = elementsMap[index];
                if (element && translatedText) {
                    // Preserve icons
                    const icons = Array.from(element.querySelectorAll('.fas, .far, .fab, .icon'));
                    element.textContent = translatedText;
                    
                    // Re-add icons at the beginning
                    icons.forEach((icon, iconIndex) => {
                        if (iconIndex === 0) {
                            element.insertBefore(icon.cloneNode(true), element.firstChild);
                            element.insertBefore(document.createTextNode(' '), icon.nextSibling);
                        }
                    });
                }
            });
            
            // Show language indicator
            showLanguageIndicator(targetLang);
            
        } catch (error) {
            console.error('Page translation failed:', error);
        } finally {
            hideMiniLoader();
            isTranslating = false;
        }
    }
    
    /**
     * Show mini loading indicator
     */
    function showMiniLoader() {
        let loader = document.getElementById('global-translate-loader');
        if (!loader) {
            loader = document.createElement('div');
            loader.id = 'global-translate-loader';
            loader.style.cssText = `
                position: fixed;
                top: 80px;
                right: 20px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 12px 20px;
                border-radius: 30px;
                box-shadow: 0 4px 16px rgba(102, 126, 234, 0.3);
                z-index: 9998;
                display: flex;
                align-items: center;
                gap: 10px;
                font-size: 13px;
                font-weight: 600;
            `;
            loader.innerHTML = `
                <i class="fas fa-language" style="animation: spin 1s linear infinite;"></i>
                <span id="loader-text">កំពុងបកប្រែ...</span>
                <style>
                    @keyframes spin {
                        from { transform: rotate(0deg); }
                        to { transform: rotate(360deg); }
                    }
                </style>
            `;
            document.body.appendChild(loader);
        }
        loader.style.display = 'flex';
    }
    
    /**
     * Update mini loader progress
     */
    function updateMiniLoader(percent) {
        const loaderText = document.getElementById('loader-text');
        if (loaderText) {
            loaderText.textContent = `${percent}%`;
        }
    }
    
    /**
     * Hide mini loader
     */
    function hideMiniLoader() {
        const loader = document.getElementById('global-translate-loader');
        if (loader) {
            setTimeout(() => {
                loader.style.display = 'none';
            }, 500);
        }
    }
    
    /**
     * Show language indicator
     */
    function showLanguageIndicator(lang) {
        let indicator = document.getElementById('global-lang-indicator');
        if (!indicator) {
            indicator = document.createElement('div');
            indicator.id = 'global-lang-indicator';
            indicator.style.cssText = `
                position: fixed;
                bottom: 20px;
                right: 20px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 10px 18px;
                border-radius: 25px;
                box-shadow: 0 4px 16px rgba(102, 126, 234, 0.3);
                z-index: 9997;
                display: flex;
                align-items: center;
                gap: 8px;
                font-size: 13px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s;
            `;
            
            indicator.addEventListener('mouseenter', () => {
                indicator.style.transform = 'scale(1.05)';
            });
            
            indicator.addEventListener('mouseleave', () => {
                indicator.style.transform = 'scale(1)';
            });
            
            indicator.addEventListener('click', () => {
                window.location.href = 'settings.php';
            });
            
            document.body.appendChild(indicator);
        }
        
        const flag = lang === 'km' ? '🇰🇭' : '🇻🇳';
        const text = lang === 'km' ? 'ភាសាខ្មែរ' : 'Tiếng Việt';
        
        indicator.innerHTML = `
            <span style="font-size: 16px;">${flag}</span>
            <span>${text}</span>
        `;
        indicator.style.display = 'flex';
    }
    
    /**
     * Initialize translation system
     */
    function init() {
        const currentLang = getCurrentLanguage();
        
        // Show indicator if not Vietnamese
        if (currentLang !== 'vi') {
            showLanguageIndicator(currentLang);
        }
        
        // Auto-translate if Khmer is selected
        if (currentLang === 'km') {
            // Wait for page to fully load
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => {
                    setTimeout(() => translatePage('km'), 300);
                });
            } else {
                setTimeout(() => translatePage('km'), 300);
            }
        }
    }
    
    // Expose global functions
    window.GlobalTranslation = {
        translatePage: translatePage,
        getCurrentLanguage: getCurrentLanguage,
        setCurrentLanguage: setCurrentLanguage,
        showLanguageIndicator: showLanguageIndicator
    };
    
    console.log('✅ GlobalTranslation exposed to window:', window.GlobalTranslation);
    
    // Auto-initialize
    init();
    
    console.log('✅ Translation.js initialized successfully!');
    
})();

console.log('🎉 Translation.js loaded completely!');
