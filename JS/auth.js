
let currentPage = '';

document.addEventListener('DOMContentLoaded', function() {
    currentPage = window.location.pathname.includes('signin') ? 'signin' : 'signup';

    if (currentPage === 'signup') {
        initializeSignUp();
    } else if (currentPage === 'signin') {
        initializeSignIn();
    }

    initializeCommon();
});

function initializeSignUp() {
    setupUserTypeSelection();
    setupContactMethodTabs();
    setupSignUpForm();
}

function initializeSignIn() {
    setupSignInForm();
}

function initializeCommon() {
    setupPasswordToggles();
    setupSocialLogin();
}

function setupUserTypeSelection() {
    const userTypeOptions = document.querySelectorAll('.user-type-option');

    userTypeOptions.forEach(option => {
        option.addEventListener('click', function() {
            userTypeOptions.forEach(opt => opt.classList.remove('active'));

            this.classList.add('active');

            const radio = this.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;
            }
        });
    });
}

function setupContactMethodTabs() {
    const methodTabs = document.querySelectorAll('.method-tab');
    const emailInput = document.getElementById('email-input');
    const phoneInput = document.getElementById('phone-input');

    methodTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            methodTabs.forEach(t => t.classList.remove('active'));

            this.classList.add('active');

            const method = this.getAttribute('data-method');

            if (method === 'email') {
                emailInput.classList.remove('hidden');
                phoneInput.classList.add('hidden');

                document.getElementById('email').required = true;
                document.getElementById('phone').required = false;
            } else {
                emailInput.classList.add('hidden');
                phoneInput.classList.remove('hidden');

                document.getElementById('email').required = false;
                document.getElementById('phone').required = true;
            }
        });
    });
}

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

function submitSignUpForm() {
    const submitBtn = document.getElementById('signup-btn');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoader = submitBtn.querySelector('.btn-loader');

    btnText.style.display = 'none';
    btnLoader.style.display = 'block';
    submitBtn.disabled = true;

    const formData = collectSignUpData();
    fetch('php/auth.php?action=signup', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        // Reset button state
        btnText.style.display = 'block';
        btnLoader.style.display = 'none';
        submitBtn.disabled = false;
        
        if (data.success) {
            showNotification('Account created successfully! Welcome to LFLshop!', 'success');
            
            setTimeout(() => {
                if (data.data.user.userType === 'Shop & Sell') {
                    window.location.href = 'index.html?welcome=seller';
                } else {
                    window.location.href = 'index.html?welcome=shopper';
                }
            }, 1500);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        btnText.style.display = 'block';
        btnLoader.style.display = 'none';
        submitBtn.disabled = false;

        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    });
}

function submitSignInForm() {
    const submitBtn = document.getElementById('signin-btn');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoader = submitBtn.querySelector('.btn-loader');

    btnText.style.display = 'none';
    btnLoader.style.display = 'block';
    submitBtn.disabled = true;

    const formData = collectSignInData();
    fetch('php/auth.php?action=signin', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        btnText.style.display = 'block';
        btnLoader.style.display = 'none';
        submitBtn.disabled = false;

        if (data.success) {
            showNotification(`Welcome back, ${data.data.user.fullName}!`, 'success');

            setTimeout(() => {
                if (data.data.user.userType === 'Shop & Sell') {
                    window.location.href = 'index.html?dashboard=seller';
                } else {
                    window.location.href = 'index.html?dashboard=shopper';
                }
            }, 1500);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        btnText.style.display = 'block';
        btnLoader.style.display = 'none';
        submitBtn.disabled = false;

        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    });
}

function collectSignUpData() {
    const userType = document.querySelector('input[name="userType"]:checked').value;
    const activeMethod = document.querySelector('.method-tab.active').getAttribute('data-method');
    
    const data = {
        userType: userType,
        firstName: document.getElementById('firstName').value.trim(),
        lastName: document.getElementById('lastName').value.trim(),
        contactMethod: activeMethod,
        password: document.getElementById('password').value,
        termsAccepted: document.getElementById('terms').checked,
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

function collectSignInData() {
    return {
        loginId: document.getElementById('loginId').value.trim(),
        password: document.getElementById('loginPassword').value,
        rememberMe: document.getElementById('rememberMe').checked,
        timestamp: new Date().toISOString()
    };
}

function setupSocialLogin() {
    const googleBtn = document.querySelector('.google-btn');
    const facebookBtn = document.querySelector('.facebook-btn');
    
    if (googleBtn) {
        googleBtn.addEventListener('click', function() {
            showNotification('Google login will be implemented in the full version', 'info');
        });
    }
    
    if (facebookBtn) {
        facebookBtn.addEventListener('click', function() {
            showNotification('Facebook login will be implemented in the full version', 'info');
        });
    }
}

function showNotification(message, type = 'info') {
    const container = document.getElementById('notification-container') || createNotificationContainer();
    
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.style.cssText = `
        display: flex;
        align-items: center;
        gap: var(--space-sm);
        min-width: 300px;
        max-width: 400px;
    `;
    
    const icon = getNotificationIcon(type);
    const iconColor = getNotificationColor(type);
    
    notification.innerHTML = `
        <i class="${icon}" style="color: ${iconColor}; font-size: 1.2rem;"></i>
        <span style="flex: 1; color: var(--text-primary);">${message}</span>
        <button onclick="this.parentElement.remove()" style="background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 0; font-size: 1.2rem;">×</button>
    `;
    
    container.appendChild(notification);

    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

function createNotificationContainer() {
    const container = document.createElement('div');
    container.id = 'notification-container';
    document.body.appendChild(container);
    return container;
}

function getNotificationIcon(type) {
    const icons = {
        success: 'fas fa-check-circle',
        error: 'fas fa-exclamation-circle',
        warning: 'fas fa-exclamation-triangle',
        info: 'fas fa-info-circle'
    };
    return icons[type] || icons.info;
}

function getNotificationColor(type) {
    const colors = {
        success: 'var(--color-success)',
        error: 'var(--color-error)',
        warning: 'var(--color-warning)',
        info: 'var(--color-primary)'
    };
    return colors[type] || colors.info;
}

