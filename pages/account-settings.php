<?php
/**
 * Account Settings - LFLshop
 * User profile and account management page
 */

require_once 'php/middleware/auth_middleware.php';
requireAuth();

$user = new User();
$profile = $user->getUserById(getCurrentUserId());

if (!$profile) {
    redirect('dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/auth-navigation.css">
    <link rel="stylesheet" href="css/search-enhanced.css">
    <link rel="stylesheet" href="css/enhanced-search.css">
    <link rel="stylesheet" href="css/settings-styles.css">
    <link rel="stylesheet" href="dashboard/styles.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-left">
                <a href="index.php" class="logo">
                    <img src="images/logo.png" alt="LFLshop" height="40">
                </a>
            </div>
            
            <div class="nav-right">
                <!-- Enhanced Search Container -->
                <div class="search-container enhanced-search">
                    <div class="search-input-wrapper">
                        <input type="text"
                               id="globalSearchInput"
                               placeholder="Search Ethiopian products..."
                               class="search-bar"
                               autocomplete="off">
                        <i class="fas fa-search search-icon"></i>
                        <button class="search-clear" id="searchClear" style="display: none;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <!-- Search Suggestions Dropdown -->
                    <div class="search-suggestions" id="searchSuggestions" style="display: none;">
                        <div class="suggestions-header">
                            <span>Search Suggestions</span>
                        </div>
                        <div class="suggestions-list" id="suggestionsList">
                            <!-- Dynamic suggestions will be inserted here -->
                        </div>
                        <div class="suggestions-footer">
                            <button class="view-all-results" id="viewAllResults">
                                <i class="fas fa-search"></i>
                                View all results
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Navigation Icons (Always visible for authenticated users) -->
                <div class="nav-icons">
                    <a href="cart.php" class="nav-icon" title="Shopping Cart">
                        <i class="fas fa-shopping-cart"></i>
                    </a>

                    <a href="wishlist.php" class="nav-icon" title="Wishlist">
                        <i class="fas fa-heart"></i>
                    </a>

                    <a href="notifications.php" class="nav-icon" title="Notifications">
                        <i class="fas fa-bell"></i>
                    </a>
                </div>
                
                <!-- User Menu -->
                <div class="user-menu">
                    <button class="user-menu-toggle">
                        <span><?php echo h($profile['first_name'] . ' ' . $profile['last_name']); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    
                    <div class="user-dropdown">
                        <a href="dashboard.php" class="dropdown-item">
                            <i class="fas fa-tachometer-alt"></i>
                            Dashboard
                        </a>
                        <a href="account-settings.php" class="dropdown-item active">
                            <i class="fas fa-user-cog"></i>
                            Account Settings
                        </a>
                        <a href="orders.php" class="dropdown-item">
                            <i class="fas fa-shopping-bag"></i>
                            My Orders
                        </a>
                        <?php if (isAdmin()): ?>
                            <div class="dropdown-divider"></div>
                            <a href="admin/dashboard.php" class="dropdown-item">
                                <i class="fas fa-cogs"></i>
                                Admin Panel
                            </a>
                        <?php endif; ?>
                        <div class="dropdown-divider"></div>
                        <button class="dropdown-item logout-btn" onclick="logout()">
                            <i class="fas fa-sign-out-alt"></i>
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="dashboard-main">
        <div class="container">
            <!-- Page Header -->
            <div class="page-header">
                <h1>Account Settings</h1>
                <p class="text-secondary">Manage your profile and account preferences</p>
            </div>

            <!-- Settings Navigation -->
            <div class="settings-nav">
                <button class="settings-tab active" data-tab="profile">
                    <i class="fas fa-user"></i>
                    Profile Information
                </button>
                <button class="settings-tab" data-tab="security">
                    <i class="fas fa-shield-alt"></i>
                    Security
                </button>
                <button class="settings-tab" data-tab="preferences">
                    <i class="fas fa-cog"></i>
                    Preferences
                </button>
                <button class="settings-tab" data-tab="notifications">
                    <i class="fas fa-bell"></i>
                    Notifications
                </button>
            </div>

            <!-- Settings Content -->
            <div class="settings-content">
                <!-- Profile Tab -->
                <div class="settings-panel active" id="profile-panel">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h2>Profile Information</h2>
                            <p class="text-secondary">Update your personal information and profile details</p>
                        </div>
                        <div class="card-body">
                            <!-- Avatar Section -->
                            <div class="avatar-section">
                                <div class="avatar-container">
                                    <?php if ($profile['avatar']): ?>
                                        <img src="<?php echo h($profile['avatar']); ?>" alt="Profile Avatar" class="avatar-image">
                                    <?php else: ?>
                                        <div class="avatar-placeholder">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    <?php endif; ?>
                                    <button class="avatar-upload-btn" onclick="document.getElementById('avatarInput').click()">
                                        <i class="fas fa-camera"></i>
                                    </button>
                                </div>
                                <div class="avatar-info">
                                    <h3><?php echo h($profile['first_name'] . ' ' . $profile['last_name']); ?></h3>
                                    <p class="text-muted">@<?php echo h($profile['username']); ?></p>
                                    <small class="text-muted">Member since <?php echo date('M Y', strtotime($profile['created_at'])); ?></small>
                                </div>
                                <input type="file" id="avatarInput" accept="image/*" style="display: none;" onchange="uploadAvatar(this)">
                            </div>

                            <!-- Profile Form -->
                            <form id="profileForm" class="settings-form">
                                <?php echo csrfTokenField(); ?>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="first_name">First Name</label>
                                        <input type="text" id="first_name" name="first_name" value="<?php echo h($profile['first_name']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="last_name">Last Name</label>
                                        <input type="text" id="last_name" name="last_name" value="<?php echo h($profile['last_name']); ?>" required>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="email">Email Address</label>
                                        <input type="email" id="email" value="<?php echo h($profile['email']); ?>" disabled>
                                        <small class="form-help">Email cannot be changed. Contact support if needed.</small>
                                    </div>
                                    <div class="form-group">
                                        <label for="phone">Phone Number</label>
                                        <input type="tel" id="phone" name="phone" value="<?php echo h($profile['phone']); ?>">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="bio">Bio</label>
                                    <textarea id="bio" name="bio" rows="3" placeholder="Tell us about yourself..."><?php echo h($profile['bio']); ?></textarea>
                                </div>

                                <div class="form-section">
                                    <h3>Address Information</h3>
                                    
                                    <div class="form-group">
                                        <label for="address_line1">Address Line 1</label>
                                        <input type="text" id="address_line1" name="address_line1" value="<?php echo h($profile['address_line1']); ?>">
                                    </div>

                                    <div class="form-group">
                                        <label for="address_line2">Address Line 2 (Optional)</label>
                                        <input type="text" id="address_line2" name="address_line2" value="<?php echo h($profile['address_line2']); ?>">
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="city">City</label>
                                            <input type="text" id="city" name="city" value="<?php echo h($profile['city']); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="state">State/Region</label>
                                            <input type="text" id="state" name="state" value="<?php echo h($profile['state']); ?>">
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="postal_code">Postal Code</label>
                                            <input type="text" id="postal_code" name="postal_code" value="<?php echo h($profile['postal_code']); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="country">Country</label>
                                            <select id="country" name="country">
                                                <option value="Ethiopia" <?php echo $profile['country'] === 'Ethiopia' ? 'selected' : ''; ?>>Ethiopia</option>
                                                <option value="Kenya" <?php echo $profile['country'] === 'Kenya' ? 'selected' : ''; ?>>Kenya</option>
                                                <option value="Uganda" <?php echo $profile['country'] === 'Uganda' ? 'selected' : ''; ?>>Uganda</option>
                                                <option value="Tanzania" <?php echo $profile['country'] === 'Tanzania' ? 'selected' : ''; ?>>Tanzania</option>
                                                <option value="Other" <?php echo !in_array($profile['country'], ['Ethiopia', 'Kenya', 'Uganda', 'Tanzania']) ? 'selected' : ''; ?>>Other</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-section">
                                    <h3>Personal Information</h3>
                                    
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="date_of_birth">Date of Birth</label>
                                            <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo h($profile['date_of_birth']); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="gender">Gender</label>
                                            <select id="gender" name="gender">
                                                <option value="">Prefer not to say</option>
                                                <option value="male" <?php echo $profile['gender'] === 'male' ? 'selected' : ''; ?>>Male</option>
                                                <option value="female" <?php echo $profile['gender'] === 'female' ? 'selected' : ''; ?>>Female</option>
                                                <option value="other" <?php echo $profile['gender'] === 'other' ? 'selected' : ''; ?>>Other</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <button type="button" class="btn btn-secondary" onclick="resetForm()">Reset</button>
                                    <button type="submit" class="btn btn-primary">
                                        <span class="btn-text">Save Changes</span>
                                        <i class="fas fa-spinner fa-spin btn-loading" style="display: none;"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Security Tab -->
                <div class="settings-panel" id="security-panel">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h2>Security Settings</h2>
                            <p class="text-secondary">Manage your password and security preferences</p>
                        </div>
                        <div class="card-body">
                            <!-- Change Password Form -->
                            <form id="passwordForm" class="settings-form">
                                <?php echo csrfTokenField(); ?>
                                
                                <div class="form-group">
                                    <label for="current_password">Current Password</label>
                                    <div class="input-group">
                                        <input type="password" id="current_password" name="current_password" required>
                                        <button type="button" class="password-toggle" onclick="togglePassword('current_password')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="new_password">New Password</label>
                                    <div class="input-group">
                                        <input type="password" id="new_password" name="new_password" required>
                                        <button type="button" class="password-toggle" onclick="togglePassword('new_password')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="confirm_password">Confirm New Password</label>
                                    <div class="input-group">
                                        <input type="password" id="confirm_password" name="confirm_password" required>
                                        <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="password-requirements">
                                    <h4>Password Requirements:</h4>
                                    <ul>
                                        <li>At least 8 characters</li>
                                        <li>One uppercase letter</li>
                                        <li>One lowercase letter</li>
                                        <li>One number</li>
                                        <li>One special character</li>
                                    </ul>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">
                                        <span class="btn-text">Change Password</span>
                                        <i class="fas fa-spinner fa-spin btn-loading" style="display: none;"></i>
                                    </button>
                                </div>
                            </form>

                            <!-- Security Information -->
                            <div class="security-info">
                                <h3>Account Security</h3>
                                <div class="security-item">
                                    <div class="security-icon">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="security-content">
                                        <h4>Last Login</h4>
                                        <p><?php echo $profile['last_login'] ? date('M j, Y \a\t g:i A', strtotime($profile['last_login'])) : 'Never'; ?></p>
                                    </div>
                                </div>
                                <div class="security-item">
                                    <div class="security-icon">
                                        <i class="fas fa-calendar"></i>
                                    </div>
                                    <div class="security-content">
                                        <h4>Account Created</h4>
                                        <p><?php echo date('M j, Y', strtotime($profile['created_at'])); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Preferences Tab -->
                <div class="settings-panel" id="preferences-panel">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h2>Preferences</h2>
                            <p class="text-secondary">Customize your shopping experience</p>
                        </div>
                        <div class="card-body">
                            <form id="preferencesForm" class="settings-form">
                                <?php echo csrfTokenField(); ?>
                                
                                <div class="form-section">
                                    <h3>Shopping Preferences</h3>
                                    
                                    <div class="form-group">
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="email_marketing" checked>
                                            <span class="checkmark"></span>
                                            Receive marketing emails about new products and offers
                                        </label>
                                    </div>

                                    <div class="form-group">
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="order_updates" checked>
                                            <span class="checkmark"></span>
                                            Receive email updates about order status
                                        </label>
                                    </div>

                                    <div class="form-group">
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="wishlist_notifications">
                                            <span class="checkmark"></span>
                                            Notify me when wishlist items go on sale
                                        </label>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">Save Preferences</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Notifications Tab -->
                <div class="settings-panel" id="notifications-panel">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h2>Notification Settings</h2>
                            <p class="text-secondary">Choose how you want to be notified</p>
                        </div>
                        <div class="card-body">
                            <form id="notificationsForm" class="settings-form">
                                <?php echo csrfTokenField(); ?>
                                
                                <div class="notification-group">
                                    <h3>Email Notifications</h3>
                                    
                                    <div class="form-group">
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="email_orders" checked>
                                            <span class="checkmark"></span>
                                            Order confirmations and updates
                                        </label>
                                    </div>

                                    <div class="form-group">
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="email_promotions">
                                            <span class="checkmark"></span>
                                            Promotional offers and discounts
                                        </label>
                                    </div>

                                    <div class="form-group">
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="email_newsletter">
                                            <span class="checkmark"></span>
                                            Weekly newsletter
                                        </label>
                                    </div>
                                </div>

                                <div class="notification-group">
                                    <h3>Push Notifications</h3>
                                    
                                    <div class="form-group">
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="push_orders" checked>
                                            <span class="checkmark"></span>
                                            Order status updates
                                        </label>
                                    </div>

                                    <div class="form-group">
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="push_deals">
                                            <span class="checkmark"></span>
                                            Flash sales and limited-time offers
                                        </label>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">Save Notification Settings</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Alert Container -->
    <div id="alertContainer"></div>

    <!-- Scripts -->
    <script src="javascript/auth-state-manager.js"></script>
    <script src="javascript/enhanced-search.js"></script>
    <script src="javascript/auth-aware-navigation.js"></script>
    <script src="javascript/search-functionality.js"></script>
    <script>
        // Tab switching
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.settings-tab');
            const panels = document.querySelectorAll('.settings-panel');

            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const targetTab = this.dataset.tab;
                    
                    // Remove active class from all tabs and panels
                    tabs.forEach(t => t.classList.remove('active'));
                    panels.forEach(p => p.classList.remove('active'));
                    
                    // Add active class to clicked tab and corresponding panel
                    this.classList.add('active');
                    document.getElementById(targetTab + '-panel').classList.add('active');
                });
            });
        });

        // Profile form handler
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            e.preventDefault();
            submitForm(this, 'php/handlers/profile_handler.php', 'update_profile');
        });

        // Password form handler
        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            e.preventDefault();
            submitForm(this, 'php/handlers/profile_handler.php', 'change_password');
        });

        // Generic form submission
        function submitForm(form, url, action) {
            const submitBtn = form.querySelector('button[type="submit"]');
            const btnText = submitBtn.querySelector('.btn-text');
            const btnLoading = submitBtn.querySelector('.btn-loading');
            
            // Show loading state
            submitBtn.disabled = true;
            if (btnText) btnText.style.display = 'none';
            if (btnLoading) btnLoading.style.display = 'inline-block';
            
            // Clear previous alerts
            clearAlerts();
            
            // Get form data
            const formData = new FormData(form);
            formData.append('action', action);
            
            // Submit form
            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message || 'Changes saved successfully!', 'success');
                    if (action === 'change_password') {
                        form.reset();
                    }
                } else {
                    showAlert(data.errors ? data.errors.join('<br>') : 'Operation failed', 'error');
                }
            })
            .catch(error => {
                console.error('Form submission error:', error);
                showAlert('Operation failed. Please try again.', 'error');
            })
            .finally(() => {
                // Reset button state
                submitBtn.disabled = false;
                if (btnText) btnText.style.display = 'inline';
                if (btnLoading) btnLoading.style.display = 'none';
            });
        }

        // Avatar upload
        function uploadAvatar(input) {
            if (input.files && input.files[0]) {
                const formData = new FormData();
                formData.append('avatar', input.files[0]);
                formData.append('action', 'upload_avatar');
                formData.append('csrf_token', '<?php echo generateCSRFToken(); ?>');

                fetch('php/handlers/profile_handler.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('Avatar uploaded successfully!', 'success');
                        // Update avatar image
                        const avatarContainer = document.querySelector('.avatar-container');
                        const existingImg = avatarContainer.querySelector('.avatar-image');
                        const placeholder = avatarContainer.querySelector('.avatar-placeholder');
                        
                        if (existingImg) {
                            existingImg.src = data.avatar_url;
                        } else if (placeholder) {
                            placeholder.outerHTML = `<img src="${data.avatar_url}" alt="Profile Avatar" class="avatar-image">`;
                        }
                    } else {
                        showAlert(data.errors ? data.errors.join('<br>') : 'Avatar upload failed', 'error');
                    }
                })
                .catch(error => {
                    console.error('Avatar upload error:', error);
                    showAlert('Avatar upload failed. Please try again.', 'error');
                });
            }
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

        // Reset form
        function resetForm() {
            document.getElementById('profileForm').reset();
        }

        // Logout function
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                fetch('php/handlers/auth_handler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=logout'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = data.redirect || 'index.php';
                    }
                })
                .catch(error => {
                    console.error('Logout error:', error);
                    window.location.href = 'index.php';
                });
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

        // Initialize user menu dropdown
        document.addEventListener('DOMContentLoaded', function() {
            const userMenuToggle = document.querySelector('.user-menu-toggle');
            const userDropdown = document.querySelector('.user-dropdown');

            if (userMenuToggle && userDropdown) {
                userMenuToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    userDropdown.style.opacity = userDropdown.style.opacity === '1' ? '0' : '1';
                    userDropdown.style.visibility = userDropdown.style.visibility === 'visible' ? 'hidden' : 'visible';
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!userMenuToggle.contains(e.target) && !userDropdown.contains(e.target)) {
                        userDropdown.style.opacity = '0';
                        userDropdown.style.visibility = 'hidden';
                    }
                });
            }
        });
    </script>
</body>
</html>
