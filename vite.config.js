import {
    defineConfig,
    loadEnv
} from 'vite';

import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";

const env = loadEnv(process.cwd(), '');
console.log(env)

export default defineConfig({

    server: {
        host: env.VITE_HOST,
        port: env.VITE_PORT ? parseInt(env.VITE_PORT) : 5173,
        hmr: {
            host: env.VITE_HMR_HOST,
            clientPort: env.VITE_HMR_PORT ? parseInt(env.VITE_HMR_PORT) : 5173,
        },
        cors: true,
        allowedHosts: ['vite.jp.dev.local.test', '192.168.1.12', '0.0.0.0'],
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: [`resources/views/**/*`],
        }),
        tailwindcss(),
    ],
});
