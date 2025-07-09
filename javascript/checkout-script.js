// Checkout JavaScript

// Delivery pricing
const deliveryPricing = {
    'lfl-delivery': {
        price: 50,
        currency: 'ETB',
        usdEquivalent: 0.90,
        time: 'Same day delivery (2-6 hours)'
    },
    'partner-delivery': {
        price: 35,
        currency: 'ETB',
        usdEquivalent: 0.63,
        time: '1-2 business days'
    }
};

// Order data
let orderData = {
    subtotal: 245.99,
    deliveryMethod: 'lfl-delivery',
    deliveryFee: 50,
    deliveryFeeUSD: 0.90,
    total: 246.89
};

document.addEventListener('DOMContentLoaded', function() {
    // Initialize checkout
    initializeCheckout();
    
    // Setup delivery options
    setupDeliveryOptions();
    
    // Setup time selection
    setupTimeSelection();
    
    // Setup form validation
    setupFormValidation();
    
    // Setup navigation
    setupNavigation();
    
    // Set minimum date for delivery
    setMinimumDeliveryDate();
});

// Initialize checkout
function initializeCheckout() {
    // Update order summary
    updateOrderSummary();
    
    // Set default delivery method
    updateDeliverySelection('lfl-delivery');
}

// Setup delivery options
function setupDeliveryOptions() {
    const deliveryOptions = document.querySelectorAll('.delivery-option');
    const radioButtons = document.querySelectorAll('input[name="deliveryMethod"]');
    
    deliveryOptions.forEach(option => {
        option.addEventListener('click', function() {
            const radioButton = this.querySelector('input[type="radio"]');
            const deliveryMethod = radioButton.value;
            
            // Update radio button
            radioButton.checked = true;
            
            // Update visual selection
            deliveryOptions.forEach(opt => opt.classList.remove('active'));
            this.classList.add('active');
            
            // Update delivery details
            updateDeliverySelection(deliveryMethod);
        });
    });
    
    radioButtons.forEach(radio => {
        radio.addEventListener('change', function() {
            updateDeliverySelection(this.value);
        });
    });
}

// Update delivery selection
function updateDeliverySelection(method) {
    orderData.deliveryMethod = method;
    const pricing = deliveryPricing[method];
    
    // Update order data
    orderData.deliveryFee = pricing.price;
    orderData.deliveryFeeUSD = pricing.usdEquivalent;
    orderData.total = orderData.subtotal + pricing.usdEquivalent;
    
    // Update UI
    updateOrderSummary();
    updateDeliveryDisplay(method);
}

// Update order summary
function updateOrderSummary() {
    const deliveryFeeElement = document.getElementById('delivery-fee');
    const orderTotalElement = document.getElementById('order-total');
    
    const pricing = deliveryPricing[orderData.deliveryMethod];
    
    deliveryFeeElement.textContent = `${pricing.price} ${pricing.currency} (~$${pricing.usdEquivalent.toFixed(2)})`;
    orderTotalElement.textContent = `$${orderData.total.toFixed(2)}`;
}

// Update delivery display in summary
function updateDeliveryDisplay(method) {
    const selectedDeliveryElement = document.getElementById('selected-delivery');
    const deliveryTimeElement = document.getElementById('delivery-time-display');
    
    const deliveryNames = {
        'lfl-delivery': 'LFLshop Fast Delivery',
        'partner-delivery': 'ZayRide Delivery'
    };
    
    selectedDeliveryElement.textContent = deliveryNames[method];
    deliveryTimeElement.textContent = deliveryPricing[method].time;
}

// Setup time selection
function setupTimeSelection() {
    const timeOptions = document.querySelectorAll('input[name="deliveryTime"]');
    const scheduledTimeDiv = document.getElementById('scheduled-time');
    
    timeOptions.forEach(option => {
        option.addEventListener('change', function() {
            if (this.value === 'scheduled') {
                scheduledTimeDiv.style.display = 'block';
                // Make date and time required
                document.getElementById('deliveryDate').required = true;
                document.getElementById('deliveryTimeSlot').required = true;
            } else {
                scheduledTimeDiv.style.display = 'none';
                // Remove required attributes
                document.getElementById('deliveryDate').required = false;
                document.getElementById('deliveryTimeSlot').required = false;
            }
        });
    });
}

// Set minimum delivery date
function setMinimumDeliveryDate() {
    const deliveryDateInput = document.getElementById('deliveryDate');
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    
    const minDate = tomorrow.toISOString().split('T')[0];
    deliveryDateInput.min = minDate;
    deliveryDateInput.value = minDate;
}

// Setup form validation
function setupFormValidation() {
    const form = document.getElementById('delivery-form');
    const requiredFields = form.querySelectorAll('[required]');
    
    // Real-time validation
    requiredFields.forEach(field => {
        field.addEventListener('blur', function() {
            validateField(this);
        });
        
        field.addEventListener('input', function() {
            if (this.classList.contains('error')) {
                validateField(this);
            }
        });
    });
    
    // Phone number formatting
    const phoneInput = document.getElementById('phone');
    phoneInput.addEventListener('input', function() {
        formatPhoneNumber(this);
    });
    
    // Email validation
    const emailInput = document.getElementById('email');
    emailInput.addEventListener('blur', function() {
        validateEmail(this);
    });
}

// Validate individual field
function validateField(field) {
    const value = field.value.trim();
    let isValid = true;
    let errorMessage = '';
    
    // Remove existing error styling
    field.classList.remove('error');
    removeFieldError(field);
    
    // Check if required field is empty
    if (field.required && !value) {
        isValid = false;
        errorMessage = `${getFieldLabel(field)} is required`;
    }
    
    // Specific validations
    switch (field.type) {
        case 'email':
            if (value && !isValidEmail(value)) {
                isValid = false;
                errorMessage = 'Please enter a valid email address';
            }
            break;
        case 'tel':
            if (value && !isValidEthiopianPhone(value)) {
                isValid = false;
                errorMessage = 'Please enter a valid Ethiopian phone number';
            }
            break;
    }
    
    // Show error if invalid
    if (!isValid) {
        field.classList.add('error');
        showFieldError(field, errorMessage);
    }
    
    return isValid;
}

// Validate email
function validateEmail(emailField) {
    const email = emailField.value.trim();
    if (email && !isValidEmail(email)) {
        emailField.classList.add('error');
        showFieldError(emailField, 'Please enter a valid email address');
        return false;
    }
    return true;
}

// Format phone number
function formatPhoneNumber(phoneField) {
    let value = phoneField.value.replace(/\D/g, ''); // Remove non-digits
    
    // Format as Ethiopian phone number (9 12 34 56 78)
    if (value.length > 0) {
        if (value.length <= 1) {
            value = value;
        } else if (value.length <= 3) {
            value = value.substring(0, 1) + ' ' + value.substring(1);
        } else if (value.length <= 5) {
            value = value.substring(0, 1) + ' ' + value.substring(1, 3) + ' ' + value.substring(3);
        } else if (value.length <= 7) {
            value = value.substring(0, 1) + ' ' + value.substring(1, 3) + ' ' + value.substring(3, 5) + ' ' + value.substring(5);
        } else {
            value = value.substring(0, 1) + ' ' + value.substring(1, 3) + ' ' + value.substring(3, 5) + ' ' + value.substring(5, 7) + ' ' + value.substring(7, 9);
        }
    }
    
    phoneField.value = value;
}

// Validation helper functions
function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function isValidEthiopianPhone(phone) {
    // Remove spaces and check if it's a valid Ethiopian mobile number
    const cleanPhone = phone.replace(/\s/g, '');
    return /^9\d{8}$/.test(cleanPhone);
}

function getFieldLabel(field) {
    const label = field.closest('.form-group').querySelector('label');
    return label ? label.textContent.replace('*', '').trim() : 'Field';
}

function showFieldError(field, message) {
    const formGroup = field.closest('.form-group');
    let errorElement = formGroup.querySelector('.field-error');
    
    if (!errorElement) {
        errorElement = document.createElement('span');
        errorElement.className = 'field-error';
        formGroup.appendChild(errorElement);
    }
    
    errorElement.textContent = message;
}

function removeFieldError(field) {
    const formGroup = field.closest('.form-group');
    const errorElement = formGroup.querySelector('.field-error');
    if (errorElement) {
        errorElement.remove();
    }
}

// Setup navigation
function setupNavigation() {
    const backBtn = document.querySelector('.back-btn');
    const continueBtn = document.querySelector('.continue-btn');
    
    backBtn.addEventListener('click', function() {
        // Go back to cart (you would implement this based on your routing)
        window.history.back();
    });
    
    continueBtn.addEventListener('click', function() {
        if (validateCheckoutForm()) {
            proceedToPayment();
        }
    });
}

// Validate entire checkout form
function validateCheckoutForm() {
    const form = document.getElementById('delivery-form');
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    // Validate all required fields
    requiredFields.forEach(field => {
        if (!validateField(field)) {
            isValid = false;
        }
    });
    
    // Check if delivery method is selected
    const deliveryMethod = document.querySelector('input[name="deliveryMethod"]:checked');
    if (!deliveryMethod) {
        showNotification('Please select a delivery method', 'error');
        isValid = false;
    }
    
    // Validate scheduled delivery if selected
    const scheduledDelivery = document.querySelector('input[name="deliveryTime"]:checked');
    if (scheduledDelivery && scheduledDelivery.value === 'scheduled') {
        const deliveryDate = document.getElementById('deliveryDate').value;
        const timeSlot = document.getElementById('deliveryTimeSlot').value;
        
        if (!deliveryDate || !timeSlot) {
            showNotification('Please select delivery date and time slot', 'error');
            isValid = false;
        }
    }
    
    if (!isValid) {
        showNotification('Please fill in all required fields correctly', 'error');
    }
    
    return isValid;
}

// Proceed to payment
function proceedToPayment() {
    // Collect form data
    const formData = collectCheckoutData();
    
    // Store in session storage for next step
    sessionStorage.setItem('checkoutData', JSON.stringify(formData));
    
    // Show loading state
    const continueBtn = document.querySelector('.continue-btn');
    const originalText = continueBtn.innerHTML;
    continueBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    continueBtn.disabled = true;
    
    // Simulate processing
    setTimeout(() => {
        // Redirect to payment page
        window.location.href = 'payment.html';
    }, 1500);
}

// Collect checkout data
function collectCheckoutData() {
    const form = document.getElementById('delivery-form');
    const formData = new FormData(form);
    
    const data = {
        // Contact information
        firstName: formData.get('firstName'),
        lastName: formData.get('lastName'),
        phone: formData.get('phone'),
        email: formData.get('email'),
        
        // Delivery address
        subcity: formData.get('subcity'),
        woreda: formData.get('woreda'),
        streetAddress: formData.get('streetAddress'),
        landmark: formData.get('landmark'),
        deliveryNotes: formData.get('deliveryNotes'),
        
        // Delivery options
        deliveryMethod: document.querySelector('input[name="deliveryMethod"]:checked').value,
        deliveryTime: document.querySelector('input[name="deliveryTime"]:checked').value,
        deliveryDate: formData.get('deliveryDate'),
        deliveryTimeSlot: formData.get('deliveryTimeSlot'),
        
        // Order details
        orderData: orderData,
        timestamp: new Date().toISOString()
    };
    
    return data;
}

// Show notification
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="notification-icon fas fa-${type === 'error' ? 'exclamation-circle' : 'check-circle'}"></i>
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
        padding: 16px 20px;
        border-left: 4px solid ${type === 'error' ? '#dc3545' : '#28a745'};
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Auto hide after 5 seconds
    setTimeout(() => {
        notification.remove();
    }, 5000);
    
    // Close button functionality
    notification.querySelector('.notification-close').addEventListener('click', () => {
        notification.remove();
    });
}
