import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import i18n from 'laravel-vue-i18n/vite';

/** @type {import('vite').UserConfig} */
export default defineConfig({
  plugins: [
    laravel({
      input: [
        'resources/css/app.css',
        'resources/js/app.ts',
      ],
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
      vue: 'vue/dist/vue.esm-bundler.js',
    },
  },
});
