import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
});

// Generate a simple device fingerprint
function getDeviceFingerprint(): string {
  const stored = localStorage.getItem('mbg_device_fp');
  if (stored) return stored;
  const raw = [
    navigator.userAgent,
    navigator.language,
    screen.width + 'x' + screen.height,
    Intl.DateTimeFormat().resolvedOptions().timeZone,
    Date.now().toString(36),
  ].join('|');
  // Simple hash
  let hash = 0;
  for (let i = 0; i < raw.length; i++) {
    hash = ((hash << 5) - hash + raw.charCodeAt(i)) | 0;
  }
  const fp = Math.abs(hash).toString(36) + Date.now().toString(36);
  localStorage.setItem('mbg_device_fp', fp);
  return fp;
}

// Attach token and device fingerprint
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('mbg_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  config.headers['X-Device-Fingerprint'] = getDeviceFingerprint();
  return config;
});

// Handle 401 globally — clear token but let React router handle redirect
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('mbg_token');
      // Don't do window.location.href — let React state handle redirect
    }
    return Promise.reject(error);
  }
);

export default api;
