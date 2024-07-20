import { fileURLToPath, URL } from 'node:url';
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import i18n from 'laravel-vue-i18n/vite';
// import path from "path";

/** @type {import('vite').UserConfig} */
export default defineConfig({
  plugins: [
    laravel({
      input: [
        'resources/sass/app.scss',
        'resources/js/app.ts',
      ],
      refresh: true,
    }),
    vue(
    {
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false,
        },
      },
    }),
    i18n(),
  ],
  server: {
    watch: {
      ignored: [
        "**/.*/**",
        "**/database/**",
        "**/node_modules/**",
        "**/public/**",
        "**/storage/**",
        "**/tests/**",
        "**/vendor/**",
        "**/presets/**",
      ],
    },
  },
  resolve: {
    alias: {
      // @ts-ignore-next-line
      '@': fileURLToPath(new URL('./resources/js/', import.meta.url)),
      // "@": path.resolve(__dirname, "./resources/js/"),
      vue: 'vue/dist/vue.esm-bundler.js',
    },
  },
  build: {
    rollupOptions: {
        output:{
            manualChunks(id) {
                if (id.includes('node_modules')) {
                    return id.toString().split('node_modules/')[1].split('/')[0].toString();
                }
            }
        }
    }
  }
});
