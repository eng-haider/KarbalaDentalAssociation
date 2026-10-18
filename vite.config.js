import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/site.css',
                'resources/js/site.js',
                'resources/css/osif.css',
                'resources/js/osif.js',
                'resources/js/osif-invitation.js',
                'resources/css/osif-shop.css',
                'resources/js/osif-shop.js',
                'resources/js/dashboard-share.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
