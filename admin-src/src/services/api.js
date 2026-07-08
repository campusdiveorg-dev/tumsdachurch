// Check if we're running in dev (Vite dev server) or production
const isDev = window.location.hostname === 'localhost' && window.location.port === '5173';
const API_BASE = isDev ? '/api' : window.location.pathname.replace(/\/admin\/?$/, '/api');

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
};

// Payments
export const paymentsApi = {
  list: () => apiFetch('/payments'),
};
