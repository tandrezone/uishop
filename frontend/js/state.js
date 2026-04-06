/**
 * state.js — Global application state
 * All modules share a single mutable state object.
 */

export const state = {
  /** @type {string|null} JWT string */
  token: null,
  /** @type {object|null} Decoded JWT payload */
  user: null,
  /** @type {Array} Cached product list */
  products: [],
  /** @type {Array} Cached order list */
  orders: [],
};
