/**
 * modal.js — Lightweight modal utility
 *
 * Opens a shared modal overlay with a title and arbitrary HTML body.
 * An optional onSubmit callback fires when a #modal-form inside the body
 * is submitted.
 */

const overlay  = () => document.getElementById('modal-overlay');
const titleEl  = () => document.getElementById('modal-title');
const bodyEl   = () => document.getElementById('modal-body');

/**
 * Open the modal.
 * @param {string}   title     — text shown in the modal header
 * @param {string}   bodyHTML  — HTML injected into the modal body
 * @param {Function} [onSubmit] — async (form) handler for #modal-form submit
 */
export function openModal(title, bodyHTML, onSubmit) {
  titleEl().textContent = title;
  bodyEl().innerHTML    = bodyHTML;
  overlay().classList.remove('hidden');

  // Attach submit handler if the body contains a form with id="modal-form"
  const form = document.getElementById('modal-form');
  if (form && typeof onSubmit === 'function') {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      await onSubmit(form);
    });
  }
}

/** Close the modal and wipe its content. */
export function closeModal() {
  overlay().classList.add('hidden');
  bodyEl().innerHTML    = '';
  titleEl().textContent = '';
}
