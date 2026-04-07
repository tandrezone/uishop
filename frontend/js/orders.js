/**
 * orders.js — Orders section
 *
 * Admins see a table of ALL orders (with a "User" column).
 * Regular users see only their own orders ("My Orders").
 */

import { state } from './state.js';
import { isAdmin } from './auth.js';
import { api } from './api.js';

/* ---- Helpers ---- */

function escapeHtml(str) {
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

/**
 * Map a status string to a CSS class for the coloured badge.
 * Falls back gracefully for unknown statuses.
 */
function statusClass(status) {
  const map = {
    pending: 'status-pending',
    processing: 'status-processing',
    completed: 'status-completed',
    cancelled: 'status-cancelled',
    canceled: 'status-cancelled',
  };
  return map[String(status).toLowerCase()] ?? '';
}

function formatDate(dateStr) {
  const d = new Date(dateStr);
  return isNaN(d.getTime()) ? escapeHtml(String(dateStr)) : d.toLocaleDateString();
}

/* ---- Render ---- */

export async function renderOrders() {
  const area = document.getElementById('content-area');
  const admin = isAdmin();

  area.innerHTML = '<p class="loading">Loading orders…</p>';

  try {
    const result = admin ? await api.getOrders() : await api.getMyOrders();
    state.orders = result.orders ?? [];
  } catch (err) {
    area.innerHTML = `<p class="error-msg">${escapeHtml(err.message)}</p>`;
    return;
  }

  const title = admin ? 'All Orders' : 'My Orders';
  const colSpan = admin ? 5 : 4;

  const thead = `
    <thead>
      <tr>
        <th>#</th>
        ${admin ? '<th>User</th>' : ''}
        <th>Status</th>
        <th>Total</th>
        <th>Date</th>
      </tr>
    </thead>
  `;

  const tbody = state.orders.length
    ? state.orders.map((o) => {
      const status = o.status ?? 'unknown';
      const user = String(o.userId ?? '—');
      return `
          <tr>
            <td>${escapeHtml(String(o.id))}</td>
            ${admin ? `<td>${escapeHtml(user)}</td>` : ''}
            <td>
              <span class="status-badge ${statusClass(status)}">
                ${escapeHtml(status)}
              </span>
            </td>
            <td>$${Number(o.totalAmount ?? 0).toFixed(2)}</td>
            <td>${formatDate(o.createdAt)}</td>
          </tr>
        `;
    }).join('')
    : `<tr><td colspan="${colSpan}" class="empty-msg">No orders found.</td></tr>`;

  area.innerHTML = `
    <div class="section-header">
      <h2>${title}</h2>
    </div>
    <div class="table-wrapper">
      <table class="data-table" aria-label="${title}">
        ${thead}
        <tbody>${tbody}</tbody>
      </table>
    </div>
  `;
}
