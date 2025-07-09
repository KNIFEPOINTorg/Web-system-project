/**
 * Authentication functionality for LFLshop
 * Handles user login, registration, session management, and authentication state
 *
 * @class AuthManager
 * @version 1.0.0
 * @author LFLshop Development Team
 */

class AuthManager {
    /**
     * Initialize the authentication manager
     * Sets up event listeners and checks current authentication status
     */
    constructor() {
        /** @type {Object|null} Current authenticated user data */
        this.currentUser = null;

        /** @type {string} Base URL for authentication API endpoints */
        this.apiBaseUrl = LFLConfig?.API_BASE_URL || '../api';

        this.init();
    }

    /**
     * Initialize authentication system
     * Checks current auth status and sets up event listeners
     * @private
     */
    init() {
        this.checkAuthStatus();
        this.setupEventListeners();
    }

    async checkAuthStatus() {
        try {
            const response = await fetch(ApiHelper.getApiUrl(LFLConfig.API.AUTH, { action: 'check' }));
            const data = await response.json();

            if (data.success) {
                this.currentUser = data.data.user;
                // Store user data in localStorage for dashboard access
                localStorage.setItem('currentUser', JSON.stringify(data.data.user));
            } else {
                this.currentUser = null;
                // Clear localStorage if not authenticated
                localStorage.removeItem('currentUser');
            }
        } catch (error) {
            this.currentUser = null;
            localStorage.removeItem('currentUser');
        }

        this.updateAuthState();
    }

    getCurrentUser() {
        return this.currentUser;
    }

    setCurrentUser(user) {
        this.currentUser = user;
        if (user) {
            // Store user data in localStorage for dashboard access
            localStorage.setItem('currentUser', JSON.stringify(user));
        } else {
            // Clear localStorage if user is null
            localStorage.removeItem('currentUser');
        }
        this.updateAuthState();
    }

    async logout() {
        try {
            const response = await fetch(ApiHelper.getApiUrl(LFLConfig.API.AUTH, { action: 'logout' }), {
                method: 'POST'
            });

            this.currentUser = null;
            // Clear localStorage on logout
            localStorage.removeItem('currentUser');
            this.updateAuthState();

            if (window.location.pathname.includes('dashboard')) {
                window.location.href = 'index.html';
            }
        } catch (error) {
            this.currentUser = null;
            localStorage.removeItem('currentUser');
            this.updateAuthState();
        }
    }

    updateAuthState() {
        const authLinks = document.querySelector('.auth-links');
        const navIcons = document.querySelector('.nav-icons');
        const userMenu = document.querySelector('.user-menu');
        
        if (this.currentUser) {
            // User is authenticated - show user menu and nav icons, hide auth links
            if (authLinks) authLinks.style.display = 'none';
            if (navIcons) navIcons.style.display = 'flex';
            if (userMenu) {
                userMenu.style.display = 'flex';
                const userNameSpan = userMenu.querySelector('span');
                if (userNameSpan) {
                    userNameSpan.textContent = this.currentUser.name || 'User';
                }
            }
        } else {
            // User is not authenticated - show auth links, hide user menu and nav icons
            if (authLinks) {
                authLinks.style.display = 'flex';
                authLinks.innerHTML = `
                    <a href="../login.php">Sign In</a>
                    <span class="auth-divider">|</span>
                    <a href="../register.php">Sign Up</a>
                `;
            }
            if (navIcons) navIcons.style.display = 'none';
            if (userMenu) userMenu.style.display = 'none';
        }
    }

    setupEventListeners() {
        // Handle sign in form
        const signinForm = document.getElementById('signin-form');
        if (signinForm) {
            signinForm.addEventListener('submit', (e) => this.handleSignIn(e));
        }

        // Handle sign up form
        const signupForm = document.getElementById('signup-form');
        if (signupForm) {
            signupForm.addEventListener('submit', (e) => this.handleSignUp(e));
        }
    }

    async handleSignIn(event) {
        event.preventDefault();

        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

        if (!email || !password) {
            this.showError('Please fill in all fields');
            return;
        }

        // Show loading state
        const submitBtn = event.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing In...';
        submitBtn.disabled = true;

        try {
            const response = await fetch(ApiHelper.getApiUrl(LFLConfig.API.AUTH, { action: 'login' }), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email, password })
            });

            const data = await response.json();

            if (data.success) {
                this.setCurrentUser(data.data.user);
                this.showSuccess('Signed in successfully!');

                setTimeout(() => {
                    window.location.href = data.data.redirect;
                }, 1000);
            } else {
                this.showError(data.message);
                // Reset button
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        } catch (error) {
            this.showError('Login failed. Please try again.');
            // Reset button
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    }

    async handleSignUp(event) {
        event.preventDefault();

        const firstName = document.getElementById('first-name').value;
        const lastName = document.getElementById('last-name').value;
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm-password').value;
        const userType = document.querySelector('input[name="user-type"]:checked')?.value || 'customer';
        const terms = document.getElementById('terms').checked;

        if (!firstName || !lastName || !email || !password || !confirmPassword) {
            this.showError('Please fill in all fields');
            return;
        }

        if (password !== confirmPassword) {
            this.showError('Passwords do not match');
            return;
        }

        if (password.length < 6) {
            this.showError('Password must be at least 6 characters');
            return;
        }

        if (!terms) {
            this.showError('Please agree to the terms and conditions');
            return;
        }

        // Show loading state
        const submitBtn = document.getElementById('signup-submit-btn');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
        submitBtn.disabled = true;

        try {
            const response = await fetch(ApiHelper.getApiUrl(LFLConfig.API.AUTH, { action: 'register' }), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    firstName,
                    lastName,
                    email,
                    password,
                    confirmPassword,
                    userType
                })
            });

            const data = await response.json();

            if (data.success) {
                this.setCurrentUser(data.data.user);

                if (userType === 'seller') {
                    this.showSuccess('Seller account created successfully! Redirecting to your seller dashboard...');
                } else {
                    this.showSuccess('Account created successfully! Welcome to LFLshop!');
                }

                setTimeout(() => {
                    window.location.href = data.data.redirect;
                }, 2000);
            } else {
                this.showError(data.message);
                // Reset button
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        } catch (error) {
            this.showError('Registration failed. Please try again.');
            // Reset button
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    }

    getRedirectUrl() {
        const urlParams = new URLSearchParams(window.location.search);
        const redirect = urlParams.get('redirect');
        
        if (redirect) {
            return redirect;
        }
        
        const userType = this.currentUser.userType || this.currentUser.user_type;
        if (userType === 'seller') {
            return 'seller-dashboard.html';
        } else if (userType === 'admin') {
            return 'admin_dashboard.html';
        } else {
            return 'customer-dashboard.html';
        }
    }

    showError(message) {
        // Remove existing messages
        const existingError = document.querySelector('.auth-error');
        if (existingError) {
            existingError.remove();
        }
        
        // Create error element
        const errorDiv = document.createElement('div');
        errorDiv.className = 'auth-error';
        errorDiv.style.cssText = `
            background: #fee;
            color: #c33;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            border: 1px solid #fcc;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        `;
        errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
        
        // Insert error before form
        const form = document.querySelector('form');
        if (form) {
            form.parentNode.insertBefore(errorDiv, form);
        }
    }

    showSuccess(message) {
        // Remove existing messages
        const existingError = document.querySelector('.auth-error');
        if (existingError) {
            existingError.remove();
        }
        
        // Create success element
        const successDiv = document.createElement('div');
        successDiv.className = 'auth-success';
        successDiv.style.cssText = `
            background: #efe;
            color: #363;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            border: 1px solid #cfc;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        `;
        successDiv.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`;
        
        // Insert success before form
        const form = document.querySelector('form');
        if (form) {
            form.parentNode.insertBefore(successDiv, form);
        }
    }

    requireAuth() {
        if (!this.currentUser) {
            window.location.href = 'signin.html?redirect=' + encodeURIComponent(window.location.pathname);
            return false;
        }
        return true;
    }

    requireSellerAuth() {
        if (!this.currentUser || (this.currentUser.userType !== 'seller' && this.currentUser.user_type !== 'seller')) {
            window.location.href = 'signin.html';
            return false;
        }
        return true;
    }
}

// Initialize auth manager
const authManager = new AuthManager();

// Global functions for backward compatibility
function logout() {
    authManager.logout();
}

function updateAuthState() {
    authManager.updateAuthState();
}

function getCurrentUser() {
    return authManager.getCurrentUser();
}