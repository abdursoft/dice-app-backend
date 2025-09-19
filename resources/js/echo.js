import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

let echoInstance = null;

export function initEcho(token) {
    if (!echoInstance) {
        const driver = import.meta.env.VITE_BROADCAST_DRIVER;
        if(driver === 'pusher'){
            echoInstance = new Echo({
                broadcaster: 'pusher',
                key: import.meta.env.VITE_PUSHER_APP_KEY,
                cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
                forceTLS: true,
                authEndpoint: 'http://localhost:8000/api/broadcasting/auth',
                auth: {
                    headers: {
                        Authorization: `Bearer ${token}`,
                    },
                },
            });
        }else{
            echoInstance = new Echo({
                broadcaster: 'reverb',
                key: import.meta.env.VITE_REVERB_APP_KEY,
                wsHost: import.meta.env.VITE_REVERB_HOST ?? window.location.hostname,
                wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
                wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
                forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
                enabledTransports: ['ws', 'wss'],
                authEndpoint: 'http://localhost:8000/api/broadcasting/auth',
                auth: {
                    headers: {
                        Authorization: `Bearer ${token}`,
                    },
                },
            });
        }
    }
    return echoInstance;
}

export function getEcho() {
    return echoInstance;
}
