/**
 * notifications.js — Reusable notification utilities
 *
 * Provides consistent error and success messaging across the application.
 */

/**
 * Display an error message
 * @param {string} msg - The error message to display
 */
export function showError(msg) {
  const container = document.getElementById('content-area');
  if (!container) return;
  
  const errorDiv = document.createElement('div');
  errorDiv.className = 'alert alert-error';
  errorDiv.textContent = msg;
  errorDiv.style.marginBottom = '1rem';
  
  container.insertBefore(errorDiv, container.firstChild);
  
  setTimeout(() => errorDiv.remove(), 5000);
}

/**
 * Display a success message
 * @param {string} msg - The success message to display
 */
export function showSuccess(msg) {
  const container = document.getElementById('content-area');
  if (!container) return;
  
  const successDiv = document.createElement('div');
  successDiv.className = 'alert alert-success';
  successDiv.textContent = msg;
  successDiv.style.marginBottom = '1rem';
  
  container.insertBefore(successDiv, container.firstChild);
  
  setTimeout(() => successDiv.remove(), 3000);
}
