// Check if we're running in dev (Vite dev server) or production
const isDev = (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') &&
              (window.location.port === '5173' || window.location.port === '5174');

let API_BASE;
if (import.meta.env.VITE_API_URL) {
  API_BASE = import.meta.env.VITE_API_URL;
} else if (isDev) {
  API_BASE = '/api';
} else {
  console.error(
    '[api] VITE_API_URL is not set. In production the frontend cannot fall back to a ' +
    'relative URL because the frontend and backend are on different domains. ' +
    'Set the VITE_API_URL environment variable to the backend\'s full URL ' +
    '(e.g. https://tumsdachurch-production.up.railway.app) and rebuild.'
  );
  // Assign an empty string so subsequent fetch calls fail with a clear network
  // error rather than silently hitting the wrong origin.
  API_BASE = '';
}

// Helper to get CSRF token
export async function getCsrfToken() {
  const res = await fetch(`${API_BASE}/auth/csrf`, { credentials: 'include' });
  const data = await res.json();
  return data.csrf_token;
}

// Generic API wrapper
async function apiFetch(endpoint, options = {}) {
  const headers = {
    'Content-Type': 'application/json',
    ...options.headers,
  };

  if (options.method && options.method !== 'GET') {
    const csrfToken = await getCsrfToken();
    headers['X-CSRF-Token'] = csrfToken;
  }

  const res = await fetch(`${API_BASE}${endpoint}`, {
    credentials: 'include',
    ...options,
    headers,
  });
  const data = await res.json();
  if (!res.ok) throw data;
  return data;
}

// Auth
export const authApi = {
  register: (data) => apiFetch('/auth/register', { method: 'POST', body: JSON.stringify(data) }),
  login: (data) => apiFetch('/auth/login', { method: 'POST', body: JSON.stringify(data) }),
  logout: () => apiFetch('/auth/logout', { method: 'POST' }),
  me: () => apiFetch('/auth/me'),
};

// Content
export const contentApi = {
  list: (table) => apiFetch(`/${table}`),
  get: (table, id) => apiFetch(`/${table}/${id}`),
  create: (table, data) => apiFetch(`/${table}`, { method: 'POST', body: JSON.stringify(data) }),
  update: (table, id, data) => apiFetch(`/${table}/${id}`, { method: 'PUT', body: JSON.stringify(data) }),
  delete: (table, id) => apiFetch(`/${table}/${id}`, { method: 'DELETE' }),
};

// Users
export const usersApi = {
  list: () => apiFetch('/users'),
  update: (id, data) => apiFetch(`/users/${id}`, { method: 'PUT', body: JSON.stringify(data) }),
  deactivate: (id) => apiFetch(`/users/${id}`, { method: 'DELETE' }),
  delete: (id) => apiFetch(`/users/${id}?permanent=true`, { method: 'DELETE' }),
};

// Payments
export const paymentsApi = {
  list: () => apiFetch('/payments'),
};
