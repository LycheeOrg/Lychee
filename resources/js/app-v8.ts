/**
 * v8 entry point (Nuxt UI). Served instead of `app.ts` when
 * `Features::active('nuxt_ui')` is true (see Feature 049 / ADR-0006).
 * Kept independent from `app.ts` — v7 is not affected by anything here.
 */
import { createApp } from "vue";
import { createPinia } from "pinia";
import { createRouter, createWebHistory } from "vue-router";
import { routes } from "@/v8/router/routes";
import { i18nVue } from "laravel-vue-i18n";
import AxiosConfig from "@/config/axios-config";
import piniaPluginPersistedstate from "pinia-plugin-persistedstate";
import AppComponent from "@/v8/views/App.vue";
import ui from "@nuxt/ui/vue-plugin";
import { registerIconCollections } from "@/v8/icons";
import "../sass/app-v8.css";

declare global {
	var assets_url: string;
}

const router = createRouter({
	history: createWebHistory(),
	routes,
});

const pinia = createPinia();
pinia.use(piniaPluginPersistedstate);

const langs = import.meta.glob("../../lang/*.json");

const app = createApp(AppComponent);
app.config.globalProperties.window = window;
app.use(pinia);
app.use(router);
app.use(ui);
app.use(i18nVue, {
	resolve: async (lang: string) => {
		const loader = langs[`../../lang/php_${lang}.json`];
		if (!loader) {
			throw new Error(`Missing locale: ${lang}`);
		}
		return await loader();
	},
});

registerIconCollections();

app.mount("#app");

AxiosConfig.axiosSetUp();
