/**
 * Checkout functionality for LFLshop
 * Handles form validation, payment processing, and order completion
 */

class CheckoutManager {
    constructor() {
        this.deliveryFee = 50;
        this.cartItems = [];
        this.init();
    }

    init() {
        this.loadCartItems();
        this.setupEventListeners();
        this.updateDeliveryInfo();
    }

    setupEventListeners() {
        // Delivery option changes
        document.querySelectorAll('input[name="deliveryMethod"]').forEach(radio => {
            radio.addEventListener('change', () => this.updateDeliveryInfo());
        });

        // Delivery time changes
        document.querySelectorAll('input[name="deliveryTime"]').forEach(radio => {
            radio.addEventListener('change', () => this.toggleScheduledTime());
        });

        // City selection
        const citySelect = document.getElementById('city');
        if (citySelect) {
            citySelect.addEventListener('change', () => this.updateDeliveryLocation());
        }

        // Place order button
        const placeOrderBtn = document.getElementById('place-order-btn');
        if (placeOrderBtn) {
            placeOrderBtn.addEventListener('click', () => this.placeOrder());
        }

        // Form validation
        this.setupFormValidation();
    }

    setupFormValidation() {
        const form = document.getElementById('delivery-form');
        if (!form) return;

        const inputs = form.querySelectorAll('input[required], select[required]');
        inputs.forEach(input => {
            input.addEventListener('blur', () => this.validateField(input));
            input.addEventListener('input', () => {
                if (input.classList.contains('error')) {
                    this.validateField(input);
                }
            });
        });
    }

    validateField(field) {
        const value = field.value.trim();
        let isValid = true;
        let errorMessage = '';

        // Remove existing error styling
        field.classList.remove('error');
        this.removeFieldError(field);

        // Required field validation
        if (field.hasAttribute('required') && !value) {
            isValid = false;
            errorMessage = `${this.getFieldLabel(field)} is required`;
        }

        // Email validation
        if (field.type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                isValid = false;
                errorMessage = 'Please enter a valid email address';
            }
        }

        // Phone validation (Ethiopian format)
        if (field.id === 'phone' && value) {
            const phoneRegex = /^[0-9]{9}$/;
            if (!phoneRegex.test(value)) {
                isValid = false;
                errorMessage = 'Please enter a valid Ethiopian phone number';
            }
        }

        // Show error if invalid
        if (!isValid) {
            field.classList.add('error');
            this.showFieldError(field, errorMessage);
        }

        return isValid;
    }

    showFieldError(field, message) {
        const formGroup = field.closest('.form-group');
        let errorElement = formGroup.querySelector('.field-error');

        if (!errorElement) {
            errorElement = document.createElement('span');
            errorElement.className = 'field-error';
            formGroup.appendChild(errorElement);
        }

        errorElement.textContent = message;
    }

    removeFieldError(field) {
        const formGroup = field.closest('.form-group');
        const errorElement = formGroup.querySelector('.field-error');
        if (errorElement) {
            errorElement.remove();
        }
    }

    getFieldLabel(field) {
        const label = field.closest('.form-group').querySelector('label');
        return label ? label.textContent.replace('*', '').trim() : field.name;
    }

    async loadCartItems() {
        try {
            const response = await fetch('../api/cart.php');
            const data = await response.json();

            if (data.success) {
                this.cartItems = data.data.items || [];
                this.updateOrderSummary();
            } else {
                this.showNotification('Failed to load cart items', 'error');
            }
        } catch (error) {
            console.error('Error loading cart:', error);
            this.showNotification('Failed to load cart items', 'error');
        }
    }

    updateOrderSummary() {
        const itemsContainer = document.getElementById('checkout-order-items');
        const itemCountElement = document.getElementById('checkout-item-count');
        const subtotalElement = document.getElementById('checkout-subtotal');
        const totalElement = document.getElementById('order-total');

        if (!itemsContainer) return;

        let subtotal = 0;
        let itemCount = 0;

        if (this.cartItems.length === 0) {
            itemsContainer.innerHTML = '<p>No items in cart</p>';
            itemCountElement.textContent = '0';
            subtotalElement.textContent = '0 ETB';
            totalElement.textContent = `${this.deliveryFee} ETB`;
            return;
        }

        itemsContainer.innerHTML = this.cartItems.map(item => {
            const itemTotal = item.price * item.quantity;
            subtotal += itemTotal;
            itemCount += item.quantity;

            return `
                <div class="order-item">
                    <div class="item-image">
                        <img src="${item.image || 'https://via.placeholder.com/60x60'}" alt="${item.name}">
                    </div>
                    <div class="item-details">
                        <h4>${item.name}</h4>
                        <p>Quantity: ${item.quantity}</p>
                        ${item.size ? `<p>Size: ${item.size}</p>` : ''}
                    </div>
                    <div class="item-price">
                        ${itemTotal.toLocaleString()} ETB
                    </div>
                </div>
            `;
        }).join('');

        const total = subtotal + this.deliveryFee;

        itemCountElement.textContent = itemCount;
        subtotalElement.textContent = `${subtotal.toLocaleString()} ETB`;
        totalElement.textContent = `${total.toLocaleString()} ETB`;
    }

    updateDeliveryInfo() {
        const selectedDelivery = document.querySelector('input[name="deliveryMethod"]:checked');
        if (!selectedDelivery) return;

        const deliveryName = selectedDelivery.closest('.delivery-option').querySelector('h4').textContent;
        const deliveryPrice = selectedDelivery.closest('.delivery-option').querySelector('.price').textContent;

        // Update delivery fee
        this.deliveryFee = parseInt(deliveryPrice.replace(/[^0-9]/g, ''));

        // Update display
        document.getElementById('selected-delivery').textContent = deliveryName;
        document.getElementById('delivery-fee').textContent = `${this.deliveryFee} ETB`;

        // Update delivery time display
        let timeDisplay = 'Standard delivery';
        if (selectedDelivery.value === 'lfl-delivery') {
            timeDisplay = 'Same day delivery (2-6 hours)';
        } else if (selectedDelivery.value === 'partner-delivery') {
            timeDisplay = '2-3 business days';
        }
        document.getElementById('delivery-time-display').textContent = timeDisplay;

        // Update total
        this.updateOrderSummary();
    }

    updateDeliveryLocation() {
        const citySelect = document.getElementById('city');
        if (citySelect && citySelect.value) {
            const cityName = citySelect.options[citySelect.selectedIndex].text;
            document.getElementById('delivery-location').textContent = cityName;
        }
    }

    toggleScheduledTime() {
        const scheduledRadio = document.getElementById('scheduled');
        const scheduledTimeDiv = document.getElementById('scheduled-time');

        if (scheduledRadio && scheduledTimeDiv) {
            scheduledTimeDiv.style.display = scheduledRadio.checked ? 'block' : 'none';
        }
    }

    validateForm() {
        const form = document.getElementById('delivery-form');
        if (!form) return false;

        const requiredFields = form.querySelectorAll('input[required], select[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });

        return isValid;
    }

    async placeOrder() {
        // Validate form
        if (!this.validateForm()) {
            this.showNotification('Please fill in all required fields correctly', 'error');
            return;
        }

        // Check if cart is empty
        if (this.cartItems.length === 0) {
            this.showNotification('Your cart is empty', 'error');
            return;
        }

        const placeOrderBtn = document.getElementById('place-order-btn');
        const originalText = placeOrderBtn.innerHTML;
        placeOrderBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        placeOrderBtn.disabled = true;

        try {
            // Collect form data
            const formData = this.collectFormData();

            // Create order
            const orderResponse = await fetch('../api/orders.php?action=create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            const orderData = await orderResponse.json();

            if (!orderData.success) {
                throw new Error(orderData.message);
            }

            const orderId = orderData.data.order_id;
            const orderNumber = orderData.data.order_number;

            // Process payment
            const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked').value;
            
            if (paymentMethod === 'cash') {
                // Cash on delivery - just confirm order
                window.location.href = `payment_success.html?order=${orderNumber}&amount=${this.getTotalAmount()}`;
            } else {
                // Redirect to payment processing
                this.processPayment(orderId, paymentMethod);
            }

        } catch (error) {
            console.error('Order placement error:', error);
            this.showNotification(error.message || 'Failed to place order', 'error');
        } finally {
            placeOrderBtn.innerHTML = originalText;
            placeOrderBtn.disabled = false;
        }
    }

    collectFormData() {
        const form = document.getElementById('delivery-form');
        const formData = new FormData(form);
        
        const data = {
            customer_info: {
                first_name: formData.get('firstName'),
                last_name: formData.get('lastName'),
                phone: formData.get('phone'),
                email: formData.get('email')
            },
            shipping_address: {
                city: formData.get('city'),
                district: formData.get('district'),
                street_address: formData.get('streetAddress'),
                landmark: formData.get('landmark')
            },
            delivery_info: {
                method: document.querySelector('input[name="deliveryMethod"]:checked').value,
                time_preference: document.querySelector('input[name="deliveryTime"]:checked').value,
                delivery_date: formData.get('deliveryDate'),
                time_slot: formData.get('deliveryTimeSlot'),
                notes: formData.get('deliveryNotes')
            },
            payment_method: document.querySelector('input[name="paymentMethod"]:checked').value,
            delivery_cost: this.deliveryFee
        };

        return data;
    }

    async processPayment(orderId, paymentMethod) {
        // For demo purposes, simulate payment processing
        const paymentData = {
            order_id: orderId,
            payment_method: paymentMethod,
            amount: this.getTotalAmount()
        };

        // Add card details if card payment
        if (paymentMethod === 'card') {
            // This would collect card details from a payment form
            paymentData.card_number = '4111111111111111'; // Demo card
            paymentData.expiry_month = '12';
            paymentData.expiry_year = '2025';
            paymentData.cvv = '123';
            paymentData.cardholder_name = 'Demo User';
        }

        try {
            const response = await fetch('../api/payment.php?action=process', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(paymentData)
            });

            const data = await response.json();

            if (data.success) {
                window.location.href = `payment_success.html?order=${orderId}&amount=${this.getTotalAmount()}`;
            } else {
                window.location.href = `payment_failed.html?reason=${encodeURIComponent(data.message)}&transaction_id=${data.transaction_id || 'N/A'}`;
            }
        } catch (error) {
            console.error('Payment processing error:', error);
            window.location.href = `payment_failed.html?reason=${encodeURIComponent('Payment processing failed')}&transaction_id=N/A`;
        }
    }

    getTotalAmount() {
        const subtotal = this.cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        return subtotal + this.deliveryFee;
    }

    showNotification(message, type = 'info') {
        const notification = document.getElementById('notification');
        const messageElement = notification.querySelector('.notification-message');
        const iconElement = notification.querySelector('.notification-icon');

        messageElement.textContent = message;
        
        // Set icon based on type
        iconElement.className = 'notification-icon fas ' + 
            (type === 'success' ? 'fa-check-circle' : 
             type === 'error' ? 'fa-exclamation-circle' : 
             'fa-info-circle');

        notification.className = `notification ${type}`;
        notification.style.display = 'block';

        // Auto-hide after 5 seconds
        setTimeout(() => {
            notification.style.display = 'none';
        }, 5000);

        // Close button
        notification.querySelector('.notification-close').onclick = () => {
            notification.style.display = 'none';
        };
    }
}

// Initialize checkout manager when page loads
document.addEventListener('DOMContentLoaded', () => {
    new CheckoutManager();
});

// Set minimum date for delivery scheduling
document.addEventListener('DOMContentLoaded', () => {
    const deliveryDateInput = document.getElementById('deliveryDate');
    if (deliveryDateInput) {
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        deliveryDateInput.min = tomorrow.toISOString().split('T')[0];
    }
});