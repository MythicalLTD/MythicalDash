import { fileURLToPath, URL } from 'node:url';

import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import vueJsx from '@vitejs/plugin-vue-jsx';
import vueDevTools from 'vite-plugin-vue-devtools';
import ViteYaml from '@modyfi/vite-plugin-yaml';
import tailwindcss from '@tailwindcss/vite';
import { visualizer } from 'rollup-plugin-visualizer';
// https://vite.dev/config/
export default defineConfig({
    plugins: [ViteYaml(), vue(), vueJsx(), vueDevTools(), tailwindcss(), visualizer({ open: true })],
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./src', import.meta.url)),
        },
    },
    server: {
        proxy: {
            '/api': {
                target: 'http://localhost:6000',
                changeOrigin: true,
                rewrite: (path) => path,
            },
            '/attachments': {
                target: 'http://localhost:6000',
                changeOrigin: true,
                rewrite: (path) => path,
            },
        },
    },
    build: {
        sourcemap: true,
        chunkSizeWarningLimit: 120000,
        rollupOptions: {
            // Help tree-shaking be more aggressive
            treeshake: {
                moduleSideEffects: false,
                propertyReadSideEffects: false,
                tryCatchDeoptimization: false,
            },
        },
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true,
                drop_debugger: true,
            },
        },
    },
    optimizeDeps: {
        include: ['vue', 'vue-router', 'pinia', 'vue-i18n', 'vue-sweetalert2'],
    },
});
