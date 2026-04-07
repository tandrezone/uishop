/**
 * api.js — Centralised fetch() wrapper
 *
 * Every request automatically attaches the Authorization: Bearer header.
 * A 401 response dispatches a custom "auth:expired" event so the app can
 * redirect to the login screen without a direct dependency on the DOM.
 *
 * Change BASE_URL to match your backend (e.g. 'https://api.example.com').
 */

import { getToken } from './auth.js';

const BASE_URL = 'http://localhost:8000/api';

/**
 * Internal request helper.
 * @param {'GET'|'POST'|'PUT'|'PATCH'|'DELETE'} method
 * @param {string} endpoint  — path relative to BASE_URL
 * @param {object|null} body — will be JSON-serialised when provided
 * @returns {Promise<object|null>}
 */
async function request(method, endpoint, body = null) {
  const token = getToken();

  const headers = { 'Content-Type': 'application/json' };
  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
  }

  const options = { method, headers };
  if (body !== null) {
    options.body = JSON.stringify(body);
  }

  const response = await fetch(`${BASE_URL}${endpoint}`, options);

  // Empty body (e.g. 204 No Content on DELETE)
  if (response.status === 204) return null;

  const data = await response.json().catch(() => null);

  if (response.status === 401) {
    // Let the app handle the redirect without a direct coupling
    window.dispatchEvent(new CustomEvent('auth:expired'));
    throw Object.assign(new Error('Session expired. Please log in again.'), {
      status: 401,
      data,
    });
  }

  if (!response.ok) {
    const message = data?.message ?? data?.error ?? `HTTP ${response.status}`;
    throw Object.assign(new Error(message), { status: response.status, data });
  }

  return data;
}

/* ---- Public API surface ---- */
export const api = {
  // Auth
  login: (credentials) => request('POST', '/auth/login', credentials),
  register: (data) => request('POST', '/auth/register', data),

  // Products
  getProducts: () => request('GET', '/products'),
  createProduct: (data) => request('POST', '/products', data),
  updateProduct: (id, data) => request('PUT', `/products/${id}`, data),
  deleteProduct: (id) => request('DELETE', `/products/${id}`),

  // Orders
  getOrders: () => request('GET', '/orders'),
  getMyOrders: () => request('GET', '/orders/my'),

  // Users
  getProfile: () => request('GET', '/users/profile'),

  // Cart
  getCart: () => request('GET', '/cart'),
  addToCart: (data) => request('POST', '/cart/items', data),
  updateCartItem: (id, data) => request('PUT', `/cart/items/${id}`, data),
  removeCartItem: (id) => request('DELETE', `/cart/items/${id}`),
  clearCart: () => request('DELETE', '/cart'),
  checkout: (data) => request('POST', '/cart/checkout', data),
};
