import { fileURLToPath, URL } from 'node:url';

import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import oxlintPlugin from 'vite-plugin-oxlint';
import ViteYaml from '@modyfi/vite-plugin-yaml';
import tailwindcss from '@tailwindcss/vite';

// https://vite.dev/config/
export default defineConfig({
    plugins: [
        ViteYaml({
            onWarning: (warning) => {
                console.warn('[MythicalDash/YML⚠️] Yaml parser warning: ' + warning);
            },
        }),
        vue(),
        tailwindcss(),
        oxlintPlugin(),
    ],
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./src', import.meta.url)),
        },
    },
    server: {
        host: '0.0.0.0',
        proxy: {
            '/api': {
                target: 'http://localhost:6000',
                changeOrigin: true,
                secure: false,
                rewrite: (path) => path,
            },
            '/attachments': {
                target: 'http://localhost:6000',
                changeOrigin: true,
                secure: false,
                rewrite: (path) => path,
            },
            '/i/': {
                target: 'http://localhost:6000',
                changeOrigin: true,
                secure: false,
                rewrite: (path) => path,
            },
        },
    },
    build: {
        sourcemap: false,    
        // Increase chunk size warning limit
        chunkSizeWarningLimit: 1000,
    },
    optimizeDeps: {
        include: [
            'vue', 
            'vue-router', 
            'pinia', 
            'vue-i18n', 
            'vue-sweetalert2',
            'lucide-vue-next',
            'date-fns'
        ],
        exclude: [],
    },
    cacheDir: '.vite',
});
