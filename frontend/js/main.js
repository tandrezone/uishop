/**
 * main.js — Application entry point
 *
 * Responsibilities:
 *  - Bootstrap auth state from localStorage
 *  - Handle the login form submission
 *  - Handle the register form submission
 *  - Wire up sidebar navigation and logout button
 *  - Listen for the "auth:expired" event dispatched by api.js
 *  - Route to the correct initial section after login
 */

import { state } from './state.js';
import {
  isAuthenticated, initAuthState,
  setToken, logout, isAdmin
} from './auth.js';
import { api } from './api.js';
import { navigate } from './router.js';
import { closeModal } from './modal.js';

/* ====================================================
   Page visibility helpers
   ==================================================== */

function showPage(page) {
  document.getElementById('login-page').classList.toggle('hidden', page !== 'login');
  document.getElementById('dashboard').classList.toggle('hidden', page !== 'dashboard');
}

/* ====================================================
   Auth mode toggle (Login ↔ Register)
   ==================================================== */

function toggleToRegister() {
  document.getElementById('login-mode').classList.add('hidden');
  document.getElementById('register-mode').classList.remove('hidden');
  // Clear any previous errors
  document.getElementById('login-error').classList.add('hidden');
  document.getElementById('register-error').classList.add('hidden');
}

function toggleToLogin() {
  document.getElementById('register-mode').classList.add('hidden');
  document.getElementById('login-mode').classList.remove('hidden');
  // Clear any previous errors
  document.getElementById('login-error').classList.add('hidden');
  document.getElementById('register-error').classList.add('hidden');
}

/* ====================================================
   Sidebar user display
   ==================================================== */

function updateSidebarUser() {
  const user = state.user;
  if (!user) return;

  const displayName = user.username ?? user.name ?? user.sub ?? 'User';
  const role = user.role ?? 'user';

  document.getElementById('sidebar-user-info').textContent = displayName;

  const badge = document.getElementById('user-role-badge');
  badge.textContent = role;
  badge.className = `role-badge role-${role}`;
}

/* ====================================================
   Login
   ==================================================== */

async function handleLogin(e) {
  debugger;
  e.preventDefault();
  const form = e.target;
  const errorEl = document.getElementById('login-error');
  const loginBtn = document.getElementById('login-btn');

  errorEl.classList.add('hidden');
  loginBtn.disabled = true;
  loginBtn.textContent = 'Signing in…';

  const credentials = {
    username: form.username.value.trim(),
    password: form.password.value,
  };

  try {
    const data = await api.login(credentials);

    // Accept common token field names returned by different backends
    const token = data?.token ?? data?.access_token ?? data?.jwt;
    if (!token) throw new Error('No token received from server.');

    setToken(token);
    showDashboard();
    form.reset();
  } catch (err) {
    errorEl.textContent = err.message;
    errorEl.classList.remove('hidden');
  } finally {
    loginBtn.disabled = false;
    loginBtn.textContent = 'Sign In';
  }
}

/* ====================================================
   Register
   ==================================================== */

async function handleRegister(e) {
  e.preventDefault();
  const form = e.target;
  const errorEl = document.getElementById('register-error');
  const registerBtn = document.getElementById('register-btn');

  errorEl.classList.add('hidden');

  // Validate password match
  const password = form.password.value;
  const confirmPassword = form['confirm-password'].value;
  if (password !== confirmPassword) {
    errorEl.textContent = 'Passwords do not match.';
    errorEl.classList.remove('hidden');
    return;
  }

  registerBtn.disabled = true;
  registerBtn.textContent = 'Creating account…';

  try {
    const userData = {
      username: form.username.value.trim(),
      password: form.password.value,
      email: form.email.value.trim() || undefined,
    };

    const data = await api.register(userData);

    // Accept common token field names returned by different backends
    const token = data?.token ?? data?.access_token ?? data?.jwt;
    if (!token) throw new Error('Account created but no token received. Please sign in.');

    setToken(token);
    showDashboard();
    form.reset();
  } catch (err) {
    errorEl.textContent = err.message;
    errorEl.classList.remove('hidden');
  } finally {
    registerBtn.disabled = false;
    registerBtn.textContent = 'Create Account';
  }
}

/* ====================================================
   Dashboard bootstrap
   ==================================================== */

function showDashboard() {
  updateSidebarUser();
  showPage('dashboard');
  navigate('products');
}

/* ====================================================
   Logout
   ==================================================== */

function handleLogout() {
  logout();
  showPage('login');
  toggleToLogin(); // Reset to login mode
}

/* ====================================================
   Initialisation
   ==================================================== */

function init() {
  // Restore any existing session
  initAuthState();

  // --- Login form ---
  document.getElementById('login-form')
    .addEventListener('submit', handleLogin);

  // --- Register form ---
  document.getElementById('register-form')
    .addEventListener('submit', handleRegister);

  // --- Auth mode toggle buttons ---
  document.getElementById('toggle-register')
    .addEventListener('click', (e) => {
      e.preventDefault();
      toggleToRegister();
    });

  document.getElementById('toggle-login')
    .addEventListener('click', (e) => {
      e.preventDefault();
      toggleToLogin();
    });

  // --- Logout button ---
  document.getElementById('logout-btn')
    .addEventListener('click', handleLogout);

  // --- Sidebar navigation ---
  document.querySelectorAll('.nav-link').forEach((link) => {
    link.addEventListener('click', (e) => {
      e.preventDefault();
      navigate(link.dataset.section);
    });
  });

  // --- Modal close controls ---
  document.getElementById('modal-close')
    .addEventListener('click', closeModal);

  document.getElementById('modal-overlay')
    .addEventListener('click', (e) => {
      // Close only when clicking the backdrop, not the modal itself
      if (e.target === e.currentTarget) closeModal();
    });

  // --- Session expiry (fired by api.js on 401 response) ---
  window.addEventListener('auth:expired', () => {
    logout();
    showPage('login');
    toggleToLogin();
    // Show a message on the (now-visible) login form
    const errEl = document.getElementById('login-error');
    if (errEl) {
      errEl.textContent = 'Your session has expired. Please sign in again.';
      errEl.classList.remove('hidden');
    }
  });

  // --- Initial routing ---
  if (isAuthenticated()) {
    showDashboard();
  } else {
    showPage('login');
    toggleToLogin();
  }
}

document.addEventListener('DOMContentLoaded', init);
