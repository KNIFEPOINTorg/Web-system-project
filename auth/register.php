<?php
/**
 * Registration Page - LFLshop
 * User registration page
 */

require_once 'php/middleware/auth_middleware.php';
requireGuest(); // Redirect if already logged in
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/auth-styles.css">
</head>
<body class="auth-page">
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-left">
                <a href="index.php" class="logo">
                    <img src="images/logo.png" alt="LFLshop" height="40">
                </a>
            </div>
            
            <div class="nav-right">
                <div class="auth-links">
                    <a href="register.php" class="auth-link active">Register</a>
                    <span class="auth-divider">|</span>
                    <a href="login.php" class="auth-link">Login</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="auth-main">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <h1>Create Account</h1>
                    <p>Join LFLshop and support Ethiopian creators</p>
                </div>

                <form id="registerForm" class="auth-form">
                    <?php echo csrfTokenField(); ?>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <div class="input-group">
                                <i class="fas fa-user input-icon"></i>
                                <input type="text" id="first_name" name="first_name" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <div class="input-group">
                                <i class="fas fa-user input-icon"></i>
                                <input type="text" id="last_name" name="last_name" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="username">Username</label>
                        <div class="input-group">
                            <i class="fas fa-at input-icon"></i>
                            <input type="text" id="username" name="username" required>
                        </div>
                        <small class="form-help">Choose a unique username (letters, numbers, and underscores only)</small>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-group">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" id="email" name="email" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number (Optional)</label>
                        <div class="input-group">
                            <i class="fas fa-phone input-icon"></i>
                            <input type="tel" id="phone" name="phone">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="input-group">
                                <i class="fas fa-lock input-icon"></i>
                                <input type="password" id="password" name="password" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <div class="input-group">
                                <i class="fas fa-lock input-icon"></i>
                                <input type="password" id="confirm_password" name="confirm_password" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="password-requirements">
                        <h4>Password Requirements:</h4>
                        <ul>
                            <li id="req-length">At least 8 characters</li>
                            <li id="req-uppercase">One uppercase letter</li>
                            <li id="req-lowercase">One lowercase letter</li>
                            <li id="req-number">One number</li>
                            <li id="req-special">One special character</li>
                        </ul>
                    </div>

                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="terms_accepted" required>
                            <span class="checkmark"></span>
                            I agree to the <a href="terms.php" target="_blank">Terms of Service</a> and <a href="privacy.php" target="_blank">Privacy Policy</a>
                        </label>
                    </div>

                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="newsletter_subscribe">
                            <span class="checkmark"></span>
                            Subscribe to our newsletter for updates and special offers
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-full">
                        <span class="btn-text">Create Account</span>
                        <i class="fas fa-spinner fa-spin btn-loading" style="display: none;"></i>
                    </button>

                    <div class="auth-divider">
                        <span>or</span>
                    </div>

                    <div class="social-login">
                        <button type="button" class="btn btn-outline social-btn">
                            <i class="fab fa-google"></i>
                            Sign up with Google
                        </button>
                        <button type="button" class="btn btn-outline social-btn">
                            <i class="fab fa-facebook"></i>
                            Sign up with Facebook
                        </button>
                    </div>
                </form>

                <div class="auth-footer">
                    <p>Already have an account? <a href="login.php">Sign in here</a></p>
                </div>
            </div>

            <!-- Benefits Section -->
            <div class="auth-features">
                <h2>Join Our Community</h2>
                <div class="features-grid">
                    <div class="feature-item">
                        <i class="fas fa-users"></i>
                        <h3>Community</h3>
                        <p>Connect with Ethiopian creators and fellow shoppers</p>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-gift"></i>
                        <h3>Exclusive Offers</h3>
                        <p>Get access to member-only deals and early product launches</p>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-history"></i>
                        <h3>Order History</h3>
                        <p>Track your purchases and easily reorder your favorites</p>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-headset"></i>
                        <h3>Priority Support</h3>
                        <p>Get faster customer support and personalized assistance</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Alert Container -->
    <div id="alertContainer"></div>

    <script>
        // Registration form handler
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const form = this;
            const submitBtn = form.querySelector('button[type="submit"]');
            const btnText = submitBtn.querySelector('.btn-text');
            const btnLoading = submitBtn.querySelector('.btn-loading');
            
            // Show loading state
            submitBtn.disabled = true;
            btnText.style.display = 'none';
            btnLoading.style.display = 'inline-block';
            
            // Clear previous alerts
            clearAlerts();
            
            // Get form data
            const formData = new FormData(form);
            formData.append('action', 'register');
            
            // Submit form
            fetch('php/handlers/auth_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Registration successful! Redirecting to login...', 'success');
                    setTimeout(() => {
                        window.location.href = data.redirect || 'login.php';
                    }, 2000);
                } else {
                    showAlert(data.errors ? data.errors.join('<br>') : 'Registration failed', 'error');
                }
            })
            .catch(error => {
                console.error('Registration error:', error);
                showAlert('Registration failed. Please try again.', 'error');
            })
            .finally(() => {
                // Reset button state
                submitBtn.disabled = false;
                btnText.style.display = 'inline';
                btnLoading.style.display = 'none';
            });
        });

        // Password validation
        document.getElementById('password').addEventListener('input', function() {
            validatePassword(this.value);
        });

        function validatePassword(password) {
            const requirements = {
                'req-length': password.length >= 8,
                'req-uppercase': /[A-Z]/.test(password),
                'req-lowercase': /[a-z]/.test(password),
                'req-number': /[0-9]/.test(password),
                'req-special': /[^A-Za-z0-9]/.test(password)
            };

            Object.keys(requirements).forEach(reqId => {
                const element = document.getElementById(reqId);
                if (requirements[reqId]) {
                    element.classList.add('valid');
                    element.classList.remove('invalid');
                } else {
                    element.classList.add('invalid');
                    element.classList.remove('valid');
                }
            });
        }

        // Password toggle function
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const toggle = input.parentNode.querySelector('.password-toggle i');
            
            if (input.type === 'password') {
                input.type = 'text';
                toggle.classList.remove('fa-eye');
                toggle.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                toggle.classList.remove('fa-eye-slash');
                toggle.classList.add('fa-eye');
            }
        }

        // Alert functions
        function showAlert(message, type = 'info') {
            const alertContainer = document.getElementById('alertContainer');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;
            alert.innerHTML = `
                <div class="alert-content">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                    <span>${message}</span>
                </div>
                <button class="alert-close" onclick="this.parentNode.remove()">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            alertContainer.appendChild(alert);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.remove();
                }
            }, 5000);
        }

        function clearAlerts() {
            const alertContainer = document.getElementById('alertContainer');
            alertContainer.innerHTML = '';
        }

        // Social login handlers (placeholder)
        document.querySelectorAll('.social-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const provider = this.textContent.includes('Google') ? 'Google' : 'Facebook';
                showAlert(`${provider} registration is coming soon!`, 'info');
            });
        });
    </script>
</body>
</html>
