// Authentication JavaScript

// Password validation requirements
const passwordRequirements = {
    minLength: 8,
    hasUppercase: /[A-Z]/,
    hasLowercase: /[a-z]/,
    hasNumber: /[0-9]/,
    hasSpecial: /[!@#$%^&*]/
};

// Global variables
let currentPage = '';
let passwordStrength = 0;

document.addEventListener('DOMContentLoaded', function() {
    // Determine current page
    currentPage = window.location.pathname.includes('signin') ? 'signin' : 'signup';
    
    // Initialize page-specific functionality
    if (currentPage === 'signup') {
        initializeSignUp();
    } else {
        initializeSignIn();
    }
    
    // Common initialization
    initializeCommon();
});

// Initialize Sign Up page
function initializeSignUp() {
    // User type selection
    setupUserTypeSelection();
    
    // Contact method tabs
    setupContactMethodTabs();
    
    // Password validation
    setupPasswordValidation();
    
    // Form submission
    setupSignUpForm();
}

// Initialize Sign In page
function initializeSignIn() {
    // Login ID validation
    setupLoginIdValidation();
    
    // Form submission
    setupSignInForm();
}

// Common initialization
function initializeCommon() {
    // Password toggle functionality
    setupPasswordToggles();
    
    // Notification system
    setupNotifications();
    
    // Social login buttons
    setupSocialLogin();
}

// User Type Selection
function setupUserTypeSelection() {
    const userTypeOptions = document.querySelectorAll('.user-type-option');
    
    userTypeOptions.forEach(option => {
        option.addEventListener('click', function() {
            // Remove active class from all options
            userTypeOptions.forEach(opt => opt.classList.remove('active'));
            
            // Add active class to clicked option
            this.classList.add('active');
            
            // Check the radio button
            const radio = this.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;
            }
        });
    });
}

// Contact Method Tabs
function setupContactMethodTabs() {
    const methodTabs = document.querySelectorAll('.method-tab');
    const emailInput = document.getElementById('email-input');
    const phoneInput = document.getElementById('phone-input');
    
    methodTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const method = this.getAttribute('data-method');
            
            // Update tab states
            methodTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // Show/hide appropriate input
            if (method === 'email') {
                emailInput.style.display = 'block';
                phoneInput.style.display = 'none';
                document.getElementById('email').required = true;
                document.getElementById('phone').required = false;
            } else {
                emailInput.style.display = 'none';
                phoneInput.style.display = 'block';
                document.getElementById('email').required = false;
                document.getElementById('phone').required = true;
            }
        });
    });
}

// Password Validation
function setupPasswordValidation() {
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            validatePassword(this.value);
            if (confirmPasswordInput.value) {
                validatePasswordMatch();
            }
        });
    }
    
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', validatePasswordMatch);
    }
}

// Validate Password Strength
function validatePassword(password) {
    const requirements = {
        length: password.length >= passwordRequirements.minLength,
        uppercase: passwordRequirements.hasUppercase.test(password),
        lowercase: passwordRequirements.hasLowercase.test(password),
        number: passwordRequirements.hasNumber.test(password),
        special: passwordRequirements.hasSpecial.test(password)
    };
    
    // Update requirement indicators
    updateRequirementIndicator('req-length', requirements.length);
    updateRequirementIndicator('req-uppercase', requirements.uppercase);
    updateRequirementIndicator('req-lowercase', requirements.lowercase);
    updateRequirementIndicator('req-number', requirements.number);
    updateRequirementIndicator('req-special', requirements.special);
    
    // Calculate strength
    const metRequirements = Object.values(requirements).filter(Boolean).length;
    passwordStrength = metRequirements;
    
    // Update strength bar
    updatePasswordStrengthBar(metRequirements);
    
    return Object.values(requirements).every(Boolean);
}

// Update Requirement Indicator
function updateRequirementIndicator(elementId, isMet) {
    const element = document.getElementById(elementId);
    if (element) {
        if (isMet) {
            element.classList.add('met');
        } else {
            element.classList.remove('met');
        }
    }
}

// Update Password Strength Bar
function updatePasswordStrengthBar(strength) {
    const strengthFill = document.querySelector('.strength-fill');
    const strengthText = document.querySelector('.strength-text');
    
    if (strengthFill && strengthText) {
        // Remove all strength classes
        strengthFill.classList.remove('weak', 'fair', 'good', 'strong');
        
        let strengthClass = '';
        let strengthLabel = '';
        
        switch (strength) {
            case 0:
            case 1:
                strengthClass = 'weak';
                strengthLabel = 'Very weak';
                break;
            case 2:
                strengthClass = 'weak';
                strengthLabel = 'Weak';
                break;
            case 3:
                strengthClass = 'fair';
                strengthLabel = 'Fair';
                break;
            case 4:
                strengthClass = 'good';
                strengthLabel = 'Good';
                break;
            case 5:
                strengthClass = 'strong';
                strengthLabel = 'Strong';
                break;
        }
        
        strengthFill.classList.add(strengthClass);
        strengthText.textContent = `Password strength: ${strengthLabel}`;
    }
}

// Validate Password Match
function validatePasswordMatch() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const errorElement = document.getElementById('confirmPassword-error');
    const confirmInput = document.getElementById('confirmPassword');
    
    if (confirmPassword && password !== confirmPassword) {
        showFieldError('confirmPassword', 'Passwords do not match');
        confirmInput.classList.add('error');
        confirmInput.classList.remove('success');
        return false;
    } else if (confirmPassword) {
        clearFieldError('confirmPassword');
        confirmInput.classList.remove('error');
        confirmInput.classList.add('success');
        return true;
    }
    
    return true;
}

// Login ID Validation (Sign In)
function setupLoginIdValidation() {
    const loginIdInput = document.getElementById('loginId');
    const typeIndicator = document.getElementById('input-type-indicator');

    if (loginIdInput && typeIndicator) {
        loginIdInput.addEventListener('input', function() {
            const value = this.value.trim();
            const isEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
            const isPhone = /^[\+]?[0-9\s\-\(\)]{10,}$/.test(value);
            const isUsername = /^[a-zA-Z0-9_]{3,}$/.test(value);

            if (isEmail) {
                typeIndicator.innerHTML = '<i class="fas fa-envelope"></i>';
                typeIndicator.style.color = '#28a745';
                typeIndicator.title = 'Email address detected';
            } else if (isPhone) {
                typeIndicator.innerHTML = '<i class="fas fa-phone"></i>';
                typeIndicator.style.color = '#28a745';
                typeIndicator.title = 'Phone number detected';
            } else if (isUsername) {
                typeIndicator.innerHTML = '<i class="fas fa-user"></i>';
                typeIndicator.style.color = '#28a745';
                typeIndicator.title = 'Username detected';
            } else {
                typeIndicator.innerHTML = '<i class="fas fa-question"></i>';
                typeIndicator.style.color = '#666';
                typeIndicator.title = 'Enter username, email, or phone';
            }
        });
    }
}

// Password Toggle Functionality
function setupPasswordToggles() {
    const passwordToggles = document.querySelectorAll('.password-toggle');
    
    passwordToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const targetInput = document.getElementById(targetId);
            const icon = this.querySelector('i');
            
            if (targetInput.type === 'password') {
                targetInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                targetInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
}

// Sign Up Form Submission
function setupSignUpForm() {
    const form = document.getElementById('signup-form');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (validateSignUpForm()) {
                submitSignUpForm();
            }
        });
    }
}

// Validate Sign Up Form
function validateSignUpForm() {
    let isValid = true;
    
    // Clear previous errors
    clearAllErrors();
    
    // Validate required fields
    const firstName = document.getElementById('firstName').value.trim();
    const lastName = document.getElementById('lastName').value.trim();
    
    if (!firstName) {
        showFieldError('firstName', 'First name is required');
        isValid = false;
    }
    
    if (!lastName) {
        showFieldError('lastName', 'Last name is required');
        isValid = false;
    }
    
    // Validate contact method
    const activeMethod = document.querySelector('.method-tab.active').getAttribute('data-method');
    
    if (activeMethod === 'email') {
        const email = document.getElementById('email').value.trim();
        if (!email) {
            showFieldError('email', 'Email is required');
            isValid = false;
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            showFieldError('email', 'Please enter a valid email address');
            isValid = false;
        }
    } else {
        const phone = document.getElementById('phone').value.trim();
        if (!phone) {
            showFieldError('phone', 'Phone number is required');
            isValid = false;
        } else if (!/^[\d\s\-\+\(\)]{10,}$/.test(phone)) {
            showFieldError('phone', 'Please enter a valid phone number');
            isValid = false;
        }
    }
    
    // Validate password
    const password = document.getElementById('password').value;
    if (!password) {
        showFieldError('password', 'Password is required');
        isValid = false;
    } else if (!validatePassword(password)) {
        showFieldError('password', 'Password does not meet requirements');
        isValid = false;
    }
    
    // Validate password match
    if (!validatePasswordMatch()) {
        isValid = false;
    }
    
    // Validate terms acceptance
    const terms = document.getElementById('terms').checked;
    if (!terms) {
        showFieldError('terms', 'You must accept the terms and conditions');
        isValid = false;
    }
    
    return isValid;
}

// Submit Sign Up Form
function submitSignUpForm() {
    const submitBtn = document.getElementById('signup-btn');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoader = submitBtn.querySelector('.btn-loader');

    // Show loading state
    btnText.style.display = 'none';
    btnLoader.style.display = 'flex';
    submitBtn.disabled = true;

    // Get form data
    const firstName = document.getElementById('firstName').value.trim();
    const lastName = document.getElementById('lastName').value.trim();
    const fullName = `${firstName} ${lastName}`;

    // Get contact method
    const activeMethod = document.querySelector('.method-tab.active').getAttribute('data-method');
    const email = activeMethod === 'email' ? document.getElementById('email').value.trim() : null;
    const phone = activeMethod === 'phone' ? document.getElementById('phone').value.trim() : null;

    const password = document.getElementById('password').value;
    const userType = document.querySelector('input[name="userType"]:checked').value;

    // Generate username from email or phone
    let username;
    if (email) {
        username = email.split('@')[0].toLowerCase().replace(/[^a-z0-9]/g, '');
    } else if (phone) {
        username = 'user_' + phone.replace(/[^0-9]/g, '').slice(-8);
    }

    // Ensure username is unique
    let baseUsername = username;
    let counter = 1;
    while (userAccounts && userAccounts[username]) {
        username = baseUsername + counter;
        counter++;
    }

    const userData = {
        username: username,
        password: password,
        email: email,
        phone: phone,
        fullName: fullName,
        userType: userType
    };

    // Use the registration system
    setTimeout(() => {
        const result = register(userData);

        // Reset button state
        btnText.style.display = 'block';
        btnLoader.style.display = 'none';
        submitBtn.disabled = false;

        if (result.success) {
            // Show success notification with username
            showNotification(`Account created successfully! Your username is: ${username}. You can now sign in.`, 'success');

            // Redirect after delay
            setTimeout(() => {
                window.location.href = 'signin.html';
            }, 3000);
        } else {
            // Show error notification
            showNotification(result.message, 'error');
        }
    }, 1500);
}

// Sign In Form Submission
function setupSignInForm() {
    const form = document.getElementById('signin-form');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (validateSignInForm()) {
                submitSignInForm();
            }
        });
    }
}

// Validate Sign In Form
function validateSignInForm() {
    let isValid = true;

    // Clear previous errors
    clearAllErrors();

    const loginId = document.getElementById('loginId').value.trim();
    const password = document.getElementById('loginPassword').value;

    if (!loginId) {
        showFieldError('loginId', 'Please enter your username, email, or phone number');
        isValid = false;
    } else {
        // Validate format
        const isEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(loginId);
        const isPhone = /^[\+]?[0-9\s\-\(\)]{10,}$/.test(loginId);
        const isUsername = /^[a-zA-Z0-9_]{3,}$/.test(loginId);

        if (!isEmail && !isPhone && !isUsername) {
            showFieldError('loginId', 'Please enter a valid username, email, or phone number');
            isValid = false;
        }
    }

    if (!password) {
        showFieldError('password', 'Password is required');
        isValid = false;
    }

    return isValid;
}

// Submit Sign In Form
function submitSignInForm() {
    const submitBtn = document.getElementById('signin-btn');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoader = submitBtn.querySelector('.btn-loader');

    // Show loading state
    btnText.style.display = 'none';
    btnLoader.style.display = 'flex';
    submitBtn.disabled = true;

    // Get form data
    const username = document.getElementById('loginId').value.trim();
    const password = document.getElementById('loginPassword').value;

    // Use the authentication system
    setTimeout(() => {
        const result = login(username, password);

        // Reset button state
        btnText.style.display = 'block';
        btnLoader.style.display = 'none';
        submitBtn.disabled = false;

        if (result.success) {
            // Success
            showNotification(`Welcome back, ${result.user.fullName}!`, 'success');

            setTimeout(() => {
                // Redirect based on user type
                if (result.user.userType === 'Shop & Sell') {
                    window.location.href = 'dashboard.html';
                } else {
                    window.location.href = 'customer-dashboard.html';
                }
            }, 1500);
        } else {
            // Error
            showNotification(result.message, 'error');
        }
    }, 1000);
}

// Social Login Setup
function setupSocialLogin() {
    const googleBtn = document.querySelector('.google-btn');
    const facebookBtn = document.querySelector('.facebook-btn');

    if (googleBtn) {
        googleBtn.addEventListener('click', function() {
            showNotification('Google login integration coming soon!', 'info');
        });
    }

    if (facebookBtn) {
        facebookBtn.addEventListener('click', function() {
            showNotification('Facebook login integration coming soon!', 'info');
        });
    }
}

// Notification System
function setupNotifications() {
    const notificationClose = document.querySelector('.notification-close');

    if (notificationClose) {
        notificationClose.addEventListener('click', function() {
            hideNotification();
        });
    }
}

// Show Notification
function showNotification(message, type = 'info') {
    const notification = document.getElementById('notification');
    const icon = notification.querySelector('.notification-icon');
    const messageElement = notification.querySelector('.notification-message');

    // Set message
    messageElement.textContent = message;

    // Set icon based on type
    let iconClass = 'fas fa-info-circle';
    switch (type) {
        case 'success':
            iconClass = 'fas fa-check-circle';
            break;
        case 'error':
            iconClass = 'fas fa-exclamation-circle';
            break;
        case 'warning':
            iconClass = 'fas fa-exclamation-triangle';
            break;
    }

    icon.className = `notification-icon ${iconClass}`;

    // Set notification type class
    notification.className = `notification ${type}`;

    // Show notification
    notification.style.display = 'block';

    // Auto hide after 5 seconds
    setTimeout(() => {
        hideNotification();
    }, 5000);
}

// Hide Notification
function hideNotification() {
    const notification = document.getElementById('notification');
    notification.style.display = 'none';
}

// Show Field Error
function showFieldError(fieldName, message) {
    const errorElement = document.getElementById(`${fieldName}-error`);
    const inputElement = document.getElementById(fieldName);

    if (errorElement) {
        errorElement.textContent = message;
    }

    if (inputElement) {
        inputElement.classList.add('error');
        inputElement.classList.remove('success');
    }
}

// Clear Field Error
function clearFieldError(fieldName) {
    const errorElement = document.getElementById(`${fieldName}-error`);
    const inputElement = document.getElementById(fieldName);

    if (errorElement) {
        errorElement.textContent = '';
    }

    if (inputElement) {
        inputElement.classList.remove('error');
    }
}

// Clear All Errors
function clearAllErrors() {
    const errorElements = document.querySelectorAll('.error-message');
    const inputElements = document.querySelectorAll('input.error');

    errorElements.forEach(element => {
        element.textContent = '';
    });

    inputElements.forEach(element => {
        element.classList.remove('error');
    });
}

// Password Hashing (Client-side demonstration - in production, use server-side hashing)
function hashPassword(password) {
    // This is a simple demonstration. In production, use proper server-side hashing
    // with bcrypt or similar secure hashing algorithms
    let hash = 0;
    if (password.length === 0) return hash;

    for (let i = 0; i < password.length; i++) {
        const char = password.charCodeAt(i);
        hash = ((hash << 5) - hash) + char;
        hash = hash & hash; // Convert to 32-bit integer
    }

    return Math.abs(hash).toString(16);
}

// Form Data Collection
function collectSignUpData() {
    const userType = document.querySelector('input[name="userType"]:checked').value;
    const activeMethod = document.querySelector('.method-tab.active').getAttribute('data-method');

    const data = {
        userType: userType,
        firstName: document.getElementById('firstName').value.trim(),
        lastName: document.getElementById('lastName').value.trim(),
        contactMethod: activeMethod,
        password: hashPassword(document.getElementById('password').value),
        termsAccepted: document.getElementById('terms').checked,
        marketingConsent: document.getElementById('marketing').checked,
        timestamp: new Date().toISOString()
    };

    if (activeMethod === 'email') {
        data.email = document.getElementById('email').value.trim();
    } else {
        data.countryCode = document.getElementById('countryCode').value;
        data.phone = document.getElementById('phone').value.trim();
    }

    return data;
}

// Form Data Collection for Sign In
function collectSignInData() {
    return {
        loginId: document.getElementById('loginId').value.trim(),
        password: hashPassword(document.getElementById('loginPassword').value),
        rememberMe: document.getElementById('rememberMe').checked,
        timestamp: new Date().toISOString()
    };
}

// Input Validation Helpers
function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function isValidPhone(phone) {
    return /^[\+]?[1-9][\d]{0,15}$/.test(phone.replace(/\s/g, ''));
}

function isStrongPassword(password) {
    return password.length >= passwordRequirements.minLength &&
           passwordRequirements.hasUppercase.test(password) &&
           passwordRequirements.hasLowercase.test(password) &&
           passwordRequirements.hasNumber.test(password) &&
           passwordRequirements.hasSpecial.test(password);
}

// Security Features
function generateSecureToken() {
    const array = new Uint8Array(32);
    crypto.getRandomValues(array);
    return Array.from(array, byte => byte.toString(16).padStart(2, '0')).join('');
}

// Local Storage Management (for demo purposes)
function saveUserSession(userData) {
    const sessionData = {
        ...userData,
        sessionToken: generateSecureToken(),
        loginTime: new Date().toISOString(),
        expiresAt: new Date(Date.now() + 24 * 60 * 60 * 1000).toISOString() // 24 hours
    };

    localStorage.setItem('lflshop_session', JSON.stringify(sessionData));
}

function clearUserSession() {
    localStorage.removeItem('lflshop_session');
}

function getUserSession() {
    const sessionData = localStorage.getItem('lflshop_session');
    if (sessionData) {
        const parsed = JSON.parse(sessionData);
        if (new Date(parsed.expiresAt) > new Date()) {
            return parsed;
        } else {
            clearUserSession();
        }
    }
    return null;
}

// Initialize page based on authentication state
function checkAuthenticationState() {
    const session = getUserSession();
    const currentPath = window.location.pathname;

    if (session && (currentPath.includes('signin') || currentPath.includes('signup'))) {
        // User is already logged in, redirect to home
        window.location.href = 'index.html';
    }
}

// Call authentication check on page load
document.addEventListener('DOMContentLoaded', function() {
    checkAuthenticationState();
});

// Prevent form submission on Enter key in certain fields
document.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && e.target.type !== 'submit' && e.target.tagName !== 'BUTTON') {
        const form = e.target.closest('form');
        if (form) {
            e.preventDefault();
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn && !submitBtn.disabled) {
                submitBtn.click();
            }
        }
    }
});
