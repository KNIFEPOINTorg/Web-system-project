// Delivery Info Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize FAQ functionality
    initializeFAQ();
    
    // Initialize notification signup
    initializeNotifySignup();
    
    // Initialize contact interactions
    initializeContactInteractions();
    
    // Initialize animations
    initializeAnimations();
});

// Initialize FAQ functionality
function initializeFAQ() {
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        
        question.addEventListener('click', function() {
            // Close other FAQ items
            faqItems.forEach(otherItem => {
                if (otherItem !== item) {
                    otherItem.classList.remove('active');
                }
            });
            
            // Toggle current item
            item.classList.toggle('active');
        });
    });
}

// Initialize notification signup
function initializeNotifySignup() {
    const notifyForm = document.querySelector('.notify-form');
    
    if (notifyForm) {
        notifyForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const emailInput = this.querySelector('input[type="email"]');
            const email = emailInput.value.trim();
            
            if (validateEmail(email)) {
                submitNotificationSignup(email);
            } else {
                showNotification('Please enter a valid email address', 'error');
            }
        });
    }
}

// Submit notification signup
function submitNotificationSignup(email) {
    const submitBtn = document.querySelector('.notify-form .btn-primary');
    const originalText = submitBtn.innerHTML;
    
    // Show loading state
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Subscribing...';
    submitBtn.disabled = true;
    
    // Simulate API call
    setTimeout(() => {
        // Reset button
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        
        // Clear form
        document.querySelector('.notify-form input').value = '';
        
        // Show success message
        showNotification('Thank you! We\'ll notify you when we expand to your area.', 'success');
    }, 2000);
}

// Initialize contact interactions
function initializeContactInteractions() {
    const contactCards = document.querySelectorAll('.contact-card');
    
    contactCards.forEach(card => {
        card.addEventListener('click', function() {
            const cardType = this.querySelector('h4').textContent;
            
            switch (cardType) {
                case 'Call Us':
                    showNotification('Phone: +251 11 123 4567', 'info');
                    break;
                case 'Email Support':
                    window.location.href = 'mailto:delivery@lflshop.com';
                    break;
                case 'Live Chat':
                    showNotification('Live chat feature coming soon!', 'info');
                    break;
            }
        });
    });
}

// Initialize animations
function initializeAnimations() {
    // Add CSS for animations
    addAnimationStyles();
    
    // Intersection Observer for fade-in animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
            }
        });
    }, observerOptions);
    
    // Observe sections for animation
    const sections = document.querySelectorAll('section');
    sections.forEach(section => {
        section.classList.add('animate-fade');
        observer.observe(section);
    });
}

// Add animation styles
function addAnimationStyles() {
    const style = document.createElement('style');
    style.textContent = `
        .animate-fade {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }
        
        .animate-fade.animate-in {
            opacity: 1;
            transform: translateY(0);
        }
        
        .option-card:hover .option-icon {
            transform: scale(1.1);
            transition: transform 0.3s ease;
        }
        
        .expansion-card:hover .expansion-icon {
            transform: scale(1.1);
            transition: transform 0.3s ease;
        }
        
        .contact-card:hover {
            cursor: pointer;
        }
        
        .subcity:hover,
        .city:hover {
            transform: scale(1.05);
            transition: transform 0.2s ease;
            cursor: default;
        }
    `;
    document.head.appendChild(style);
}

// Email validation
function validateEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// Show notification
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => notification.remove());
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    
    // Set colors based on type
    let bgColor = '#17a2b8';
    switch (type) {
        case 'success':
            bgColor = '#28a745';
            break;
        case 'error':
            bgColor = '#dc3545';
            break;
        case 'warning':
            bgColor = '#ffc107';
            break;
    }
    
    notification.innerHTML = `
        <div class="notification-content">
            <i class="notification-icon fas fa-${type === 'error' ? 'exclamation-circle' : type === 'success' ? 'check-circle' : 'info-circle'}"></i>
            <span class="notification-message">${message}</span>
            <button class="notification-close">&times;</button>
        </div>
    `;
    
    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        z-index: 1000;
        max-width: 400px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
        border-left: 4px solid ${bgColor};
        animation: slideIn 0.3s ease;
        display: flex;
        align-items: center;
        padding: 16px 20px;
        gap: 12px;
    `;
    
    // Style the content
    const icon = notification.querySelector('.notification-icon');
    const message = notification.querySelector('.notification-message');
    const closeBtn = notification.querySelector('.notification-close');
    
    icon.style.cssText = `color: ${bgColor}; font-size: 1.2rem;`;
    message.style.cssText = `flex: 1; font-family: 'Open Sans', sans-serif; font-size: 0.9rem; color: #2C2C2C;`;
    closeBtn.style.cssText = `background: none; border: none; font-size: 1.2rem; color: #666; cursor: pointer; padding: 4px;`;
    
    // Add animation keyframes if not exists
    if (!document.querySelector('#notification-animations')) {
        const animationStyle = document.createElement('style');
        animationStyle.id = 'notification-animations';
        animationStyle.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
        `;
        document.head.appendChild(animationStyle);
    }
    
    // Add to page
    document.body.appendChild(notification);
    
    // Auto hide after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
    
    // Close button functionality
    closeBtn.addEventListener('click', () => {
        notification.remove();
    });
}
