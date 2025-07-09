/**
 * Form Validation System for LFLshop
 * IMPORTANT: Contains Ethiopian-specific validation rules (phone numbers, names)
 */

class FormValidator {
    constructor() {
        this.rules = {
            required: (value) => value.trim() !== '',
            email: (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value),
            phone: (value) => /^(\+251|0)[79]\d{8}$/.test(value.replace(/\s/g, '')),
            password: (value) => value.length >= 8,
            strongPassword: (value) => {
                return value.length >= 8 &&
                       /[A-Z]/.test(value) &&
                       /[a-z]/.test(value) &&
                       /[0-9]/.test(value) &&
                       /[!@#$%^&*]/.test(value);
            },
            minLength: (value, min) => value.length >= min,
            maxLength: (value, max) => value.length <= max,
            numeric: (value) => /^\d+$/.test(value),
            decimal: (value) => /^\d+(\.\d{1,2})?$/.test(value),
            ethiopianName: (value) => /^[a-zA-Z\s\u1200-\u137F]+$/.test(value),
            url: (value) => {
                try {
                    new URL(value);
                    return true;
                } catch {
                    return false;
                }
            }
        };

        this.messages = {
            required: 'This field is required',
            email: 'Please enter a valid email address',
            phone: 'Please enter a valid Ethiopian phone number (+251 or 09/07)',
            password: 'Password must be at least 8 characters long',
            strongPassword: 'Password must contain uppercase, lowercase, number, and special character',
            minLength: 'Must be at least {min} characters long',
            maxLength: 'Must be no more than {max} characters long',
            numeric: 'Please enter numbers only',
            decimal: 'Please enter a valid price (e.g., 123.45)',
            ethiopianName: 'Please enter a valid name (English or Amharic characters only)',
            url: 'Please enter a valid URL'
        };

        this.init();
    }

    init() {
        // Auto-validate forms on page load
        document.addEventListener('DOMContentLoaded', () => {
            this.attachValidators();
        });
    }

    /**
     * Attach validators to all forms with validation attributes
     */
    attachValidators() {
        const forms = document.querySelectorAll('form[data-validate]');
        forms.forEach(form => {
            this.setupFormValidation(form);
        });

        // Real-time validation for inputs
        const inputs = document.querySelectorAll('input[data-rules], textarea[data-rules], select[data-rules]');
        inputs.forEach(input => {
            this.setupInputValidation(input);
        });
    }

    /**
     * Setup validation for a form
     */
    setupFormValidation(form) {
        form.addEventListener('submit', (e) => {
            if (!this.validateForm(form)) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    }

    /**
     * Setup real-time validation for an input
     */
    setupInputValidation(input) {
        // Validate on blur
        input.addEventListener('blur', () => {
            this.validateField(input);
        });

        // Clear errors on input
        input.addEventListener('input', () => {
            this.clearFieldError(input);
        });
    }

    /**
     * Validate entire form
     */
    validateForm(form) {
        const fields = form.querySelectorAll('input[data-rules], textarea[data-rules], select[data-rules]');
        let isValid = true;

        fields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });

        return isValid;
    }

    /**
     * Validate single field
     */
    validateField(field) {
        const rules = field.dataset.rules ? field.dataset.rules.split('|') : [];
        const value = field.value;
        let isValid = true;

        // Clear previous errors
        this.clearFieldError(field);

        // Apply each rule
        for (const rule of rules) {
            const [ruleName, ...params] = rule.split(':');
            const ruleFunction = this.rules[ruleName];

            if (ruleFunction) {
                const ruleValid = params.length > 0 
                    ? ruleFunction(value, ...params)
                    : ruleFunction(value);

                if (!ruleValid) {
                    this.showFieldError(field, ruleName, params);
                    isValid = false;
                    break; // Stop at first error
                }
            }
        }

        return isValid;
    }

    /**
     * Show field error
     */
    showFieldError(field, ruleName, params = []) {
        let message = this.messages[ruleName] || 'Invalid input';
        
        // Replace placeholders in message
        params.forEach((param, index) => {
            const placeholder = `{${Object.keys(this.messages)[index] || index}}`;
            message = message.replace(placeholder, param);
        });

        // Create or update error element
        let errorElement = field.parentNode.querySelector('.field-error');
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.className = 'field-error';
            field.parentNode.appendChild(errorElement);
        }

        errorElement.textContent = message;
        errorElement.style.cssText = `
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: block;
        `;

        // Add error styling to field
        field.style.borderColor = '#dc3545';
        field.classList.add('is-invalid');
    }

    /**
     * Clear field error
     */
    clearFieldError(field) {
        const errorElement = field.parentNode.querySelector('.field-error');
        if (errorElement) {
            errorElement.remove();
        }

        // Remove error styling
        field.style.borderColor = '';
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
    }

    /**
     * Add custom validation rule
     */
    addRule(name, validator, message) {
        this.rules[name] = validator;
        this.messages[name] = message;
    }

    /**
     * Validate specific data types
     */
    static validateEthiopianPhone(phone) {
        const cleaned = phone.replace(/\s/g, '');
        return /^(\+251|0)[79]\d{8}$/.test(cleaned);
    }

    static validatePrice(price) {
        return /^\d+(\.\d{1,2})?$/.test(price) && parseFloat(price) > 0;
    }

    static validateEthiopianName(name) {
        return /^[a-zA-Z\s\u1200-\u137F]+$/.test(name) && name.trim().length >= 2;
    }

    /**
     * Format Ethiopian phone number
     */
    static formatEthiopianPhone(phone) {
        const cleaned = phone.replace(/\D/g, '');
        if (cleaned.startsWith('251')) {
            return '+' + cleaned.replace(/(\d{3})(\d{2})(\d{3})(\d{4})/, '$1 $2 $3 $4');
        } else if (cleaned.startsWith('0')) {
            return cleaned.replace(/(\d{2})(\d{2})(\d{3})(\d{4})/, '$1 $2 $3 $4');
        }
        return phone;
    }

    /**
     * Show form-level success message
     */
    showFormSuccess(form, message) {
        let successElement = form.querySelector('.form-success');
        if (!successElement) {
            successElement = document.createElement('div');
            successElement.className = 'form-success';
            form.insertBefore(successElement, form.firstChild);
        }

        successElement.textContent = message;
        successElement.style.cssText = `
            background: #d4edda;
            color: #155724;
            padding: 0.75rem;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
            border: 1px solid #c3e6cb;
        `;

        // Auto-hide after 5 seconds
        setTimeout(() => {
            if (successElement.parentNode) {
                successElement.remove();
            }
        }, 5000);
    }

    /**
     * Show form-level error message
     */
    showFormError(form, message) {
        let errorElement = form.querySelector('.form-error');
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.className = 'form-error';
            form.insertBefore(errorElement, form.firstChild);
        }

        errorElement.textContent = message;
        errorElement.style.cssText = `
            background: #f8d7da;
            color: #721c24;
            padding: 0.75rem;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
            border: 1px solid #f5c6cb;
        `;
    }
}

// Initialize global validator
window.FormValidator = new FormValidator();

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = FormValidator;
}
