
let currentUser = null;
let isAuthenticated = false;

document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

function initializeApp() {
    checkAuthenticationStatus();
    setupNavigation();
    setupMobileMenu();
    setupScrollEffects();

    const currentPage = getCurrentPage();

    switch (currentPage) {
        case 'index':
            initializeHomePage();
            break;
        case 'collections':
            initializeCollectionsPage();
            break;
        case 'about':
            initializeAboutPage();
            break;
    }
}

function getCurrentPage() {
    const path = window.location.pathname;
    
    if (path.includes('collections')) return 'collections';
    if (path.includes('about')) return 'about';
    if (path.includes('signin')) return 'signin';
    if (path.includes('signup')) return 'signup';
    
    return 'index';
}

function checkAuthenticationStatus() {
    fetch('php/auth.php?action=check')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.authenticated) {
                currentUser = data.data.user;
                isAuthenticated = true;
                updateUIForAuthenticatedUser();
            } else {
                isAuthenticated = false;
                updateUIForGuestUser();
            }
        })
        .catch(error => {
            console.error('Auth check error:', error);
            isAuthenticated = false;
            updateUIForGuestUser();
        });
}

function updateUIForAuthenticatedUser() {
    const navActions = document.querySelector('.nav-actions');
    if (navActions && currentUser) {
        navActions.innerHTML = `
            <div class="user-menu">
                <span class="user-greeting">Welcome, ${currentUser.firstName}</span>
                <button class="btn btn-outline" onclick="logout()">Logout</button>
            </div>
        `;
    }

    updateHeroActions();
}

function updateUIForGuestUser() {
    const navActions = document.querySelector('.nav-actions');
    if (navActions) {
        navActions.innerHTML = `
            <a href="signin.html" class="btn btn-outline">Sign In</a>
            <a href="signup.html" class="btn btn-primary">Sign Up</a>
        `;
    }

    updateHeroActions();
}

function updateHeroActions() {
    const heroActions = document.getElementById('hero-actions');
    if (!heroActions) return;
    
    if (isAuthenticated && currentUser) {
        if (currentUser.userType === 'Shop & Sell') {
            heroActions.innerHTML = `
                <a href="collections.html" class="btn btn-primary">
                    <i class="fas fa-store"></i>
                    Manage Your Store
                </a>
                <a href="collections.html" class="btn btn-outline">
                    <i class="fas fa-shopping-bag"></i>
                    Browse Products
                </a>
            `;
        } else {
            heroActions.innerHTML = `
                <a href="collections.html" class="btn btn-primary">
                    <i class="fas fa-shopping-bag"></i>
                    Start Shopping
                </a>
                <a href="about.html" class="btn btn-outline">
                    <i class="fas fa-heart"></i>
                    Learn Our Story
                </a>
            `;
        }
    } else {
        heroActions.innerHTML = `
            <a href="collections.html" class="btn btn-primary">
                <i class="fas fa-shopping-bag"></i>
                Explore Products
            </a>
            <a href="signup.html" class="btn btn-outline">
                <i class="fas fa-user-plus"></i>
                Join LFLshop
            </a>
        `;
    }
}

function logout() {
    fetch('php/auth.php?action=logout', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            currentUser = null;
            isAuthenticated = false;
            updateUIForGuestUser();
            showNotification('Logged out successfully', 'success');

            setTimeout(() => {
                window.location.href = 'index.html';
            }, 1000);
        }
    })
    .catch(error => {
        console.error('Logout error:', error);
        showNotification('Error logging out', 'error');
    });
}

function setupNavigation() {
    const navLinks = document.querySelectorAll('.nav-links a');
    const currentPath = window.location.pathname;
    
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (currentPath.includes(href.replace('.html', '')) || 
            (href === 'index.html' && currentPath === '/')) {
            link.classList.add('active');
        }
    });
}

function setupMobileMenu() {
}

function setupScrollEffects() {
    const navbar = document.querySelector('.navbar');
    
    if (navbar) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(250, 248, 245, 0.95)';
                navbar.style.backdropFilter = 'blur(10px)';
            } else {
                navbar.style.background = 'var(--bg-primary)';
                navbar.style.backdropFilter = 'blur(8px)';
            }
        });
    }
}

function initializeHomePage() {
    setupScrollIndicator();
    handleWelcomeMessages();
}





function setupScrollIndicator() {
    const scrollIndicator = document.querySelector('.scroll-indicator');
    
    if (scrollIndicator) {
        scrollIndicator.addEventListener('click', function() {
            const featuredSection = document.querySelector('.featured-categories');
            if (featuredSection) {
                featuredSection.scrollIntoView({ behavior: 'smooth' });
            }
        });
    }
}

function handleWelcomeMessages() {
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.get('welcome') === 'seller') {
        showNotification('Welcome to LFLshop! You can now start selling your products.', 'success');
    } else if (urlParams.get('welcome') === 'shopper') {
        showNotification('Welcome to LFLshop! Start exploring authentic Ethiopian products.', 'success');
    }
    
    if (urlParams.get('dashboard') === 'seller') {
        showNotification('Welcome back! Manage your store and products.', 'success');
    } else if (urlParams.get('dashboard') === 'shopper') {
        showNotification('Welcome back! Continue exploring our marketplace.', 'success');
    }
}

function initializeCollectionsPage() {
    showNotification('Collections page functionality will be implemented in the full version', 'info');
}

function initializeAboutPage() {
}

function formatPrice(price, currency = 'ETB') {
    return `${price.toLocaleString()} ${currency}`;
}

function formatDate(date) {
    return new Date(date).toLocaleDateString('en-ET', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}
window.logout = logout;
