# LFLshop JavaScript Documentation

## Overview
LFLshop uses a modular JavaScript architecture with centralized configuration and dependency management.

## Core Modules

### Configuration (`config.js`)
Central configuration for API endpoints, application settings, and utilities.

```javascript
// Access configuration
console.log(LFLConfig.API_BASE_URL);
console.log(LFLConfig.APP.CURRENCY);

// Make API calls
const data = await ApiHelper.get('/products.php', { action: 'list' });

// Format currency
const formatted = CurrencyHelper.format(123.45); // "ETB 123.45"
```

#### LFLConfig Object
```javascript
{
    BASE_URL: "http://localhost/LFLshop",
    API_BASE_URL: "http://localhost/LFLshop/api",
    API: {
        AUTH: '/auth.php',
        PRODUCTS: '/products.php',
        CART: '/cart.php',
        ORDERS: '/orders.php',
        PAYMENT: '/payment.php'
    },
    APP: {
        NAME: 'LFLshop',
        CURRENCY: 'ETB',
        CURRENCY_SYMBOL: 'ETB'
    }
}
```

#### ApiHelper Methods
- `get(endpoint, params)` - GET request
- `post(endpoint, data)` - POST request
- `put(endpoint, data)` - PUT request
- `delete(endpoint)` - DELETE request
- `getApiUrl(endpoint, params)` - Build API URL

#### CurrencyHelper Methods
- `format(amount, showSymbol)` - Format currency
- `formatCompact(amount)` - Compact format (1.2K, 1.5M)
- `formatETB(amount, options)` - Ethiopian Birr specific
- `calculateDiscount(original, sale)` - Calculate discount %
- `formatRange(min, max)` - Format price range

### Authentication (`auth.js`)
Handles user authentication and session management.

```javascript
const authManager = new AuthManager();

// Check authentication status
await authManager.checkAuthStatus();

// Handle login
await authManager.handleSignIn(event);

// Handle registration
await authManager.handleSignUp(event);

// Logout
await authManager.logout();
```

### Cart Management (`cart.js`)
Shopping cart functionality.

```javascript
const cartManager = new CartManager();

// Load cart
await cartManager.loadCart();

// Add to cart
await cartManager.addToCart(product, quantity, size);

// Update quantity
await cartManager.updateQuantity(itemId, newQuantity);

// Remove item
await cartManager.removeFromCart(itemId);

// Clear cart
await cartManager.clearCart();
```

### Form Validation (`form-validator.js`)
Comprehensive form validation with Ethiopian-specific rules.

```javascript
// Automatic validation for forms with data-validate attribute
<form data-validate>
    <input data-rules="required|email" />
    <input data-rules="required|phone" />
    <input data-rules="required|strongPassword" />
</form>

// Manual validation
const validator = new FormValidator();
const isValid = validator.validateForm(formElement);

// Custom rules
validator.addRule('customRule', (value) => {
    return value.length > 5;
}, 'Must be longer than 5 characters');
```

#### Validation Rules
- `required` - Field is required
- `email` - Valid email format
- `phone` - Ethiopian phone number (+251 or 09/07)
- `password` - Minimum 8 characters
- `strongPassword` - Complex password requirements
- `minLength:n` - Minimum length
- `maxLength:n` - Maximum length
- `numeric` - Numbers only
- `decimal` - Valid decimal number
- `ethiopianName` - Valid Ethiopian name
- `url` - Valid URL

### Dependency Management (`dependency-loader.js`)
Manages JavaScript file loading order and dependencies.

```javascript
// Load specific scripts
await DependencyLoader.loadScript('auth.js');

// Load multiple scripts
await DependencyLoader.loadScripts(['config.js', 'auth.js']);

// Wait for global variables
const config = await DependencyLoader.waitForGlobal('LFLConfig');

// Check if globals are available
const hasGlobals = DependencyLoader.checkGlobals();
```

## Page-Specific Modules

### Collections (`collections.js`)
Product listing and filtering functionality.

```javascript
// Load products from API
await loadProductsFromAPI();

// Apply filters
applyFilters();

// Search products
searchProducts(query);

// Sort products
sortProducts('price_asc');
```

### Navigation (`navigation.js`)
Site navigation and menu management.

```javascript
// Update navigation based on auth state
updateNavigationForUser(user);

// Handle mobile menu
toggleMobileMenu();

// Update cart count
updateCartCount();
```

## Ethiopian-Specific Features

### Phone Number Validation
```javascript
// Validate Ethiopian phone numbers
FormValidator.validateEthiopianPhone('+251911234567'); // true
FormValidator.validateEthiopianPhone('0911234567'); // true

// Format phone numbers
FormValidator.formatEthiopianPhone('251911234567'); // '+251 91 123 4567'
```

### Currency Formatting
```javascript
// Standard formatting
CurrencyHelper.format(1234.56); // "ETB 1,234.56"

// Compact formatting
CurrencyHelper.formatCompact(1500000); // "ETB 1.5M"

// Custom options
CurrencyHelper.formatETB(123.45, {
    showDecimals: false,
    compact: true
}); // "ETB 123"
```

### Name Validation
```javascript
// Supports English and Amharic characters
FormValidator.validateEthiopianName('አበበ ከበደ'); // true
FormValidator.validateEthiopianName('Abebe Kebede'); // true
```

## Error Handling

### Global Error Handler
```javascript
// Automatic error handling for API calls
try {
    const data = await ApiHelper.get('/products.php');
} catch (error) {
    // Error is automatically logged and displayed
    console.error('API call failed:', error);
}
```

### Form Error Display
```javascript
// Automatic error display for validation
const validator = new FormValidator();
validator.showFormError(form, 'Please correct the errors below');
validator.showFormSuccess(form, 'Form submitted successfully');
```

## Best Practices

### 1. Always Load Config First
```html
<script src="../javascript/config.js"></script>
<script src="../javascript/other-modules.js"></script>
```

### 2. Use Dependency Loader for Complex Dependencies
```javascript
await DependencyLoader.initialize(['config.js', 'auth.js']);
```

### 3. Handle Errors Gracefully
```javascript
try {
    await ApiHelper.post('/orders.php', orderData);
} catch (error) {
    // Show user-friendly error message
    showNotification('Order failed. Please try again.', 'error');
}
```

### 4. Validate Forms Client-Side
```html
<form data-validate>
    <input data-rules="required|email" name="email" />
    <input data-rules="required|phone" name="phone" />
</form>
```

### 5. Use Currency Helper for All Price Display
```javascript
// Don't do this
element.textContent = price + ' ETB';

// Do this
element.textContent = CurrencyHelper.format(price);
```

## Browser Support
- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

## Performance Considerations
- Scripts are loaded asynchronously where possible
- Dependency loader prevents duplicate loading
- API responses are cached where appropriate
- Form validation is debounced to prevent excessive calls
