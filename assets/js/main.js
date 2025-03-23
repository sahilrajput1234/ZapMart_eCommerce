document.addEventListener('DOMContentLoaded', function() {
    // Initialize dropdowns
    initDropdowns();

    // Initialize mega menu
    initMegaMenu();

    // Initialize newsletter popup
    initNewsletterPopup();

    // Initialize cart functionality
    initCart();

    // Initialize product quantity controls
    initQuantityControls();
});

function initDropdowns() {
    const dropdowns = document.querySelectorAll('.dropdown');
    dropdowns.forEach(dropdown => {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        const menu = dropdown.querySelector('.dropdown-menu');

        if (toggle && menu) {
            toggle.addEventListener('click', (e) => {
                e.preventDefault();
                menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (!dropdown.contains(e.target)) {
                    menu.style.display = 'none';
                }
            });
        }
    });
}

function initMegaMenu() {
    const menuToggle = document.querySelector('.menu-toggle');
    const megaMenu = document.querySelector('.mega-menu');

    if (menuToggle && megaMenu) {
        let isMenuVisible = false;
        let timeoutId;

        menuToggle.addEventListener('mouseenter', () => {
            clearTimeout(timeoutId);
            megaMenu.style.display = 'block';
            isMenuVisible = true;
        });

        menuToggle.addEventListener('mouseleave', () => {
            timeoutId = setTimeout(() => {
                if (!isMenuVisible) {
                    megaMenu.style.display = 'none';
                }
            }, 200);
        });

        megaMenu.addEventListener('mouseenter', () => {
            clearTimeout(timeoutId);
            isMenuVisible = true;
        });

        megaMenu.addEventListener('mouseleave', () => {
            isMenuVisible = false;
            megaMenu.style.display = 'none';
        });
    }
}

function initNewsletterPopup() {
    const popup = document.getElementById('newsletter-popup');
    const closeBtn = popup.querySelector('.close-popup');
    const hasSeenPopup = localStorage.getItem('hasSeenNewsletterPopup');

    if (!hasSeenPopup) {
        setTimeout(() => {
            popup.style.display = 'flex';
        }, 5000);
    }

    closeBtn.addEventListener('click', () => {
        popup.style.display = 'none';
        localStorage.setItem('hasSeenNewsletterPopup', 'true');
    });

    popup.addEventListener('click', (e) => {
        if (e.target === popup) {
            popup.style.display = 'none';
        }
    });
}

function initCart() {
    const cartButtons = document.querySelectorAll('.add-to-cart');
    
    cartButtons.forEach(button => {
        button.addEventListener('click', async (e) => {
            e.preventDefault();
            const productId = button.dataset.productId;
            const quantity = button.closest('.product-card')?.querySelector('.quantity-input')?.value || 1;

            try {
                const response = await fetch('ajax/cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'add',
                        product_id: productId,
                        quantity: quantity
                    })
                });

                const data = await response.json();
                if (data.success) {
                    updateCartCount(data.cartCount);
                    showNotification('Product added to cart!', 'success');
                } else {
                    showNotification('Failed to add product to cart.', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('An error occurred.', 'error');
            }
        });
    });
}

function initQuantityControls() {
    const quantityControls = document.querySelectorAll('.quantity-control');
    
    quantityControls.forEach(control => {
        const input = control.querySelector('.quantity-input');
        const decreaseBtn = control.querySelector('.decrease');
        const increaseBtn = control.querySelector('.increase');

        if (input && decreaseBtn && increaseBtn) {
            decreaseBtn.addEventListener('click', () => {
                const currentValue = parseInt(input.value);
                if (currentValue > 1) {
                    input.value = currentValue - 1;
                    updateCartItem(input);
                }
            });

            increaseBtn.addEventListener('click', () => {
                const currentValue = parseInt(input.value);
                const maxStock = parseInt(input.dataset.maxStock || 99);
                if (currentValue < maxStock) {
                    input.value = currentValue + 1;
                    updateCartItem(input);
                }
            });

            input.addEventListener('change', () => {
                let value = parseInt(input.value);
                const maxStock = parseInt(input.dataset.maxStock || 99);
                
                if (isNaN(value) || value < 1) {
                    value = 1;
                } else if (value > maxStock) {
                    value = maxStock;
                }
                
                input.value = value;
                updateCartItem(input);
            });
        }
    });
}

async function updateCartItem(input) {
    const productId = input.dataset.productId;
    const quantity = input.value;
    const cartItem = input.closest('.cart-item');

    try {
        const response = await fetch('ajax/cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'update',
                product_id: productId,
                quantity: quantity
            })
        });

        const data = await response.json();
        if (data.success) {
            if (cartItem) {
                cartItem.querySelector('.item-subtotal').textContent = data.itemSubtotal;
                document.querySelector('.cart-subtotal').textContent = data.cartSubtotal;
                document.querySelector('.cart-total').textContent = data.cartTotal;
            }
        } else {
            showNotification('Failed to update cart.', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('An error occurred.', 'error');
    }
}

function updateCartCount(count) {
    const cartCount = document.querySelector('.cart-count');
    if (cartCount) {
        cartCount.textContent = count;
        cartCount.style.display = count > 0 ? 'block' : 'none';
    }
}

function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;

    document.body.appendChild(notification);

    // Trigger animation
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);

    // Remove notification after delay
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
} 