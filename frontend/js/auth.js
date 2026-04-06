/**
 * auth.js — Authentication helpers
 * Handles JWT storage, parsing, expiry checking, and logout.
 */

import { state } from './state.js';

const TOKEN_KEY = 'uishop_token';

/**
 * Safely decode a JWT payload.
 * Uses the standard base64url → base64 conversion before atob().
 * @param {string} token
 * @returns {object|null}
 */
export function parseJwt(token) {
  try {
    const base64Url = token.split('.')[1];
    if (!base64Url) return null;
    // Replace URL-safe chars and pad to a multiple of 4
    const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
    const padded = base64 + '='.repeat((4 - (base64.length % 4)) % 4);
    return JSON.parse(atob(padded));
  } catch {
    return null;
  }
}

/** Read the raw token string from localStorage. */
export function getToken() {
  return localStorage.getItem(TOKEN_KEY);
}

/** Persist a token, update global state, and decode the payload. */
export function setToken(token) {
  localStorage.setItem(TOKEN_KEY, token);
  state.token = token;
  state.user = parseJwt(token);
}

/**
 * Check whether the current session is still valid.
 * Returns false (and clears state) if the token is missing or expired.
 */
export function isAuthenticated() {
  const token = getToken();
  if (!token) return false;
  const payload = parseJwt(token);
  if (!payload) return false;
  // Honour the standard "exp" claim (epoch seconds)
  if (payload.exp && payload.exp < Date.now() / 1000) {
    logout();
    return false;
  }
  return true;
}

/**
 * Restore auth state from localStorage on page load.
 * Call once during app initialization.
 */
export function initAuthState() {
  const token = getToken();
  if (token) {
    state.token = token;
    state.user = parseJwt(token);
  }
}

/** Return true when the authenticated user has the "admin" role. */
export function isAdmin() {
  return !!(state.user && state.user.role === 'admin');
}

/** Clear token from storage and reset global state. */
export function logout() {
  localStorage.removeItem(TOKEN_KEY);
  state.token   = null;
  state.user    = null;
  state.products = [];
  state.orders   = [];
}
