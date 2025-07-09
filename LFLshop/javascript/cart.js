/**
 * Shopping cart functionality for LFLshop
 * Manages cart operations including add, remove, update, and persistence
 *
 * @class CartManager
 * @version 1.0.0
 * @author LFLshop Development Team
 */

class CartManager {
    /**
     * Initialize the cart manager
     * Sets up cart state and loads existing cart data
     */
    constructor() {
        /** @type {Array} Array of cart items */
        this.cart = [];

        /** @type {string} API endpoint for cart operations */
        this.apiEndpoint = LFLConfig?.API.CART || '/cart.php';

        /** @type {number} Maximum quantity allowed per item */
        this.maxQuantity = 99;

        this.init();
    }

    /**
     * Initialize cart system
     * Loads existing cart and sets up event listeners
     * @private
     */
    init() {
        this.loadCart();
        this.setupEventListeners();
    }

    async loadCart() {
        try {
            const response = await fetch(ApiHelper.getApiUrl(LFLConfig.API.CART));
            const data = await response.json();

            if (data.success) {
                this.cart = data.data.items || [];
                this.updateCartCount(data.data.item_count || 0);
            } else {
                this.cart = [];
                this.updateCartCount(0);
            }
        } catch (error) {
            console.error('Error loading cart:', error);
            this.cart = [];
            this.updateCartCount(0);
        }
    }

    async addToCart(product, quantity = 1, size = null) {
        try {
            const response = await fetch(ApiHelper.getApiUrl(LFLConfig.API.CART, { action: 'add' }), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    product_id: product.id,
                    quantity: quantity,
                    size: size
                })
            });

            const data = await response.json();

            if (data.success) {
                await this.loadCart();
                this.showNotification(`${product.title || product.name} added to cart!`, 'success');
                return true;
            } else {
                this.showNotification(data.message, 'error');
                return false;
            }
        } catch (error) {
            console.error('Error adding to cart:', error);
            this.showNotification('Failed to add item to cart', 'error');
            return false;
        }
    }

    async removeFromCart(itemId) {
        try {
            const response = await fetch(ApiHelper.getApiUrl(LFLConfig.API.CART, { item_id: itemId }), {
                method: 'DELETE'
            });

            const data = await response.json();

            if (data.success) {
                await this.loadCart();
                this.showNotification('Item removed from cart', 'info');
            } else {
                this.showNotification(data.message, 'error');
            }
        } catch (error) {
            console.error('Error removing from cart:', error);
            this.showNotification('Failed to remove item', 'error');
        }
    }

    async updateQuantity(itemId, newQuantity) {
        try {
            const response = await fetch(ApiHelper.getApiUrl(LFLConfig.API.CART), {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    item_id: itemId,
                    quantity: newQuantity
                })
            });

            const data = await response.json();

            if (data.success) {
                await this.loadCart();
            } else {
                this.showNotification(data.message, 'error');
            }
        } catch (error) {
            console.error('Error updating quantity:', error);
            this.showNotification('Failed to update quantity', 'error');
        }
    }

    async clearCart() {
        try {
            const response = await fetch(ApiHelper.getApiUrl(LFLConfig.API.CART, { action: 'clear' }), {
                method: 'POST'
            });

            const data = await response.json();

            if (data.success) {
                await this.loadCart();
                this.showNotification('Cart cleared', 'info');
            } else {
                this.showNotification(data.message, 'error');
            }
        } catch (error) {
            console.error('Error clearing cart:', error);
            this.showNotification('Failed to clear cart', 'error');
        }
    }

    getCartTotal() {
        return this.cart.reduce((total, item) => total + (item.current_price * item.quantity), 0);
    }

    getCartItemCount() {
        return this.cart.reduce((total, item) => total + item.quantity, 0);
    }

    updateCartCount(count = null) {
        const cartCountElements = document.querySelectorAll('.cart-count');
        const itemCount = count !== null ? count : this.getCartItemCount();

        cartCountElements.forEach(element => {
            element.textContent = itemCount;

            if (itemCount > 0) {
                element.style.transform = 'scale(1.2)';
                setTimeout(() => {
                    element.style.transform = 'scale(1)';
                }, 200);
            }
        });
    }

    setupEventListeners() {
        // Add to cart buttons
        document.addEventListener('click', (e) => {
            if (e.target.matches('.add-to-cart-btn') || e.target.closest('.add-to-cart-btn')) {
                e.preventDefault();
                const button = e.target.matches('.add-to-cart-btn') ? e.target : e.target.closest('.add-to-cart-btn');
                const productId = parseInt(button.dataset.productId);
                
                if (productId) {
                    this.handleAddToCart(productId);
                }
            }
        });

        // Quick add buttons in product overlays
        document.addEventListener('click', (e) => {
            if (e.target.textContent === 'Add to Cart' && e.target.classList.contains('btn')) {
                e.preventDefault();
                e.stopPropagation();
                
                // Get product data from the card
                const productCard = e.target.closest('.product-card');
                if (productCard) {
                    const product = this.extractProductFromCard(productCard);
                    if (product) {
                        this.addToCart(product);
                    }
                }
            }
        });
    }

    handleAddToCart(productId) {
        // This would normally fetch product data from an API
        // For now, we'll use mock data
        const mockProducts = [
            {
                id: 1,
                title: 'Traditional Habesha Dress',
                price: 2500,
                image: 'https://images.unsplash.com/photo-1544829885-9a56ca0fe9e7?w=400&h=300&fit=crop',
                seller: 'Almaz Textiles'
            },
            {
                id: 2,
                title: 'Yirgacheffe Coffee Beans',
                price: 850,
                image: 'https://images.unsplash.com/photo-1559525839-d9d1e38b0a35?w=400&h=300&fit=crop',
                seller: 'Sidama Coffee'
            },
            {
                id: 3,
                title: 'Authentic Berbere Spice',
                price: 320,
                image: 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?w=400&h=300&fit=crop',
                seller: 'Harar Spices'
            },
            {
                id: 4,
                title: 'Ethiopian Silver Cross',
                price: 1800,
                image: 'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?w=400&h=300&fit=crop',
                seller: 'Lalibela Crafts'
            }
        ];
        
        const product = mockProducts.find(p => p.id === productId);
        if (product) {
            this.addToCart(product);
        }
    }

    extractProductFromCard(productCard) {
        try {
            const title = productCard.querySelector('.product-title')?.textContent;
            const priceText = productCard.querySelector('.current-price')?.textContent;
            const image = productCard.querySelector('.product-image img')?.src;
            const seller = productCard.querySelector('.seller-link')?.textContent;
            
            if (!title || !priceText) return null;
            
            const price = parseInt(priceText.replace(/[^\d]/g, ''));
            const id = Date.now() + Math.random(); // Generate unique ID
            
            return {
                id: id,
                title: title,
                price: price,
                image: image || '',
                seller: seller || 'LFLshop'
            };
        } catch (error) {
            console.error('Error extracting product data:', error);
            return null;
        }
    }

    showNotification(message, type = 'info') {
        // Remove existing notification
        const existingNotification = document.querySelector('.cart-notification');
        if (existingNotification) {
            existingNotification.remove();
        }
        
        // Create notification
        const notification = document.createElement('div');
        notification.className = 'cart-notification';
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#d4edda' : type === 'error' ? '#f8d7da' : '#d1ecf1'};
            color: ${type === 'success' ? '#155724' : type === 'error' ? '#721c24' : '#0c5460'};
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            border: 1px solid ${type === 'success' ? '#c3e6cb' : type === 'error' ? '#f5c6cb' : '#bee5eb'};
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 10000;
            max-width: 300px;
            animation: slideIn 0.3s ease-out;
        `;
        
        notification.innerHTML = `
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" style="background: none; border: none; margin-left: auto; cursor: pointer; font-size: 1.2rem;">&times;</button>
            </div>
        `;
        
        // Add CSS animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
        `;
        document.head.appendChild(style);
        
        document.body.appendChild(notification);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 3000);
    }

    // Mini cart functionality
    showMiniCart() {
        const miniCart = document.createElement('div');
        miniCart.className = 'mini-cart';
        miniCart.style.cssText = `
            position: fixed;
            top: 70px;
            right: 20px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 10000;
            width: 300px;
            max-height: 400px;
            overflow-y: auto;
        `;
        
        const cartItems = this.cart.slice(0, 3); // Show only first 3 items
        const total = this.getCartTotal();
        
        miniCart.innerHTML = `
            <div style="padding: 1rem; border-bottom: 1px solid #eee;">
                <h4 style="margin: 0; display: flex; justify-content: space-between; align-items: center;">
                    Cart (${this.getCartItemCount()})
                    <button onclick="this.closest('.mini-cart').remove()" style="background: none; border: none; cursor: pointer;">&times;</button>
                </h4>
            </div>
            <div style="padding: 1rem;">
                ${cartItems.length === 0 ? 
                    '<p style="text-align: center; color: #666;">Your cart is empty</p>' :
                    cartItems.map(item => `
                        <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem; align-items: center;">
                            <img src="${item.image}" alt="${item.title}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 0.25rem;">
                            <div style="flex: 1; font-size: 0.875rem;">
                                <div style="font-weight: 500;">${item.title}</div>
                                <div style="color: #666;">${item.quantity} × ${item.price} ETB</div>
                            </div>
                        </div>
                    `).join('')
                }
                ${this.cart.length > 3 ? `<p style="text-align: center; color: #666; font-size: 0.875rem;">+${this.cart.length - 3} more items</p>` : ''}
                ${cartItems.length > 0 ? `
                    <div style="border-top: 1px solid #eee; padding-top: 1rem; margin-top: 1rem;">
                        <div style="display: flex; justify-content: space-between; font-weight: 600; margin-bottom: 1rem;">
                            <span>Total:</span>
                            <span>${total} ETB</span>
                        </div>
                        <a href="cart.html" class="btn btn-primary" style="width: 100%; text-align: center; text-decoration: none;">View Cart</a>
                    </div>
                ` : ''}
            </div>
        `;
        
        document.body.appendChild(miniCart);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (miniCart.parentElement) {
                miniCart.remove();
            }
        }, 5000);
    }
}

// Initialize cart manager
const cartManager = new CartManager();

// Global functions for backward compatibility
function addToCart(productId, quantity = 1) {
    cartManager.handleAddToCart(productId);
}

function updateCartCount() {
    cartManager.updateCartCount();
}

function getCartItemCount() {
    return cartManager.getCartItemCount();
}

function getCartTotal() {
    return cartManager.getCartTotal();
}