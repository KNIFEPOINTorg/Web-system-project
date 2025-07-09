/**
 * Accessibility Helper for LFLshop
 * Provides WCAG 2.1 AA compliance features and utilities
 */

class AccessibilityHelper {
    constructor() {
        this.init();
    }

    init() {
        this.setupKeyboardNavigation();
        this.setupAriaLabels();
        this.setupFocusManagement();
        this.setupScreenReaderAnnouncements();
        this.setupColorContrastToggle();
        this.setupFontSizeControls();
    }

    /**
     * Setup keyboard navigation
     */
    setupKeyboardNavigation() {
        // Escape key to close modals
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeModals();
                this.closeMobileMenu();
            }
        });

        // Tab trapping in modals
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Tab') {
                const modal = document.querySelector('.modal:not([hidden])');
                if (modal) {
                    this.trapFocus(e, modal);
                }
            }
        });

        // Arrow key navigation for menus
        this.setupArrowKeyNavigation();
    }

    /**
     * Setup arrow key navigation for dropdown menus
     */
    setupArrowKeyNavigation() {
        const dropdowns = document.querySelectorAll('[role="menu"]');
        
        dropdowns.forEach(dropdown => {
            dropdown.addEventListener('keydown', (e) => {
                const items = dropdown.querySelectorAll('[role="menuitem"]');
                const currentIndex = Array.from(items).indexOf(document.activeElement);
                
                switch (e.key) {
                    case 'ArrowDown':
                        e.preventDefault();
                        const nextIndex = (currentIndex + 1) % items.length;
                        items[nextIndex].focus();
                        break;
                    case 'ArrowUp':
                        e.preventDefault();
                        const prevIndex = currentIndex > 0 ? currentIndex - 1 : items.length - 1;
                        items[prevIndex].focus();
                        break;
                    case 'Home':
                        e.preventDefault();
                        items[0].focus();
                        break;
                    case 'End':
                        e.preventDefault();
                        items[items.length - 1].focus();
                        break;
                }
            });
        });
    }

    /**
     * Trap focus within a container
     */
    trapFocus(event, container) {
        const focusableElements = container.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        
        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];

        if (event.shiftKey && document.activeElement === firstElement) {
            event.preventDefault();
            lastElement.focus();
        } else if (!event.shiftKey && document.activeElement === lastElement) {
            event.preventDefault();
            firstElement.focus();
        }
    }

    /**
     * Setup ARIA labels and descriptions
     */
    setupAriaLabels() {
        // Add ARIA labels to buttons without text
        const iconButtons = document.querySelectorAll('button:not([aria-label]):empty, button:not([aria-label]) > i:only-child');
        iconButtons.forEach(button => {
            const icon = button.querySelector('i');
            if (icon) {
                const className = icon.className;
                if (className.includes('search')) {
                    button.setAttribute('aria-label', 'Search');
                } else if (className.includes('cart')) {
                    button.setAttribute('aria-label', 'Shopping cart');
                } else if (className.includes('menu')) {
                    button.setAttribute('aria-label', 'Menu');
                } else if (className.includes('close')) {
                    button.setAttribute('aria-label', 'Close');
                }
            }
        });

        // Add ARIA labels to form inputs without labels
        const inputs = document.querySelectorAll('input:not([aria-label]):not([aria-labelledby])');
        inputs.forEach(input => {
            const placeholder = input.getAttribute('placeholder');
            if (placeholder && !input.previousElementSibling?.tagName === 'LABEL') {
                input.setAttribute('aria-label', placeholder);
            }
        });

        // Add ARIA expanded to dropdown toggles
        const dropdownToggles = document.querySelectorAll('[data-toggle="dropdown"]');
        dropdownToggles.forEach(toggle => {
            toggle.setAttribute('aria-expanded', 'false');
            toggle.setAttribute('aria-haspopup', 'true');
        });
    }

    /**
     * Setup focus management
     */
    setupFocusManagement() {
        // Store focus before opening modals
        let lastFocusedElement = null;

        // Modal focus management
        const modalTriggers = document.querySelectorAll('[data-modal-target]');
        modalTriggers.forEach(trigger => {
            trigger.addEventListener('click', () => {
                lastFocusedElement = document.activeElement;
                const modalId = trigger.getAttribute('data-modal-target');
                const modal = document.getElementById(modalId);
                if (modal) {
                    this.openModal(modal);
                }
            });
        });

        // Restore focus when modal closes
        document.addEventListener('modalClosed', () => {
            if (lastFocusedElement) {
                lastFocusedElement.focus();
                lastFocusedElement = null;
            }
        });
    }

    /**
     * Setup screen reader announcements
     */
    setupScreenReaderAnnouncements() {
        // Create live region for announcements
        const liveRegion = document.createElement('div');
        liveRegion.setAttribute('aria-live', 'polite');
        liveRegion.setAttribute('aria-atomic', 'true');
        liveRegion.className = 'sr-only';
        liveRegion.id = 'live-region';
        document.body.appendChild(liveRegion);

        // Announce form validation errors
        document.addEventListener('invalid', (e) => {
            const field = e.target;
            const message = field.validationMessage;
            this.announce(`Error in ${field.name || 'form field'}: ${message}`);
        }, true);

        // Announce successful form submissions
        document.addEventListener('formSubmitted', (e) => {
            this.announce('Form submitted successfully');
        });
    }

    /**
     * Announce message to screen readers
     */
    announce(message) {
        const liveRegion = document.getElementById('live-region');
        if (liveRegion) {
            liveRegion.textContent = message;
            // Clear after announcement
            setTimeout(() => {
                liveRegion.textContent = '';
            }, 1000);
        }
    }

    /**
     * Setup color contrast toggle
     */
    setupColorContrastToggle() {
        const contrastToggle = document.createElement('button');
        contrastToggle.textContent = 'High Contrast';
        contrastToggle.className = 'contrast-toggle';
        contrastToggle.setAttribute('aria-label', 'Toggle high contrast mode');
        
        contrastToggle.addEventListener('click', () => {
            document.body.classList.toggle('high-contrast');
            const isHighContrast = document.body.classList.contains('high-contrast');
            contrastToggle.textContent = isHighContrast ? 'Normal Contrast' : 'High Contrast';
            localStorage.setItem('highContrast', isHighContrast);
        });

        // Restore contrast preference
        if (localStorage.getItem('highContrast') === 'true') {
            document.body.classList.add('high-contrast');
            contrastToggle.textContent = 'Normal Contrast';
        }

        // Add to accessibility toolbar
        this.addToAccessibilityToolbar(contrastToggle);
    }

    /**
     * Setup font size controls
     */
    setupFontSizeControls() {
        const fontSizeControls = document.createElement('div');
        fontSizeControls.className = 'font-size-controls';
        fontSizeControls.innerHTML = `
            <button aria-label="Decrease font size">A-</button>
            <button aria-label="Reset font size">A</button>
            <button aria-label="Increase font size">A+</button>
        `;

        const [decrease, reset, increase] = fontSizeControls.querySelectorAll('button');
        
        decrease.addEventListener('click', () => this.adjustFontSize(-1));
        reset.addEventListener('click', () => this.adjustFontSize(0));
        increase.addEventListener('click', () => this.adjustFontSize(1));

        // Restore font size preference
        const savedFontSize = localStorage.getItem('fontSize');
        if (savedFontSize) {
            document.documentElement.style.fontSize = savedFontSize;
        }

        this.addToAccessibilityToolbar(fontSizeControls);
    }

    /**
     * Adjust font size
     */
    adjustFontSize(change) {
        const currentSize = parseFloat(getComputedStyle(document.documentElement).fontSize);
        let newSize;

        if (change === 0) {
            newSize = 16; // Reset to default
        } else {
            newSize = Math.max(12, Math.min(24, currentSize + (change * 2)));
        }

        document.documentElement.style.fontSize = newSize + 'px';
        localStorage.setItem('fontSize', newSize + 'px');
        
        this.announce(`Font size ${change === 0 ? 'reset' : change > 0 ? 'increased' : 'decreased'}`);
    }

    /**
     * Add element to accessibility toolbar
     */
    addToAccessibilityToolbar(element) {
        let toolbar = document.getElementById('accessibility-toolbar');
        
        if (!toolbar) {
            toolbar = document.createElement('div');
            toolbar.id = 'accessibility-toolbar';
            toolbar.className = 'accessibility-toolbar';
            toolbar.setAttribute('role', 'toolbar');
            toolbar.setAttribute('aria-label', 'Accessibility options');
            
            // Add toolbar styles
            toolbar.style.cssText = `
                position: fixed;
                top: 10px;
                right: 10px;
                background: white;
                border: 1px solid #ccc;
                border-radius: 4px;
                padding: 8px;
                display: flex;
                gap: 8px;
                z-index: 1000;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            `;
            
            document.body.appendChild(toolbar);
        }
        
        toolbar.appendChild(element);
    }

    /**
     * Close all modals
     */
    closeModals() {
        const modals = document.querySelectorAll('.modal:not([hidden])');
        modals.forEach(modal => {
            modal.setAttribute('hidden', '');
            document.dispatchEvent(new CustomEvent('modalClosed'));
        });
    }

    /**
     * Close mobile menu
     */
    closeMobileMenu() {
        const mobileMenu = document.querySelector('.mobile-menu.open');
        if (mobileMenu) {
            mobileMenu.classList.remove('open');
        }
    }

    /**
     * Open modal with proper focus management
     */
    openModal(modal) {
        modal.removeAttribute('hidden');
        
        // Focus first focusable element in modal
        const focusableElement = modal.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
        if (focusableElement) {
            focusableElement.focus();
        }
    }

    /**
     * Add skip links
     */
    addSkipLinks() {
        const skipLinks = document.createElement('div');
        skipLinks.className = 'skip-links';
        skipLinks.innerHTML = `
            <a href="#main-content" class="skip-link">Skip to main content</a>
            <a href="#navigation" class="skip-link">Skip to navigation</a>
            <a href="#search" class="skip-link">Skip to search</a>
        `;
        
        document.body.insertBefore(skipLinks, document.body.firstChild);
    }
}

// Initialize accessibility helper
document.addEventListener('DOMContentLoaded', () => {
    window.accessibilityHelper = new AccessibilityHelper();
    window.accessibilityHelper.addSkipLinks();
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AccessibilityHelper;
}
