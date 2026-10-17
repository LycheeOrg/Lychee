import { fileURLToPath, URL } from "node:url";
import { type ConfigEnv, defineConfig, type HttpProxy, loadEnv, type Plugin, type PluginOption, type ProxyOptions, type UserConfig } from "vite-plus";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import i18n from "laravel-vue-i18n/vite";
import tailwindcss from "@tailwindcss/vite";
import ui from "@nuxt/ui/vite";

/**
 * Files `vp check`, `vp fmt` and `vp lint` skip when called without paths, so
 * that the toolchain scope is resources/js, resources/sass, e2e and the root
 * TypeScript configs (Feature 096, Q-096-04). Oxfmt would otherwise format
 * every Markdown, YAML and JSON file of the repository. A new root directory
 * holding frontend-like files must be added here.
 */
const TOOLCHAIN_SCOPE = [
	// Non-frontend file types, anywhere.
	"**/*.json",
	"**/*.md",
	"**/*.yml",
	"**/*.yaml",
	"**/*.toml",
	"**/*.html",
	// Generated at the root by the Nuxt UI plugin (gitignored).
	"/auto-imports.d.ts",
	"/components.d.ts",
	// Every root directory other than resources and e2e.
	"/.agents/**",
	"/.claude/**",
	"/.github/**",
	"/.impeccable/**",
	"/.security-audit/**",
	"/.vscode/**",
	"/app/**",
	"/bootstrap/**",
	"/composer-cache/**",
	"/config/**",
	"/database/**",
	"/docker/**",
	"/docs/**",
	"/lang/**",
	"/pgdata/**",
	"/phpstan/**",
	"/public/**",
	"/routes/**",
	"/scripts/**",
	"/secrets/**",
	"/storage/**",
	"/tests/**",
	"/vendor/**",
	"/vite/**",
	"/resources/fonts/**",
	"/resources/img/**",
	"/resources/views/**",
];

/**
 * Oxlint, Oxfmt and lint-staged configuration, shared by every config this
 * file returns (Feature 096, ADR-096-01). The lint rules are the mapping of
 * the former ESLint configuration (vue essential + typescript recommended);
 * Oxlint lints the <script> blocks of .vue files, vue-tsc covers templates.
 */
const toolchain = {
	lint: {
		plugins: ["oxc", "typescript", "unicorn", "vue"],
		categories: {
			correctness: "warn",
		},
		env: {
			builtin: true,
		},
		// Vendored third-party code is not linted.
		ignorePatterns: [...TOOLCHAIN_SCOPE, "resources/js/vendor/webauthn/**"],
		rules: {
			"vue/no-arrow-functions-in-watch": "error",
			"vue/no-async-in-computed-properties": "error",
			"vue/no-computed-properties-in-data": "error",
			"vue/no-deprecated-data-object-declaration": "error",
			"vue/no-deprecated-delete-set": "error",
			"vue/no-deprecated-destroyed-lifecycle": "error",
			"vue/no-deprecated-events-api": "error",
			"vue/no-deprecated-model-definition": "error",
			"vue/no-deprecated-props-default-this": "error",
			"vue/no-deprecated-vue-config-keycodes": "error",
			"vue/no-dupe-keys": "error",
			"vue/no-export-in-script-setup": "error",
			"vue/no-expose-after-await": "error",
			"vue/no-lifecycle-after-await": "error",
			"vue/no-reserved-component-names": "error",
			"vue/no-reserved-keys": "error",
			"vue/no-reserved-props": "error",
			"vue/no-shared-component-data": "error",
			"vue/no-side-effects-in-computed-properties": "error",
			"vue/no-watch-after-await": "error",
			"vue/prefer-import-from-vue": "error",
			"vue/require-prop-type-constructor": "error",
			"vue/require-render-return": "error",
			"vue/require-slots-as-functions": "error",
			"vue/return-in-computed-property": "error",
			"vue/return-in-emits-validator": "error",
			"vue/valid-define-emits": "error",
			"vue/valid-define-options": "error",
			"vue/valid-define-props": "error",
			"vue/valid-next-tick": "error",
			"no-array-constructor": "error",
			"no-unused-expressions": "error",
			"no-unused-vars": [
				"error",
				{
					args: "all",
					argsIgnorePattern: "^_",
					caughtErrors: "all",
					caughtErrorsIgnorePattern: "^_",
					destructuredArrayIgnorePattern: "^_",
					varsIgnorePattern: "^_",
					ignoreRestSiblings: true,
				},
			],
			"typescript/ban-ts-comment": "error",
			"typescript/no-duplicate-enum-values": "error",
			"typescript/no-empty-object-type": "error",
			"typescript/no-explicit-any": "error",
			"typescript/no-extra-non-null-assertion": "error",
			"typescript/no-misused-new": "error",
			"typescript/no-namespace": "error",
			"typescript/no-non-null-asserted-optional-chain": "error",
			"typescript/no-require-imports": "error",
			"typescript/no-this-alias": "error",
			"typescript/no-unnecessary-type-constraint": "error",
			"typescript/no-unsafe-declaration-merging": "error",
			"typescript/no-unsafe-function-type": "error",
			"typescript/no-wrapper-object-types": "error",
			"typescript/prefer-as-const": "error",
			"typescript/prefer-namespace-keyword": "error",
			"typescript/triple-slash-reference": "error",
			"vite-plus/prefer-vite-plus-imports": "error",
		},
		overrides: [
			{
				// Core rules the TypeScript compiler already enforces.
				files: ["**/*.ts", "**/*.tsx", "**/*.mts", "**/*.cts", "**/*.vue"],
				rules: {
					"constructor-super": "off",
					"getter-return": "off",
					"no-class-assign": "off",
					"no-const-assign": "off",
					"no-dupe-class-members": "off",
					"no-dupe-keys": "off",
					"no-func-assign": "off",
					"no-import-assign": "off",
					"no-new-native-nonconstructor": "off",
					"no-obj-calls": "off",
					"no-redeclare": "off",
					"no-setter-return": "off",
					"no-this-before-super": "off",
					"no-undef": "off",
					"no-unreachable": "off",
					"no-unsafe-negation": "off",
					"no-var": "error",
					"no-with": "off",
					"prefer-const": "error",
					"prefer-rest-params": "error",
					"prefer-spread": "error",
				},
			},
		],
		options: {
			// vue-tsc is the type-checker (`vp run typecheck`); tsgolint does not type-check .vue files.
			typeAware: false,
			typeCheck: false,
		},
		jsPlugins: [
			{
				name: "vite-plus",
				specifier: "vite-plus/oxlint-plugin",
			},
		],
	},
	fmt: {
		// Same output as the former Prettier setup (.prettierrc.json + .editorconfig tabs);
		// insertFinalNewline overrides the .editorconfig `insert_final_newline = false`.
		printWidth: 150,
		semi: true,
		useTabs: true,
		tabWidth: 4,
		insertFinalNewline: true,
		sortPackageJson: false,
		ignorePatterns: TOOLCHAIN_SCOPE,
	},
	staged: {
		"*.{ts,vue,css}": "vp fmt",
	},
};

function leafletGlobalPlugin(): Plugin {
	const LEAFLET_PLUGINS = ["leaflet-gpx", "leaflet-rotatedmarker", "leaflet.markercluster"];
	return {
		name: "vite-plugin-leaflet-global",
		transform(code, id) {
			if (LEAFLET_PLUGINS.some((p) => id.includes(p)) && !id.endsWith(".css") && !id.includes("?vue&type=style")) {
				return { code: `import L from 'leaflet';\n${code}`, map: null };
			}
		},
	};
}

const laravelPlugin = laravel({
	input: ["resources/sass/app.css", "resources/js/app.ts", "resources/sass/app-v8.css", "resources/js/app-v8.ts"],
	refresh: true,
});

const localDevelopMiddleware: PluginOption = {
	name: "develop-rewrite-middleware",
	configureServer(server) {
		const viteIndexPath = "/vite/index.html";

		server.middlewares.use((req, _res, next) => {
			if (!req.url) {
				next();
				return;
			}

			const requestUrl: string = req.url;
			const doNotTouch = ["/@", "/api", "/resources", "/node_modules"];
			if (doNotTouch.some((page) => requestUrl.startsWith(page))) {
				next();
				return;
			}

			const startsWithPages = ["/gallery", "/frame", "/map", "/search"];
			// Check if req.url starts with pages
			if (startsWithPages.some((page) => requestUrl.startsWith(page))) {
				req.url = viteIndexPath;
			}

			const pages = [
				"/",
				"/diagnostics",
				"/permissions",
				"/jobs",
				"/maintenance",
				"/profile",
				"/settings",
				"/sharing",
				"/statistics",
				"/users",
			];
			// Check if requestUrl is in pages
			if (pages.includes(requestUrl)) {
				req.url = viteIndexPath;
			}

			next();
		});
	},
};

const baseConfig = {
	...toolchain,
	base: "./",
	/**
	 * Vitest (bundled with Vite+, run with `vp test`): frontend unit tests sit
	 * next to the module they cover as `*.test.ts` under resources/js (Feature
	 * 097). `e2e/` holds Playwright specs, which Vitest must not pick up.
	 */
	test: {
		include: ["resources/js/**/*.test.ts"],
		environment: "jsdom",
	},
	plugins: [
		leafletGlobalPlugin(),
		tailwindcss(),
		// Declares intent (component logic reads this appConfig in places), but the
		// actual --ui-color-* CSS custom properties are populated at runtime by
		// resources/js/v8/theme.ts - this plugin option alone does not materialize
		// them in Vue-standalone mode. See Feature 049 T-049-02.
		ui({
			ui: {
				colors: { primary: "sky", neutral: "slate" },
				card: { variants: { variant: { outline: { root: "ring-0" } } } },
				// Nuxt UI's `text-inverted` flips to a dark neutral in dark mode (so
				// solid buttons stay readable on the lighter shade-400 background it
				// picks for dark mode) - Lychee always wants white text on the primary
				// solid button regardless of color scheme.
				button: { compoundVariants: [{ color: "primary", variant: "solid", class: "text-white" }] },
			},
		}),
		vue({ template: { transformAssetUrls: { base: null, includeAbsolute: false } } }),
		i18n(),
	],
	server: {
		// cors: true, // Worst case scenario
		watch: {
			ignored: [
				"**/.*/**",
				"**/app/**",
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
			"@": fileURLToPath(new URL("./resources/js/", import.meta.url)),
			vue: "vue/dist/vue.runtime.esm-bundler.js",
		},
	},
	optimizeDeps: {
		// @primeuix/themes/types/* and primevue/* subpaths (imported by v7 for
		// DesignTokens/MenuItem types, some via inline `import { type X } from ...`)
		// only expose a `types` export condition, no runtime `import` condition. With
		// two Vite entries (app.ts + app-v8.ts) sharing one dependency scan, the
		// optimizer now eagerly resolves those paths and fails. `vp build` is
		// unaffected (native ESM resolution handles them fine at request time) -
		// this only skips the dev-server pre-bundling scan for them.
		exclude: ["@primeuix/themes", "primevue"],
	},
	build: {
		rollupOptions: {
			// @vueuse/core ships /* #__PURE__ */ comments in positions Rolldown's
			// stricter checker rejects (harmless - just a missed tree-shaking hint),
			// spamming the build output with INVALID_ANNOTATION warnings we can't fix upstream.
			onwarn(warning, warn) {
				if (warning.code === "INVALID_ANNOTATION" && warning.id?.includes("@vueuse/core")) {
					return;
				}
				warn(warning);
			},
			output: {
				// Per-package chunking (rather than a manual named group keyed on a
				// path regex) - with two entries now sharing this build, a regex-based
				// group can end up co-locating shared vendor code (e.g. vue's own
				// runtime helpers) inside a chunk named after one entry's dependency,
				// which then gets pulled into the OTHER entry too. Splitting strictly
				// by node_modules package name keeps every dependency - PrimeVue
				// included - in its own chunk, so app-v8 never references it.
				manualChunks(id) {
					if (id.includes("node_modules")) {
						return id.toString().split("node_modules/")[1].split("/")[0].toString();
					}
				},
			},
		},
	},
} as UserConfig;

function getCorsSettings(env: Record<string, string>) {
	return {
		origin: env.APP_URL ?? "http://localhost",
		methods: "GET",
		preflightContinue: false,
		optionsSuccessStatus: 204,
	};
}

export default defineConfig(({ command, mode }: ConfigEnv): UserConfig => {
	const config = { ...baseConfig };

	if (mode === "development") {
		console.log("DEVELOPMENT MODE detected");
		config.build.sourcemap = true;
		config.build.minify = false; // Ensure no minification in development
		config.define = {
			__VUE_OPTIONS_API__: true,
			__VUE_PROD_DEVTOOLS__: true,
		};
	}
	const env = loadEnv(mode, process.cwd(), "");

	if (config.server === undefined) {
		throw new Error("server config is missing");
	}
	if (config.plugins === undefined) {
		throw new Error("plugins list is missing");
	}

	config.server.cors = getCorsSettings(env);

	if (command === "serve") {
		console.log("LOCAL VITE MODE detected");
		if (env.VITE_HTTP_PROXY_TARGET !== undefined) {
			console.log("api calls will be forwarded to:");
			console.log(env.VITE_HTTP_PROXY_TARGET);
		}

		if (env.VITE_LOCAL_DEV === "true") {
			if (env.VITE_HTTP_PROXY_TARGET === undefined) {
				throw new Error("VITE_HTTP_PROXY_TARGET is missing");
			}
			config.server.open = "vite/index.html";
			config.server.proxy = {
				"/api/": {
					target: env.VITE_HTTP_PROXY_TARGET,
					changeOrigin: true,
					secure: true,
					ws: true,
					configure: (proxy: HttpProxy.Server, _options: ProxyOptions) => {
						proxy.on("error", (err, _req, _res) => {
							console.log("proxy error", err);
						});
						proxy.on("proxyReq", (proxyReq, req, _res) => {
							console.log(req.method?.padEnd(4), "=> ", proxyReq.host + req.url);
						});
						proxy.on("proxyRes", (proxyRes, req, _res) => {
							console.log(proxyRes.statusCode?.toString().padEnd(4), "<=", req.url);
						});
					},
				},
			};
			config.plugins.push(localDevelopMiddleware);
			return config;
		}
	}

	// command === 'build'
	config.plugins.push(laravelPlugin);
	return config;
});
