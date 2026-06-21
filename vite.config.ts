import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig, loadEnv } from 'vite';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const disableWayfinder = env.DISABLE_WAYFINDER === 'true';

    return {
        plugins: [
            laravel({
                input: ['resources/js/app.ts', 'resources/css/app.css'],
                ssr: 'resources/js/ssr.ts',
                refresh: true,
            }),
            tailwindcss(),

            ...(disableWayfinder ? [] : [wayfinder({ formVariants: true })]),

            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false,
                    },
                },
            }),
        ],

        server: {
            watch: {
                usePolling: false,
                ignored: [
                    '**/storage/**',
                    '**/bootstrap/cache/**',
                    '**/vendor/**',
                    '**/public/build/**',
                    '**/node_modules/**',
                    '**/.git/**',
                ],
            },
        },

        optimizeDeps: { exclude: [] },
    };
});