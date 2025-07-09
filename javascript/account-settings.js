// Account Settings JavaScript

// Check authentication and initialize
document.addEventListener('DOMContentLoaded', function() {
    // Check if user is logged in
    if (!isLoggedIn()) {
        showNotification('Please log in to access account settings.', 'error');
        window.location.href = 'signin.html';
        return;
    }
    
    // Initialize account settings
    initializeAccountSettings();
    setupEventListeners();
    loadUserData();
    updateCartIcon();
});

// Initialize account settings
function initializeAccountSettings() {
    const currentUser = getCurrentUser();
    
    // Show seller-specific sections for Shop & Sell users
    if (canSell()) {
        const sellerOnlyElements = document.querySelectorAll('.seller-only');
        sellerOnlyElements.forEach(element => {
            element.style.display = 'block';
        });
    }
    
    // Setup section navigation
    setupSectionNavigation();
}

// Setup section navigation
function setupSectionNavigation() {
    const navItems = document.querySelectorAll('.nav-item');
    const sections = document.querySelectorAll('.settings-section');
    
    navItems.forEach(item => {
        item.addEventListener('click', function() {
            const sectionId = this.getAttribute('data-section');
            
            // Update active nav item
            navItems.forEach(nav => nav.classList.remove('active'));
            this.classList.add('active');
            
            // Show corresponding section
            sections.forEach(section => section.classList.remove('active'));
            const targetSection = document.getElementById(`${sectionId}-section`);
            if (targetSection) {
                targetSection.classList.add('active');
            }
        });
    });
}

// Load user data into forms
function loadUserData() {
    const currentUser = getCurrentUser();
    if (!currentUser) return;
    
    // Load profile data
    loadProfileData(currentUser);
    
    // Load contact data
    loadContactData(currentUser);
    
    // Load preferences
    loadPreferences(currentUser);
    
    // Load business data (if seller)
    if (canSell()) {
        loadBusinessData(currentUser);
    }
}

// Load profile data
function loadProfileData(user) {
    const nameParts = user.fullName.split(' ');
    const firstName = nameParts[0] || '';
    const lastName = nameParts.slice(1).join(' ') || '';
    
    document.getElementById('first-name').value = firstName;
    document.getElementById('last-name').value = lastName;
    document.getElementById('display-name').value = user.displayName || user.fullName;
    
    // Load profile picture
    const profilePicture = document.getElementById('profile-picture-preview');
    if (profilePicture && user.profilePicture) {
        profilePicture.src = user.profilePicture;
    }
}

// Load contact data
function loadContactData(user) {
    document.getElementById('email').value = user.email || '';
    
    if (user.phone) {
        // Extract country code and phone number
        const phoneMatch = user.phone.match(/^(\+\d+)(.+)$/);
        if (phoneMatch) {
            document.getElementById('country-code').value = phoneMatch[1];
            document.getElementById('phone').value = phoneMatch[2].trim();
        } else {
            document.getElementById('phone').value = user.phone;
        }
    }
}

// Load preferences
function loadPreferences(user) {
    // Set default preferences if not set
    const preferences = user.preferences || {
        language: 'en',
        currency: 'ETB',
        emailNotifications: true,
        smsNotifications: false,
        orderUpdates: true,
        promotionalEmails: true,
        profileVisibility: true,
        purchaseHistory: false
    };
    
    // Load language and currency
    document.getElementById('language').value = preferences.language;
    document.getElementById('currency').value = preferences.currency;
    
    // Load notification preferences
    document.getElementById('email-notifications').checked = preferences.emailNotifications;
    document.getElementById('sms-notifications').checked = preferences.smsNotifications;
    document.getElementById('order-updates').checked = preferences.orderUpdates;
    document.getElementById('promotional-emails').checked = preferences.promotionalEmails;
    
    // Load privacy preferences
    document.getElementById('profile-visibility').checked = preferences.profileVisibility;
    document.getElementById('purchase-history').checked = preferences.purchaseHistory;
}

// Load business data
function loadBusinessData(user) {
    document.getElementById('business-name').value = user.businessName || '';
    document.getElementById('business-description').value = user.businessDescription || '';
    
    if (user.businessLocation) {
        const locationParts = user.businessLocation.split(', ');
        if (locationParts.length >= 2) {
            document.getElementById('business-subcity').value = locationParts[0];
            document.getElementById('business-city').value = locationParts[1];
        }
    }
    
    document.getElementById('business-address').value = user.businessAddress || '';
}

// Setup event listeners
function setupEventListeners() {
    // Profile picture upload
    setupProfilePictureUpload();
    
    // Password strength checker
    setupPasswordStrengthChecker();
    
    // Password toggle buttons
    setupPasswordToggles();
    
    // Form submissions
    setupFormSubmissions();
    
    // Account actions
    setupAccountActions();
    
    // Modal functionality
    setupModalFunctionality();
}

// Setup profile picture upload
function setupProfilePictureUpload() {
    const fileInput = document.getElementById('profile-picture-input');
    const preview = document.getElementById('profile-picture-preview');
    const removeBtn = document.getElementById('remove-picture-btn');
    
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 5 * 1024 * 1024) { // 5MB limit
                    showNotification('File size must be less than 5MB', 'error');
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    showNotification('Profile picture updated', 'success');
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    if (removeBtn) {
        removeBtn.addEventListener('click', function() {
            preview.src = 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-4.0.3&auto=format&fit=crop&w=150&q=80';
            fileInput.value = '';
            showNotification('Profile picture removed', 'success');
        });
    }
}

// Setup password strength checker
function setupPasswordStrengthChecker() {
    const passwordInput = document.getElementById('new-password');
    const strengthBar = document.querySelector('.strength-fill');
    const strengthText = document.querySelector('.strength-text');
    const requirements = document.querySelectorAll('.password-requirements li');
    
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const strength = calculatePasswordStrength(password);
            
            // Update strength bar
            strengthBar.className = `strength-fill ${strength.level}`;
            strengthText.textContent = `Password strength: ${strength.text}`;
            
            // Update requirements
            updatePasswordRequirements(password, requirements);
        });
    }
}

// Calculate password strength
function calculatePasswordStrength(password) {
    let score = 0;
    
    if (password.length >= 8) score++;
    if (/[a-z]/.test(password)) score++;
    if (/[A-Z]/.test(password)) score++;
    if (/\d/.test(password)) score++;
    if (/[^a-zA-Z\d]/.test(password)) score++;
    
    const levels = ['very-weak', 'weak', 'fair', 'good', 'strong'];
    const texts = ['Very weak', 'Weak', 'Fair', 'Good', 'Strong'];
    
    return {
        level: levels[score] || 'very-weak',
        text: texts[score] || 'Very weak'
    };
}

// Update password requirements
function updatePasswordRequirements(password, requirements) {
    const checks = [
        password.length >= 8,
        /[A-Z]/.test(password),
        /[a-z]/.test(password),
        /\d/.test(password),
        /[^a-zA-Z\d]/.test(password)
    ];
    
    requirements.forEach((req, index) => {
        if (checks[index]) {
            req.classList.add('valid');
        } else {
            req.classList.remove('valid');
        }
    });
}

// Setup password toggle buttons
function setupPasswordToggles() {
    const toggleButtons = document.querySelectorAll('.password-toggle');
    
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const targetInput = document.getElementById(targetId);
            const icon = this.querySelector('i');
            
            if (targetInput.type === 'password') {
                targetInput.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                targetInput.type = 'password';
                icon.className = 'fas fa-eye';
            }
        });
    });
}

// Setup form submissions
function setupFormSubmissions() {
    // Profile form
    const profileForm = document.getElementById('profile-form');
    if (profileForm) {
        profileForm.addEventListener('submit', handleProfileSubmit);
    }
    
    // Contact form
    const contactForm = document.getElementById('contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', handleContactSubmit);
    }
    
    // Security form
    const securityForm = document.getElementById('security-form');
    if (securityForm) {
        securityForm.addEventListener('submit', handleSecuritySubmit);
    }
    
    // Preferences form
    const preferencesForm = document.getElementById('preferences-form');
    if (preferencesForm) {
        preferencesForm.addEventListener('submit', handlePreferencesSubmit);
    }
    
    // Business form
    const businessForm = document.getElementById('business-form');
    if (businessForm) {
        businessForm.addEventListener('submit', handleBusinessSubmit);
    }
}

// Handle profile form submission
function handleProfileSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const firstName = formData.get('firstName');
    const lastName = formData.get('lastName');
    const displayName = formData.get('displayName');
    
    // Update user data
    const currentUser = getCurrentUser();
    currentUser.fullName = `${firstName} ${lastName}`;
    currentUser.displayName = displayName;
    
    // Get profile picture if changed
    const profilePicture = document.getElementById('profile-picture-preview').src;
    if (profilePicture && !profilePicture.includes('unsplash.com')) {
        currentUser.profilePicture = profilePicture;
    }
    
    // Save to localStorage
    localStorage.setItem('lflshop_user', JSON.stringify(currentUser));
    
    showNotification('Profile updated successfully!', 'success');
}

// Handle contact form submission
function handleContactSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const email = formData.get('email');
    const countryCode = formData.get('countryCode');
    const phone = formData.get('phone');
    
    // Validate Ethiopian phone number
    if (phone && countryCode === '+251') {
        if (!/^9\d{8}$/.test(phone)) {
            showNotification('Please enter a valid Ethiopian phone number (9XXXXXXXX)', 'error');
            return;
        }
    }
    
    // Update user data
    const currentUser = getCurrentUser();
    currentUser.email = email;
    currentUser.phone = phone ? `${countryCode}${phone}` : null;
    
    // Save to localStorage
    localStorage.setItem('lflshop_user', JSON.stringify(currentUser));
    
    showNotification('Contact information updated successfully!', 'success');
}

// Handle security form submission
function handleSecuritySubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const currentPassword = formData.get('currentPassword');
    const newPassword = formData.get('newPassword');
    const confirmPassword = formData.get('confirmPassword');
    
    // Validate current password
    const currentUser = getCurrentUser();
    if (currentPassword !== currentUser.password) {
        showNotification('Current password is incorrect', 'error');
        return;
    }
    
    // Validate new password
    if (newPassword !== confirmPassword) {
        showNotification('New passwords do not match', 'error');
        return;
    }
    
    // Validate password strength
    const strength = calculatePasswordStrength(newPassword);
    if (strength.level === 'very-weak' || strength.level === 'weak') {
        showNotification('Please choose a stronger password', 'error');
        return;
    }
    
    // Update password
    currentUser.password = newPassword;
    
    // Save to localStorage
    localStorage.setItem('lflshop_user', JSON.stringify(currentUser));
    
    // Clear form
    e.target.reset();
    
    showNotification('Password updated successfully!', 'success');
}

// Handle preferences form submission
function handlePreferencesSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const preferences = {
        language: formData.get('language'),
        currency: formData.get('currency'),
        emailNotifications: formData.has('emailNotifications'),
        smsNotifications: formData.has('smsNotifications'),
        orderUpdates: formData.has('orderUpdates'),
        promotionalEmails: formData.has('promotionalEmails'),
        profileVisibility: formData.has('profileVisibility'),
        purchaseHistory: formData.has('purchaseHistory')
    };
    
    // Update user data
    const currentUser = getCurrentUser();
    currentUser.preferences = preferences;
    
    // Save to localStorage
    localStorage.setItem('lflshop_user', JSON.stringify(currentUser));
    
    showNotification('Preferences saved successfully!', 'success');
}

// Handle business form submission
function handleBusinessSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const businessName = formData.get('businessName');
    const businessDescription = formData.get('businessDescription');
    const businessCity = formData.get('businessCity');
    const businessSubcity = formData.get('businessSubcity');
    const businessAddress = formData.get('businessAddress');
    
    // Update user data
    const currentUser = getCurrentUser();
    currentUser.businessName = businessName;
    currentUser.businessDescription = businessDescription;
    currentUser.businessLocation = businessSubcity ? `${businessSubcity}, ${businessCity}` : businessCity;
    currentUser.businessAddress = businessAddress;
    
    // Save to localStorage
    localStorage.setItem('lflshop_user', JSON.stringify(currentUser));
    
    showNotification('Business information updated successfully!', 'success');
}

// Setup account actions
function setupAccountActions() {
    // Export data
    const exportBtn = document.getElementById('export-data-btn');
    if (exportBtn) {
        exportBtn.addEventListener('click', exportAccountData);
    }
    
    // Deactivate account
    const deactivateBtn = document.getElementById('deactivate-account-btn');
    if (deactivateBtn) {
        deactivateBtn.addEventListener('click', () => showConfirmationModal('deactivate'));
    }
    
    // Delete account
    const deleteBtn = document.getElementById('delete-account-btn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', () => showConfirmationModal('delete'));
    }
}

// Export account data
function exportAccountData() {
    const currentUser = getCurrentUser();
    const data = {
        profile: currentUser,
        notifications: JSON.parse(localStorage.getItem('lflshop_notifications') || '[]'),
        cart: JSON.parse(localStorage.getItem('lflshop_cart') || '{}'),
        exportDate: new Date().toISOString()
    };
    
    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `lflshop-account-data-${new Date().toISOString().split('T')[0]}.json`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
    
    showNotification('Account data exported successfully!', 'success');
}

// Setup modal functionality
function setupModalFunctionality() {
    const modal = document.getElementById('confirmation-modal');
    const closeBtn = document.getElementById('modal-close');
    const cancelBtn = document.getElementById('modal-cancel');
    const confirmBtn = document.getElementById('modal-confirm');
    
    if (closeBtn) {
        closeBtn.addEventListener('click', closeConfirmationModal);
    }
    
    if (cancelBtn) {
        cancelBtn.addEventListener('click', closeConfirmationModal);
    }
    
    if (confirmBtn) {
        confirmBtn.addEventListener('click', handleConfirmation);
    }
    
    // Close modal when clicking outside
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeConfirmationModal();
            }
        });
    }
}

// Show confirmation modal
function showConfirmationModal(action) {
    const modal = document.getElementById('confirmation-modal');
    const title = document.getElementById('modal-title');
    const message = document.getElementById('modal-message');
    const input = document.getElementById('modal-input');
    const confirmBtn = document.getElementById('modal-confirm');
    
    if (action === 'deactivate') {
        title.textContent = 'Deactivate Account';
        message.textContent = 'Are you sure you want to deactivate your account? You can reactivate it anytime by logging in.';
        input.style.display = 'none';
        confirmBtn.textContent = 'Deactivate';
        confirmBtn.className = 'btn-warning';
    } else if (action === 'delete') {
        title.textContent = 'Delete Account';
        message.textContent = 'This action cannot be undone. All your data will be permanently deleted. Please enter your password to confirm.';
        input.style.display = 'block';
        confirmBtn.textContent = 'Delete Account';
        confirmBtn.className = 'btn-danger';
    }
    
    modal.setAttribute('data-action', action);
    modal.style.display = 'flex';
}

// Close confirmation modal
function closeConfirmationModal() {
    const modal = document.getElementById('confirmation-modal');
    const passwordInput = document.getElementById('confirmation-password');
    
    modal.style.display = 'none';
    passwordInput.value = '';
}

// Handle confirmation
function handleConfirmation() {
    const modal = document.getElementById('confirmation-modal');
    const action = modal.getAttribute('data-action');
    const passwordInput = document.getElementById('confirmation-password');
    
    if (action === 'delete') {
        const currentUser = getCurrentUser();
        if (passwordInput.value !== currentUser.password) {
            showNotification('Incorrect password', 'error');
            return;
        }
        
        // Delete account
        localStorage.removeItem('lflshop_user');
        localStorage.removeItem('lflshop_notifications');
        localStorage.removeItem('lflshop_cart');
        
        showNotification('Your account has been deleted. You will be redirected to the home page.', 'info');
        setTimeout(() => {
            window.location.href = 'index.html';
        }, 2000);
    } else if (action === 'deactivate') {
        // Deactivate account (for demo, just log out)
        logout();
        showNotification('Your account has been deactivated. You can reactivate it by logging in again.', 'info');
        setTimeout(() => {
            window.location.href = 'index.html';
        }, 2000);
    }
    
    closeConfirmationModal();
}

// Show notification
function showNotification(message, type = 'success') {
    // Use the notification system from notifications.js if available
    if (typeof showNotificationToast === 'function') {
        showNotificationToast(message, type);
    } else {
        // Fallback notification
        const notification = document.createElement('div');
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed; top: 20px; right: 20px; padding: 10px 20px;
            background: ${type === 'error' ? '#dc3545' : type === 'success' ? '#28a745' : '#007bff'};
            color: white; border-radius: 4px; z-index: 1000;
        `;
        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 3000);
    }
}
