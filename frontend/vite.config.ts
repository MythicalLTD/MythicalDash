import { fileURLToPath, URL } from 'node:url';

import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import vueDevTools from 'vite-plugin-vue-devtools';
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
        vueDevTools(),
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
        rollupOptions: {
            output: {
                manualChunks: {
                    // Core Vue ecosystem
                    'vue-core': ['vue', 'vue-router', 'pinia'],
                    // Chart.js bundle
                    'charts': ['chart.js'],
                    // UI components
                    'ui': ['lucide-vue-next', 'sweetalert2', 'vue-sweetalert2'],
                    // Utilities
                    'utils': ['date-fns', 'qrcode', 'vue-qrcode']
                },
                // Optimize chunk size
                chunkFileNames: () => {
                    return `js/[name]-[hash].js`;
                },
                entryFileNames: 'js/[name]-[hash].js',
                assetFileNames: (assetInfo) => {
                    if (!assetInfo.name) return 'assets/[name]-[hash].[ext]';
                    const info = assetInfo.name.split('.');
                    const ext = info[info.length - 1];
                    if (/\.(css)$/.test(assetInfo.name)) {
                        return `css/[name]-[hash].${ext}`;
                    }
                    return `assets/[name]-[hash].${ext}`;
                }
            }
        },
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
