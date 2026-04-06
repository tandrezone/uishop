/**
 * router.js — Simple section router
 *
 * Maps section names to render functions and updates the active nav link.
 */

import { renderProducts } from './products.js';
import { renderOrders }   from './orders.js';
import { renderProfile }  from './profile.js';

const routes = {
  products: renderProducts,
  orders:   renderOrders,
  profile:  renderProfile,
};

/**
 * Navigate to a named section.
 * Updates the sidebar active state and calls the matching render function.
 * @param {string} section — one of 'products' | 'orders' | 'profile'
 */
export function navigate(section) {
  document.querySelectorAll('.nav-link').forEach((link) => {
    link.classList.toggle('active', link.dataset.section === section);
  });

  const render = routes[section];
  if (typeof render === 'function') {
    render();
  }
}
