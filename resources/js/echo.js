import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

const isBrowser = typeof window !== 'undefined';
const isHttps = (import.meta.env.VITE_REVERB_SCHEME ?? (isBrowser && window.location.protocol === 'https:' ? 'https' : 'http')) === 'https';
const fallbackHost = isBrowser ? window.location.hostname : 'localhost';
const defaultPort = isHttps ? 443 : 80;

const reverbKey = import.meta.env.VITE_REVERB_APP_KEY || (isBrowser && window.__REVERB_KEY) || 't6g92t9i9z0tnh33ryb2';
const wsHost = import.meta.env.VITE_REVERB_HOST || fallbackHost;
const wsPort = import.meta.env.VITE_REVERB_PORT ? Number(import.meta.env.VITE_REVERB_PORT) : defaultPort;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: reverbKey,
    wsHost: wsHost,
    wsPort: wsPort,
    wssPort: wsPort,
    forceTLS: isHttps,
    enabledTransports: ['ws', 'wss'],
});
