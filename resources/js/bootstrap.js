import axios from 'axios';

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
    enableLogging: true,
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
        }
    }
});

console.log('🔌 Pusher Configuration Loaded');
console.log('  Key:', import.meta.env.VITE_PUSHER_APP_KEY?.substring(0, 10) + '...');
console.log('  Cluster:', import.meta.env.VITE_PUSHER_APP_CLUSTER);

if (window.Echo?.connector?.pusher?.connection) {
    const conn = window.Echo.connector.pusher.connection;
    
    conn.bind('connected', () => {
        console.log('✅ Connected to Pusher - Real-time chat active');
    });

    conn.bind('connecting', () => {
        console.log('🔄 Connecting to Pusher...');
    });

    conn.bind('disconnected', () => {
        console.log('❌ Disconnected from Pusher');
    });

    conn.bind('error', (error) => {
        console.error('❌ Pusher Error:', error);
    });
}