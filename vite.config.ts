import { fileURLToPath, URL } from "node:url";
import { ConfigEnv, defineConfig, HttpProxy, loadEnv, PluginOption, ProxyOptions, UserConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import i18n from "laravel-vue-i18n/vite";
import tailwindcss from "@tailwindcss/vite";
import ui from "@nuxt/ui/vite";
import type { Plugin } from 'vite';


function leafletGlobalPlugin(): Plugin {
  const LEAFLET_PLUGINS = [
    'leaflet-gpx',
    'leaflet-rotatedmarker',
	'leaflet.markercluster',
  ];
  return {
    name: 'vite-plugin-leaflet-global',
    transform(code, id) {
      if (
        LEAFLET_PLUGINS.some((p) => id.includes(p)) &&
        !id.endsWith('.css') &&
        !id.includes('?vue&type=style')
      ) {
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

    server.middlewares.use((req, res, next) => {
      if (!req.url) {
        next();
        return;
      }

      const requestUrl : string = req.url;
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
}

const baseConfig = {
	base: "./",
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
			// @ts-ignore-next-line
			"@": fileURLToPath(new URL("./resources/js/", import.meta.url)),
			vue: "vue/dist/vue.runtime.esm-bundler.js",
		},
	},
	optimizeDeps: {
		// @primeuix/themes/types/* and primevue/* subpaths (imported by v7 for
		// DesignTokens/MenuItem types, some via inline `import { type X } from ...`)
		// only expose a `types` export condition, no runtime `import` condition. With
		// two Vite entries (app.ts + app-v8.ts) sharing one dependency scan, the
		// optimizer now eagerly resolves those paths and fails. `npm run build` is
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
		"origin": env.APP_URL ?? 'http://localhost',
		"methods": "GET",
		"preflightContinue": false,
		"optionsSuccessStatus": 204
	};
}

/** @type {import('vite').UserConfig} */
export default defineConfig(({ command, mode, isSsrBuild, isPreview } : ConfigEnv) : UserConfig => {
	const config = { ...baseConfig };

	if (mode === 'development') {
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
				"/api/":  {
					target: env.VITE_HTTP_PROXY_TARGET,
					changeOrigin: true,
					secure: true,
					ws: true,
					configure: (proxy: HttpProxy.Server, _options: ProxyOptions) => {
					  proxy.on('error', (err, _req, _res) => {
						console.log('proxy error', err);
					  });
					  proxy.on('proxyReq', (proxyReq, req, _res) => {
						console.log(req.method?.padEnd(4), '=> ', proxyReq.host + req.url);
					  });
					  proxy.on('proxyRes', (proxyRes, req, _res) => {
						console.log(proxyRes.statusCode?.toString().padEnd(4), '<=', req.url);
					  });
					},
				}
			};
			config.plugins.push(localDevelopMiddleware);
			return config;
		}
	}

	// command === 'build'
	config.plugins.push(laravelPlugin);
	return config;
});
