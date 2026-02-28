import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import { fileURLToPath, URL } from 'node:url';
import { resolve } from 'path';

/**
 * Vite configuration for building the embeddable widget.
 *
 * This creates a standalone JavaScript bundle that can be embedded
 * on external websites. The widget is built as a library with:
 * - UMD format for maximum compatibility
 * - All dependencies bundled
 * - CSS extracted to separate file
 * - Optimized and minified output
 */
export default defineConfig({
	plugins: [vue()],
	publicDir: false,
	resolve: {
		alias: {
			'@': fileURLToPath(new URL('./resources/js/', import.meta.url)),
			vue: 'vue/dist/vue.esm-bundler.js',
		},
	},
	build: {
		lib: {
			entry: resolve(__dirname, 'resources/js/embed/main.ts'),
			name: 'LycheeEmbed',
			formats: ['umd'],
			fileName: () => 'lychee-embed.js',
		},
		outDir: 'public/embed',
		emptyOutDir: true,
		cssCodeSplit: false,
		rollupOptions: {
			// Don't externalize dependencies - bundle everything for standalone widget
			output: {
				// Inline all assets into the JS bundle for single-file distribution
				inlineDynamicImports: true,
				// Ensure CSS is extracted to separate file
				assetFileNames: 'lychee-embed.css',
				// Use named exports only for library
				exports: 'named',
			},
		},
		// Generate sourcemaps for debugging
		sourcemap: true,
	},
	// Define global constants
	define: {
		'process.env.NODE_ENV': JSON.stringify('production'),
		__VUE_OPTIONS_API__: false,
		__VUE_PROD_DEVTOOLS__: false,
		__VUE_PROD_HYDRATION_MISMATCH_DETAILS__: false,
	},
});
