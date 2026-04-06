/**
 * profile.js — User profile section
 *
 * Fetches /users/profile from the API.
 * Falls back to the JWT payload already stored in global state
 * if the request fails (e.g. endpoint not yet implemented).
 */

import { state } from './state.js';
import { api }   from './api.js';

function escapeHtml(str) {
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

export async function renderProfile() {
  const area = document.getElementById('content-area');
  area.innerHTML = '<p class="loading">Loading profile…</p>';

  let profile;
  try {
    profile = await api.getProfile();
  } catch {
    // Graceful fallback: use the decoded JWT payload
    profile = state.user ?? {};
  }

  // Normalise common field-name variations across different backends
  const fields = [
    ['ID',       profile.id       ?? profile.sub      ?? '—'],
    ['Username', profile.username ?? profile.name     ?? profile.login ?? '—'],
    ['Email',    profile.email    ?? '—'],
    ['Role',     profile.role     ?? '—'],
  ];

  const rows = fields
    .map(([label, value]) => `
      <div class="profile-row">
        <span class="profile-label">${label}</span>
        <span class="profile-value">${escapeHtml(String(value))}</span>
      </div>
    `)
    .join('');

  area.innerHTML = `
    <div class="section-header">
      <h2>Profile</h2>
    </div>
    <div class="profile-card">
      <div class="profile-avatar" aria-hidden="true">👤</div>
      ${rows}
    </div>
  `;
}
