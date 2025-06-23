const passwordRequirements = {
    minLength: 8,
    hasUppercase: /[A-Z]/,
    hasLowercase: /[a-z]/,
    hasNumber: /[0-9]/,
    hasSpecial: /[!@#$%^&*]/
};

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function isValidPhone(phone) {
    const cleanPhone = phone.replace(/[^0-9+]/g, '');

    const patterns = [
        /^\+251[79]\d{8}$/,
        /^0[79]\d{8}$/,
        /^[79]\d{8}$/
    ];

    return patterns.some(pattern => pattern.test(cleanPhone));
}

function validatePassword(password) {
    const errors = [];
    
    if (password.length < passwordRequirements.minLength) {
        errors.push(`At least ${passwordRequirements.minLength} characters`);
    }
    
    if (!passwordRequirements.hasUppercase.test(password)) {
        errors.push('One uppercase letter');
    }
    
    if (!passwordRequirements.hasLowercase.test(password)) {
        errors.push('One lowercase letter');
    }
    
    if (!passwordRequirements.hasNumber.test(password)) {
        errors.push('One number');
    }
    
    if (!passwordRequirements.hasSpecial.test(password)) {
        errors.push('One special character (!@#$%^&*)');
    }
    
    return {
        isValid: errors.length === 0,
        errors: errors
    };
}

function calculatePasswordStrength(password) {
    let strength = 0;
    
    if (password.length >= passwordRequirements.minLength) strength += 20;
    if (passwordRequirements.hasUppercase.test(password)) strength += 20;
    if (passwordRequirements.hasLowercase.test(password)) strength += 20;
    if (passwordRequirements.hasNumber.test(password)) strength += 20;
    if (passwordRequirements.hasSpecial.test(password)) strength += 20;
    
    return strength;
}

function showFieldError(fieldId, message) {
    const errorElement = document.getElementById(`${fieldId}-error`);
    if (errorElement) {
        errorElement.textContent = message;
        errorElement.classList.add('show');
    }
    
    const field = document.getElementById(fieldId);
    if (field) {
        field.style.borderBottomColor = 'var(--color-error)';
    }
}

function clearFieldError(fieldId) {
    const errorElement = document.getElementById(`${fieldId}-error`);
    if (errorElement) {
        errorElement.textContent = '';
        errorElement.classList.remove('show');
    }
    
    const field = document.getElementById(fieldId);
    if (field) {
        field.style.borderBottomColor = '';
    }
}

function clearAllErrors() {
    const errorElements = document.querySelectorAll('.error-message');
    errorElements.forEach(element => {
        element.textContent = '';
        element.classList.remove('show');
    });
    
    const fields = document.querySelectorAll('.form-input');
    fields.forEach(field => {
        field.style.borderBottomColor = '';
    });
}

function setupRealTimeValidation() {
    const emailField = document.getElementById('email');
    if (emailField) {
        emailField.addEventListener('blur', function() {
            const email = this.value.trim();
            if (email && !isValidEmail(email)) {
                showFieldError('email', 'Please enter a valid email address');
            } else {
                clearFieldError('email');
            }
        });
    }

    const phoneField = document.getElementById('phone');
    if (phoneField) {
        phoneField.addEventListener('blur', function() {
            const phone = this.value.trim();
            if (phone && !isValidPhone(phone)) {
                showFieldError('phone', 'Please enter a valid Ethiopian phone number');
            } else {
                clearFieldError('phone');
            }
        });
    }

    const passwordField = document.getElementById('password');
    if (passwordField) {
        passwordField.addEventListener('input', function() {
            const password = this.value;
            const validation = validatePassword(password);

            if (password && !validation.isValid) {
                showFieldError('password', `Password must have: ${validation.errors.join(', ')}`);
            } else {
                clearFieldError('password');
            }
        });
    }

    const confirmPasswordField = document.getElementById('confirmPassword');
    if (confirmPasswordField && passwordField) {
        confirmPasswordField.addEventListener('blur', function() {
            const password = passwordField.value;
            const confirmPassword = this.value;

            if (confirmPassword && password !== confirmPassword) {
                showFieldError('confirmPassword', 'Passwords do not match');
            } else {
                clearFieldError('confirmPassword');
            }
        });
    }

    const loginIdField = document.getElementById('loginId');
    if (loginIdField) {
        loginIdField.addEventListener('input', function() {
            const value = this.value.trim();
            const indicator = document.getElementById('input-type-indicator');

            if (indicator) {
                let icon = 'fas fa-user';

                if (isValidEmail(value)) {
                    icon = 'fas fa-envelope';
                } else if (isValidPhone(value)) {
                    icon = 'fas fa-phone';
                }

                indicator.innerHTML = `<i class="${icon}"></i>`;
            }
        });
    }
}

function validateSignUpForm() {
    let isValid = true;
    clearAllErrors();

    const firstName = document.getElementById('firstName').value.trim();
    if (!firstName) {
        showFieldError('firstName', 'First name is required');
        isValid = false;
    }

    const lastName = document.getElementById('lastName').value.trim();
    if (!lastName) {
        showFieldError('lastName', 'Last name is required');
        isValid = false;
    }

    const activeMethod = document.querySelector('.method-tab.active').getAttribute('data-method');

    if (activeMethod === 'email') {
        const email = document.getElementById('email').value.trim();
        if (!email) {
            showFieldError('email', 'Email is required');
            isValid = false;
        } else if (!isValidEmail(email)) {
            showFieldError('email', 'Please enter a valid email address');
            isValid = false;
        }
    } else {
        const phone = document.getElementById('phone').value.trim();
        if (!phone) {
            showFieldError('phone', 'Phone number is required');
            isValid = false;
        } else if (!isValidPhone(phone)) {
            showFieldError('phone', 'Please enter a valid Ethiopian phone number');
            isValid = false;
        }
    }

    const password = document.getElementById('password').value;
    if (!password) {
        showFieldError('password', 'Password is required');
        isValid = false;
    } else {
        const validation = validatePassword(password);
        if (!validation.isValid) {
            showFieldError('password', `Password must have: ${validation.errors.join(', ')}`);
            isValid = false;
        }
    }

    const confirmPassword = document.getElementById('confirmPassword').value;
    if (!confirmPassword) {
        showFieldError('confirmPassword', 'Please confirm your password');
        isValid = false;
    } else if (password !== confirmPassword) {
        showFieldError('confirmPassword', 'Passwords do not match');
        isValid = false;
    }

    const terms = document.getElementById('terms').checked;
    if (!terms) {
        showFieldError('terms', 'You must accept the terms and conditions');
        isValid = false;
    }

    return isValid;
}

function validateSignInForm() {
    let isValid = true;
    clearAllErrors();

    const loginId = document.getElementById('loginId').value.trim();
    const password = document.getElementById('loginPassword').value;

    if (!loginId) {
        showFieldError('loginId', 'Please enter your username, email, or phone number');
        isValid = false;
    } else {
        const isEmail = isValidEmail(loginId);
        const isPhone = isValidPhone(loginId);
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

document.addEventListener('DOMContentLoaded', function() {
    setupRealTimeValidation();
});
