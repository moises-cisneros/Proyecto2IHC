import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        // Configuración para correr dentro de Docker con HMR
        host: '0.0.0.0',
        port: 9373,
        hmr: {
            host: 'localhost', // El navegador se conecta al host real
            clientPort: 9373,
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
