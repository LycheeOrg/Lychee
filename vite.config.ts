import { fileURLToPath, URL } from "node:url";
import { ConfigEnv, defineConfig, loadEnv, PluginOption, UserConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import i18n from "laravel-vue-i18n/vite";
import tailwindcss from "@tailwindcss/vite";

const laravelPlugin = laravel({
	input: ["resources/sass/app.css", "resources/js/app.ts"],
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

      console.log(req.url);
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
		tailwindcss(),
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
			vue: "vue/dist/vue.esm-bundler.js",
		},
	},
	build: {
		rollupOptions: {
			output: {
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
	const config = baseConfig;
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
				"/api/": env.VITE_HTTP_PROXY_TARGET,
			};
			config.plugins.push(localDevelopMiddleware);
			return config;
		}
	}

	// command === 'build'
	config.plugins.push(laravelPlugin);
	return config;
});
