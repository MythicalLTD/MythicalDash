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
    plugins: [
        ViteYaml({
            onWarning: (warning) => {
                console.warn('[MythicalDash/YML⚠️] Yaml parser warning: ' + warning);
            },
        }),
        vue(),
        vueJsx(),
        vueDevTools(),
        tailwindcss(),
        visualizer({
            emitFile: true,
            filename: 'build.html',
            open: true,
            sourcemap: true,
        }),
    ],
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
    },
    optimizeDeps: {
        include: ['vue', 'vue-router', 'pinia', 'vue-i18n', 'vue-sweetalert2'],
    },
});
