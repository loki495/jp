import {
    defineConfig
} from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    server: {
        host: '0.0.0.0',
        hmr: {
            host: 'vite.jp.dev.local.test',
            clientPort: 80
        },
        cors: true,
        allowedHosts: ['vite.jp.dev.local.test'],
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: [`resources/views/**/*`],
        }),
        tailwindcss(),
    ],
});
