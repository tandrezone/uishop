/**
 * cart.js — Shopping Cart section
 *
 * Renders the user's shopping cart with items, quantities, and total amount.
 * Users can update quantities, remove items, or clear the entire cart.
 */

import { state } from './state.js';
import { api } from './api.js';
import { openModal, closeModal } from './modal.js';
import { showError, showSuccess } from './notifications.js';

/* ---- Helpers ---- */

function escapeHtml(str) {
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

/* ---- Load cart data ---- */

async function loadCart() {
  try {
    const data = await api.getCart();
    state.cart = data;
    updateCartDisplay();
  } catch (err) {
    showError(`Failed to load cart: ${err.message}`);
    updateCartDisplay();
  }
}

/* ---- Update cart display (without full re-render) ---- */

function updateCartDisplay() {
  const container = document.querySelector('.cart-container');
  if (!container) return;
  
  container.innerHTML = `
    ${renderCartItems()}
    ${renderCartSummary()}
  `;
  
  // Re-attach event listeners after updating
  attachEventListeners();
}

/* ---- Update cart item quantity ---- */

async function updateQuantity(itemId, newQuantity) {
  if (newQuantity <= 0) {
    showError('Quantity must be greater than 0');
    return;
  }

  try {
    await api.updateCartItem(itemId, { quantity: newQuantity });
    await loadCart();
    showSuccess('Cart updated successfully');
  } catch (err) {
    showError(`Failed to update quantity: ${err.message}`);
  }
}

/* ---- Remove cart item ---- */

async function removeItem(itemId) {
  if (!confirm('Remove this item from your cart?')) return;

  try {
    await api.removeCartItem(itemId);
    await loadCart();
    showSuccess('Item removed from cart');
  } catch (err) {
    showError(`Failed to remove item: ${err.message}`);
  }
}

/* ---- Clear entire cart ---- */

async function clearCart() {
  if (!confirm('Are you sure you want to clear your entire cart?')) return;

  try {
    await api.clearCart();
    await loadCart();
    showSuccess('Cart cleared successfully');
  } catch (err) {
    showError(`Failed to clear cart: ${err.message}`);
  }
}

/* ---- Render cart items ---- */

function renderCartItems() {
  const cart = state.cart;
  
  if (!cart || !cart.items || cart.items.length === 0) {
    return `
      <div class="empty-state">
        <div class="empty-state-icon">🛒</div>
        <h3>Your cart is empty</h3>
        <p>Add some products to your cart to get started!</p>
      </div>
    `;
  }

  const itemsHtml = cart.items.map(item => {
    const product = item.product;
    const itemTotal = (parseFloat(product.price) * parseInt(item.quantity)).toFixed(2);
    
    return `
      <div class="cart-item" data-item-id="${item.id}">
        <div class="cart-item-image">
          ${product.image 
            ? `<img src="${escapeHtml(product.image)}" alt="${escapeHtml(product.name)}">`
            : '<div class="placeholder-image">📦</div>'
          }
        </div>
        <div class="cart-item-details">
          <h3 class="cart-item-name">${escapeHtml(product.name)}</h3>
          ${product.description 
            ? `<p class="cart-item-description">${escapeHtml(product.description)}</p>`
            : ''
          }
          <p class="cart-item-price">$${parseFloat(product.price).toFixed(2)}</p>
          <p class="cart-item-stock">
            ${parseInt(product.stock) > 0 
              ? `<span class="stock-available">In stock: ${product.stock}</span>`
              : '<span class="stock-unavailable">Out of stock</span>'
            }
          </p>
        </div>
        <div class="cart-item-quantity">
          <label for="qty-${item.id}">Quantity:</label>
          <input 
            type="number" 
            id="qty-${item.id}" 
            class="quantity-input" 
            value="${item.quantity}" 
            min="1" 
            max="${product.stock}"
            data-item-id="${item.id}"
          >
        </div>
        <div class="cart-item-total">
          <p class="item-total-label">Total:</p>
          <p class="item-total-price">$${itemTotal}</p>
        </div>
        <div class="cart-item-actions">
          <button class="btn btn-sm btn-danger remove-item-btn" data-item-id="${item.id}">
            Remove
          </button>
        </div>
      </div>
    `;
  }).join('');

  return `
    <div class="cart-items">
      ${itemsHtml}
    </div>
  `;
}

/* ---- Render cart summary ---- */

function renderCartSummary() {
  const cart = state.cart;
  
  if (!cart || !cart.items || cart.items.length === 0) {
    return '';
  }

  return `
    <div class="cart-summary">
      <h3>Cart Summary</h3>
      <div class="cart-summary-row">
        <span>Total Items:</span>
        <span>${cart.totalItems}</span>
      </div>
      <div class="cart-summary-row cart-summary-total">
        <span>Total Amount:</span>
        <span>$${parseFloat(cart.totalAmount).toFixed(2)}</span>
      </div>
      <div class="cart-summary-actions">
        <button class="btn btn-danger btn-full" id="clear-cart-btn">
          Clear Cart
        </button>
        <button class="btn btn-primary btn-full" id="checkout-btn">
          Proceed to Checkout
        </button>
      </div>
    </div>
  `;
}

/* ---- Main render function ---- */

export function renderCart() {
  const container = document.getElementById('content-area');
  if (!container) return;

  container.innerHTML = `
    <div class="section-header">
      <h2>Shopping Cart</h2>
      <button class="btn btn-secondary" id="refresh-cart-btn">
        <span aria-hidden="true">🔄</span> Refresh
      </button>
    </div>
    
    <div class="cart-container">
      ${renderCartItems()}
      ${renderCartSummary()}
    </div>
  `;

  // Attach event listeners
  attachEventListeners();
  
  // Load cart data
  loadCart();
}

/* ---- Event listeners ---- */

function attachEventListeners() {
  // Refresh button
  const refreshBtn = document.getElementById('refresh-cart-btn');
  if (refreshBtn) {
    refreshBtn.addEventListener('click', loadCart);
  }

  // Clear cart button
  const clearBtn = document.getElementById('clear-cart-btn');
  if (clearBtn) {
    clearBtn.addEventListener('click', clearCart);
  }

  // Checkout button
  const checkoutBtn = document.getElementById('checkout-btn');
  if (checkoutBtn) {
    checkoutBtn.addEventListener('click', handleCheckout);
  }

  // Remove item buttons
  document.querySelectorAll('.remove-item-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const itemId = e.target.dataset.itemId;
      removeItem(parseInt(itemId));
    });
  });

  // Quantity inputs
  document.querySelectorAll('.quantity-input').forEach(input => {
    input.addEventListener('change', (e) => {
      const itemId = e.target.dataset.itemId;
      const newQuantity = parseInt(e.target.value);
      updateQuantity(parseInt(itemId), newQuantity);
    });
  });
}

/* ---- Checkout handler ---- */

function handleCheckout() {
  openModal('Checkout', `
    <div class="modal-content">
      <p>Checkout functionality is not yet implemented.</p>
      <p>This will create an order from your cart items.</p>
      <div class="modal-actions">
        <button class="btn btn-secondary" id="close-checkout-modal">
          Close
        </button>
      </div>
    </div>
  `);
  
  // Attach event listener to close button
  document.getElementById('close-checkout-modal')?.addEventListener('click', closeModal);
}
