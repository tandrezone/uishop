/**
 * products.js — Products section
 *
 * Renders a responsive card grid of products.
 * Admins see Create / Edit / Delete controls; regular users get read-only view.
 */

import { state } from './state.js';
import { isAdmin } from './auth.js';
import { api } from './api.js';
import { openModal, closeModal } from './modal.js';

/* ---- Helpers ---- */

function escapeHtml(str) {
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

function showFormError(msg) {
  const el = document.getElementById('form-error');
  if (el) {
    el.textContent = msg;
    el.classList.remove('hidden');
  }
}

/* ---- Product form template ---- */

function productFormHTML(product = null) {
  const v = (field) => escapeHtml(product?.[field] ?? '');
  return `
    <form id="modal-form" novalidate>
      <div class="form-group">
        <label for="p-name">Name</label>
        <input type="text" id="p-name" name="name" required
               placeholder="Product name" value="${v('name')}">
      </div>
      <div class="form-group">
        <label for="p-description">Description</label>
        <textarea id="p-description" name="description"
                  placeholder="Short description">${v('description')}</textarea>
      </div>
      <div class="form-group">
        <label for="p-price">Price ($)</label>
        <input type="number" id="p-price" name="price"
               step="0.01" min="0" required
               placeholder="0.00" value="${v('price')}">
      </div>
      <div class="form-group">
        <label for="p-stock">Stock</label>
        <input type="number" id="p-stock" name="stock"
               min="0" required
               placeholder="0" value="${v('stock')}">
      </div>
      <div id="form-error" class="error-msg hidden"></div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary">
          ${product ? 'Update Product' : 'Create Product'}
        </button>
        <button type="button" class="btn btn-secondary" id="cancel-modal-btn">
          Cancel
        </button>
      </div>
    </form>
  `;
}

/* ---- Modal actions ---- */

function openCreateModal() {
  openModal('Create Product', productFormHTML(), async (form) => {
    const data = Object.fromEntries(new FormData(form));
    data.price = parseFloat(data.price);
    data.stock = parseInt(data.stock, 10);
    try {
      const created = await api.createProduct(data);
      state.products.push(created);
      closeModal();
      renderProducts();
    } catch (err) {
      showFormError(err.message);
    }
  });
  document.getElementById('cancel-modal-btn')?.addEventListener('click', closeModal);
}

function openEditModal(product) {
  openModal('Edit Product', productFormHTML(product), async (form) => {
    const data = Object.fromEntries(new FormData(form));
    data.price = parseFloat(data.price);
    data.stock = parseInt(data.stock, 10);
    try {
      const updated = await api.updateProduct(product.id, data);
      const idx = state.products.findIndex((p) => p.id === product.id);
      if (idx !== -1) state.products[idx] = updated;
      closeModal();
      renderProducts();
    } catch (err) {
      showFormError(err.message);
    }
  });
  document.getElementById('cancel-modal-btn')?.addEventListener('click', closeModal);
}

async function confirmDeleteProduct(id) {
  if (!confirm('Are you sure you want to delete this product?')) return;
  try {
    await api.deleteProduct(id);
    state.products = state.products.filter((p) => String(p.id) !== String(id));
    renderProducts();
  } catch (err) {
    alert(`Delete failed: ${err.message}`);
  }
}

/* ---- Add to Cart ---- */

async function addToCart(productId) {
  try {
    await api.addToCart({ productId: parseInt(productId), quantity: 1 });
    alert('Product added to cart successfully!');
  } catch (err) {
    alert(`Failed to add to cart: ${err.message}`);
  }
}

/* ---- Render ---- */

export async function renderProducts() {
  const area = document.getElementById('content-area');
  area.innerHTML = '<p class="loading">Loading products…</p>';

  try {
    const result = await api.getProducts();
    state.products = result.products ?? [];
  } catch (err) {
    area.innerHTML = `<p class="error-msg">${escapeHtml(err.message)}</p>`;
    return;
  }

  const admin = isAdmin();

  const headerHTML = `
    <div class="section-header">
      <h2>Products</h2>
      ${admin
      ? '<button class="btn btn-primary" id="create-product-btn">+ New Product</button>'
      : ''}
    </div>
  `;

  const cardsHTML = state.products.length
    ? state.products.map((p) => `
        <div class="product-card">
          <div class="product-img-placeholder" aria-hidden="true">🛍</div>
          <div class="product-info">
            <h3 class="product-name" title="${escapeHtml(p.name)}">${escapeHtml(p.name)}</h3>
            <p class="product-desc">${escapeHtml(p.description ?? '')}</p>
            <p class="product-price">$${Number(p.price).toFixed(2)}</p>
            <p class="product-stock">Stock: ${Number(p.stock)}</p>
          </div>
          ${admin ? `
          <div class="product-actions">
            <button class="btn btn-sm btn-secondary edit-btn" data-id="${p.id}">Edit</button>
            <button class="btn btn-sm btn-danger delete-btn"  data-id="${p.id}">Delete</button>
          </div>` : `
          <div class="product-actions">
            <button class="btn btn-sm btn-primary add-to-cart-btn" data-id="${p.id}" ${Number(p.stock) === 0 ? 'disabled' : ''}>
              ${Number(p.stock) === 0 ? 'Out of Stock' : '🛒 Add to Cart'}
            </button>
          </div>`}
        </div>
      `).join('')
    : '<p class="empty-msg">No products found.</p>';

  area.innerHTML = `${headerHTML}<div class="products-grid">${cardsHTML}</div>`;

  if (admin) {
    document.getElementById('create-product-btn')
      ?.addEventListener('click', openCreateModal);

    area.querySelectorAll('.edit-btn').forEach((btn) => {
      btn.addEventListener('click', () => {
        const product = state.products.find((p) => String(p.id) === btn.dataset.id);
        if (product) openEditModal(product);
      });
    });

    area.querySelectorAll('.delete-btn').forEach((btn) => {
      btn.addEventListener('click', () => confirmDeleteProduct(btn.dataset.id));
    });
  } else {
    // Add to cart functionality for non-admin users
    area.querySelectorAll('.add-to-cart-btn').forEach((btn) => {
      btn.addEventListener('click', () => addToCart(btn.dataset.id));
    });
  }
}
