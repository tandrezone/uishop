/**
 * products.js — Products section
 *
 * Renders a responsive card grid of products.
 * Admins see Create / Edit / Delete controls; regular users get read-only view.
 * Supports product images (base64 or URL), suppliers, ratings, comments, and variants.
 */

import { state } from './state.js';
import { isAdmin } from './auth.js';
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

function showFormError(msg) {
  const el = document.getElementById('form-error');
  if (el) {
    el.textContent = msg;
    el.classList.remove('hidden');
  }
}

/**
 * Render a product image element.
 * If the product has an image (base64 or URL), shows it; otherwise shows
 * a styled placeholder with the product name centered.
 */
function productImageHTML(product) {
  if (product.image) {
    return `<img class="product-img" src="${escapeHtml(product.image)}" alt="${escapeHtml(product.name)}" loading="lazy">`;
  }
  return `<div class="product-img-placeholder" aria-hidden="true" data-name="${escapeHtml(product.name)}">
    <span class="product-img-placeholder-text">${escapeHtml(product.name)}</span>
  </div>`;
}

/** Render star rating (0-5) */
function ratingHTML(rating) {
  if (rating === null || rating === undefined) return '';
  const stars = Math.round(Number(rating));
  const filled = '★'.repeat(Math.min(5, Math.max(0, stars)));
  const empty = '☆'.repeat(5 - filled.length);
  return `<p class="product-rating" title="Rating: ${Number(rating).toFixed(1)}/5">${filled}${empty} <span class="rating-value">${Number(rating).toFixed(1)}</span></p>`;
}

/* ---- Product form template ---- */

function productFormHTML(product = null, suppliers = []) {
  const v = (field) => escapeHtml(product?.[field] ?? '');
  const supplierOptions = suppliers.map((s) =>
    `<option value="${s.id}" ${product?.supplierId === s.id ? 'selected' : ''}>${escapeHtml(s.name)}</option>`
  ).join('');

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
      <div class="form-group">
        <label for="p-image">Image (URL or base64)</label>
        <input type="text" id="p-image" name="image"
               placeholder="https://... or data:image/..." value="${v('image')}">
      </div>
      <div class="form-group">
        <label for="p-category">Category</label>
        <input type="text" id="p-category" name="category"
               placeholder="e.g. Electronics" value="${v('category')}">
      </div>
      <div class="form-group">
        <label for="p-supplier">Supplier</label>
        <select id="p-supplier" name="supplierId">
          <option value="">— None —</option>
          ${supplierOptions}
        </select>
      </div>
      <div class="form-group">
        <label for="p-rating">Rating (0–5)</label>
        <input type="number" id="p-rating" name="rating"
               step="0.1" min="0" max="5"
               placeholder="e.g. 4.5" value="${v('rating')}">
      </div>
      <div class="form-group">
        <label for="p-comments">Comments</label>
        <textarea id="p-comments" name="comments"
                  placeholder="Internal notes or comments">${v('comments')}</textarea>
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

/* ---- Variant form template ---- */

function variantFormHTML(variant = null) {
  const v = (field) => escapeHtml(variant?.[field] ?? '');
  const attrs = variant?.attributes ? JSON.stringify(variant.attributes, null, 2) : '';
  return `
    <form id="modal-form" novalidate>
      <div class="form-group">
        <label for="v-name">Variant Name</label>
        <input type="text" id="v-name" name="name" required
               placeholder="e.g. Small / Red / 500ml" value="${v('name')}">
      </div>
      <div class="form-group">
        <label for="v-sku">SKU (optional)</label>
        <input type="text" id="v-sku" name="sku"
               placeholder="e.g. PROD-001-SM" value="${v('sku')}">
      </div>
      <div class="form-group">
        <label for="v-price">Price ($)</label>
        <input type="number" id="v-price" name="price"
               step="0.01" min="0" required
               placeholder="0.00" value="${v('price')}">
      </div>
      <div class="form-group">
        <label for="v-stock">Stock</label>
        <input type="number" id="v-stock" name="stock"
               min="0" required
               placeholder="0" value="${v('stock')}">
      </div>
      <div class="form-group">
        <label for="v-attributes">Attributes (JSON, optional)</label>
        <textarea id="v-attributes" name="attributes"
                  placeholder='{"size":"S","color":"red"}'>${escapeHtml(attrs)}</textarea>
      </div>
      <div id="form-error" class="error-msg hidden"></div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary">
          ${variant ? 'Update Variant' : 'Add Variant'}
        </button>
        <button type="button" class="btn btn-secondary" id="cancel-modal-btn">
          Cancel
        </button>
      </div>
    </form>
  `;
}

/* ---- Form data helpers ---- */

function normalizeProductFormData(data) {
  data.price = parseFloat(data.price);
  data.stock = parseInt(data.stock, 10);
  if (data.supplierId === '') data.supplierId = null;
  if (data.rating === '') data.rating = null;
  if (data.supplierId !== null) data.supplierId = parseInt(data.supplierId, 10);
  if (data.rating !== null) data.rating = parseFloat(data.rating);
  if (data.image === '') data.image = null;
  if (data.category === '') data.category = null;
  if (data.comments === '') data.comments = null;
  return data;
}

function normalizeVariantFormData(data) {
  data.price = parseFloat(data.price);
  data.stock = parseInt(data.stock, 10);
  if (data.sku === '') data.sku = null;
  if (data.attributes) {
    try { data.attributes = JSON.parse(data.attributes); } catch (_) { data.attributes = null; }
  } else {
    data.attributes = null;
  }
  return data;
}

/* ---- Modal actions ---- */

async function openCreateModal() {
  let suppliers = [];
  try {
    const result = await api.getSuppliers();
    suppliers = result.suppliers ?? [];
  } catch (_) { /* suppliers optional */ }

  openModal('Create Product', productFormHTML(null, suppliers), async (form) => {
    const data = normalizeProductFormData(Object.fromEntries(new FormData(form)));
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

async function openEditModal(product) {
  let suppliers = [];
  try {
    const result = await api.getSuppliers();
    suppliers = result.suppliers ?? [];
  } catch (_) { /* suppliers optional */ }

  openModal('Edit Product', productFormHTML(product, suppliers), async (form) => {
    const data = normalizeProductFormData(Object.fromEntries(new FormData(form)));
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

/* ---- Variant management ---- */

async function openVariantsModal(product) {
  let variants = [];
  try {
    const result = await api.getVariants(product.id);
    variants = result.variants ?? [];
  } catch (err) {
    showError(`Failed to load variants: ${err.message}`);
    return;
  }

  const variantRows = variants.length
    ? variants.map((v) => `
        <tr>
          <td>${escapeHtml(v.name)}</td>
          <td>${v.sku ? escapeHtml(v.sku) : '—'}</td>
          <td>$${Number(v.price).toFixed(2)}</td>
          <td>${Number(v.stock)}</td>
          <td>
            <button class="btn btn-sm btn-secondary edit-variant-btn" data-id="${v.id}">Edit</button>
            <button class="btn btn-sm btn-danger delete-variant-btn" data-id="${v.id}">Delete</button>
          </td>
        </tr>
      `).join('')
    : '<tr><td colspan="5" class="empty-msg">No variants yet.</td></tr>';

  const html = `
    <div>
      <table class="variants-table">
        <thead>
          <tr><th>Name</th><th>SKU</th><th>Price</th><th>Stock</th><th>Actions</th></tr>
        </thead>
        <tbody>${variantRows}</tbody>
      </table>
      <div style="margin-top:1rem">
        <button class="btn btn-primary" id="add-variant-btn">+ Add Variant</button>
      </div>
    </div>
  `;

  openModal(`Variants — ${escapeHtml(product.name)}`, html, null);

  document.getElementById('add-variant-btn')?.addEventListener('click', () => {
    closeModal();
    openAddVariantModal(product);
  });

  document.querySelectorAll('.edit-variant-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
      const variant = variants.find((v) => String(v.id) === btn.dataset.id);
      if (variant) {
        closeModal();
        openEditVariantModal(product, variant);
      }
    });
  });

  document.querySelectorAll('.delete-variant-btn').forEach((btn) => {
    btn.addEventListener('click', async () => {
      if (!confirm('Delete this variant?')) return;
      try {
        await api.deleteVariant(product.id, btn.dataset.id);
        closeModal();
        openVariantsModal(product);
      } catch (err) {
        showError(`Delete failed: ${err.message}`);
      }
    });
  });
}

function openAddVariantModal(product) {
  openModal(`Add Variant — ${escapeHtml(product.name)}`, variantFormHTML(), async (form) => {
    const data = normalizeVariantFormData(Object.fromEntries(new FormData(form)));
    try {
      await api.createVariant(product.id, data);
      closeModal();
      showSuccess('Variant added!');
      openVariantsModal(product);
    } catch (err) {
      showFormError(err.message);
    }
  });
  document.getElementById('cancel-modal-btn')?.addEventListener('click', () => {
    closeModal();
    openVariantsModal(product);
  });
}

function openEditVariantModal(product, variant) {
  openModal(`Edit Variant — ${escapeHtml(product.name)}`, variantFormHTML(variant), async (form) => {
    const data = normalizeVariantFormData(Object.fromEntries(new FormData(form)));
    try {
      await api.updateVariant(product.id, variant.id, data);
      closeModal();
      showSuccess('Variant updated!');
      openVariantsModal(product);
    } catch (err) {
      showFormError(err.message);
    }
  });
  document.getElementById('cancel-modal-btn')?.addEventListener('click', () => {
    closeModal();
    openVariantsModal(product);
  });
}

/* ---- Add to Cart with variant selection ---- */

async function openAddToCartModal(product) {
  let variants = [];
  try {
    const result = await api.getVariants(product.id);
    variants = result.variants ?? [];
  } catch (_) { /* no variants */ }

  if (variants.length === 0) {
    // No variants — add base product directly
    await addToCart(product.id);
    return;
  }

  const variantOptions = variants
    .filter((v) => v.stock > 0)
    .map((v) => `<option value="${v.id}" data-price="${v.price}">${escapeHtml(v.name)} — $${Number(v.price).toFixed(2)} (${v.stock} in stock)</option>`)
    .join('');

  if (!variantOptions) {
    showError('All variants are out of stock.');
    return;
  }

  const html = `
    <form id="modal-form" novalidate>
      <div class="form-group">
        <label for="cart-variant">Select variant</label>
        <select id="cart-variant" name="variantId" required>
          ${variantOptions}
        </select>
      </div>
      <div class="form-group">
        <label for="cart-qty">Quantity</label>
        <input type="number" id="cart-qty" name="quantity" min="1" value="1" required>
      </div>
      <div id="form-error" class="error-msg hidden"></div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary">🛒 Add to Cart</button>
        <button type="button" class="btn btn-secondary" id="cancel-modal-btn">Cancel</button>
      </div>
    </form>
  `;

  openModal(`Add to Cart — ${escapeHtml(product.name)}`, html, async (form) => {
    const data = Object.fromEntries(new FormData(form));
    try {
      await api.addToCart({
        productId: product.id,
        variantId: data.variantId ? parseInt(data.variantId, 10) : undefined,
        quantity: parseInt(data.quantity, 10) || 1,
      });
      closeModal();
      showSuccess('Product added to cart successfully!');
    } catch (err) {
      showFormError(err.message);
    }
  });
  document.getElementById('cancel-modal-btn')?.addEventListener('click', closeModal);
}

async function addToCart(productId) {
  try {
    await api.addToCart({ productId: parseInt(productId), quantity: 1 });
    showSuccess('Product added to cart successfully!');
  } catch (err) {
    showError(`Failed to add to cart: ${err.message}`);
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

  // Fetch suppliers map for display
  let suppliersMap = {};
  try {
    const sResult = await api.getSuppliers();
    (sResult.suppliers ?? []).forEach((s) => { suppliersMap[s.id] = s; });
  } catch (_) { /* suppliers optional */ }

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
    ? state.products.map((p) => {
        const supplier = p.supplierId ? suppliersMap[p.supplierId] : null;
        return `
        <div class="product-card">
          ${productImageHTML(p)}
          <div class="product-info">
            <h3 class="product-name" title="${escapeHtml(p.name)}">${escapeHtml(p.name)}</h3>
            <p class="product-desc">${escapeHtml(p.description ?? '')}</p>
            <p class="product-price">$${Number(p.price).toFixed(2)}</p>
            <p class="product-stock">Stock: ${Number(p.stock)}</p>
            ${ratingHTML(p.rating)}
            ${supplier ? `<p class="product-supplier">Supplier: ${escapeHtml(supplier.name)}</p>` : ''}
            ${p.comments ? `<p class="product-comments">${escapeHtml(p.comments)}</p>` : ''}
          </div>
          ${admin ? `
          <div class="product-actions">
            <button class="btn btn-sm btn-secondary edit-btn" data-id="${p.id}">Edit</button>
            <button class="btn btn-sm btn-secondary variants-btn" data-id="${p.id}">Variants</button>
            <button class="btn btn-sm btn-danger delete-btn"  data-id="${p.id}">Delete</button>
          </div>` : `
          <div class="product-actions">
            <button class="btn btn-sm btn-primary add-to-cart-btn" data-id="${p.id}" ${Number(p.stock) === 0 ? 'disabled' : ''}>
              ${Number(p.stock) === 0 ? 'Out of Stock' : '🛒 Add to Cart'}
            </button>
          </div>`}
        </div>
      `;
      }).join('')
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

    area.querySelectorAll('.variants-btn').forEach((btn) => {
      btn.addEventListener('click', () => {
        const product = state.products.find((p) => String(p.id) === btn.dataset.id);
        if (product) openVariantsModal(product);
      });
    });

    area.querySelectorAll('.delete-btn').forEach((btn) => {
      btn.addEventListener('click', () => confirmDeleteProduct(btn.dataset.id));
    });
  } else {
    // Add to cart functionality for non-admin users
    area.querySelectorAll('.add-to-cart-btn').forEach((btn) => {
      btn.addEventListener('click', () => {
        const product = state.products.find((p) => String(p.id) === btn.dataset.id);
        if (product) openAddToCartModal(product);
      });
    });
  }
}
