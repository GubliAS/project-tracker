import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
    assetsInclude: ['**/*.woff', '**/*.woff2', '**/*.ttf', '**/*.eot'],
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            '@': path.resolve('resources/js'),
        },
    },
    server: {
        host: '127.0.0.1',
        strictPort: true,
        hmr: {
            host: '127.0.0.1',
        },
    },
    build: {
        cssMinify: false,
    },
});
