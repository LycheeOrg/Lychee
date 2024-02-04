import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import commonjs from 'vite-plugin-commonjs'

/** @type {import('vite').UserConfig} */
export default defineConfig({
  plugins: [
    commonjs(/* options */),

    laravel({
      input: ['resources/css/app.css', 'resources/js/app.ts'],
      refresh: true,
    }),

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
        "vendor/**",
      ],
    },
  }
});
