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
            output: {
                // Organize chunks into folders
                chunkFileNames: 'js/[name]-[hash].js',
                entryFileNames: 'js/[name]-[hash].js',
                assetFileNames: (assetInfo) => {
                    if (!assetInfo.name) {
                        return 'assets/[name]-[hash][extname]';
                    }
                    const info = assetInfo.name.split('.');
                    const ext = info[info.length - 1];
                    const fileName = assetInfo.name;

                    if (/\.(css)$/.test(fileName)) {
                        return `css/[name]-[hash].${ext}`;
                    }
                    if (/\.(png|jpe?g|gif|svg|webp|ico)$/.test(fileName)) {
                        return `images/[name]-[hash].${ext}`;
                    }
                    if (/\.(woff2?|eot|ttf|otf)$/.test(fileName)) {
                        return `fonts/[name]-[hash].${ext}`;
                    }
                    if (/\.(mp4|webm|ogg|wav|m4a|aac|oga|mp3|m4b|m4p|m4r)$/.test(fileName)) {
                        return `media/[name]-[hash].${ext}`;
                    }
                    return `others/[name]-[hash].${ext}`;
                },
            },
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
