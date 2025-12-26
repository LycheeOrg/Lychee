/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */
import { createApp } from "vue";
import { createPinia } from "pinia";
import PrimeVue from "primevue/config";
import Ripple from "primevue/ripple";
import { createRouter, createWebHistory } from "vue-router";
import { routes } from "@/router/routes";
import Aura from "@primeuix/themes/aura";
import { i18nVue } from "laravel-vue-i18n";
import ToastService from "primevue/toastservice";
import ConfirmationService from "primevue/confirmationservice";
import AxiosConfig from "@/config/axios-config";
import piniaPluginPersistedstate from "pinia-plugin-persistedstate";
import AppComponent from "@/views/App.vue";
import { definePreset } from "@primeuix/themes";
import LycheePrimeVueConfig from "./style/preset";
import FocusTrap from "primevue/focustrap";
import Tooltip from "primevue/tooltip";
import "../sass/app.css";

declare global {
	var assets_url: string;
}

const router = createRouter({
	history: createWebHistory(),
	routes,
});

const LycheePreset = definePreset(Aura, LycheePrimeVueConfig);

const pinia = createPinia();
pinia.use(piniaPluginPersistedstate);

const langs = import.meta.glob("../../lang/*.json");

/**
 * Next, we will create a fresh Vue application instance. You may then begin
 * registering components with the application instance so they are ready
 * to use in your application's views. An example is included for you.
 */
const app = createApp({});
app.config.globalProperties.window = window;
app.use(pinia);
app.use(PrimeVue, {
	ripple: true,
	theme: {
		preset: LycheePreset,
		options: {
			// cssLayer: false,
			cssLayer: {
				name: "primevue",
				order: "base, primevue",
			},
			darkModeSelector: ".dark",
		},
	},
});
app.directive("ripple", Ripple);
app.directive("focustrap", FocusTrap);
app.directive("tooltip", Tooltip);

app.component("App", AppComponent);
app.use(router);
app.use(ToastService);
app.use(ConfirmationService);
app.use(i18nVue, {
	resolve: async (lang: string) => {
		const loader = langs[`../../lang/php_${lang}.json`];
		if (!loader) {
			throw new Error(`Missing locale: ${lang}`);
		}
		return await loader();
	},
});

/**
 * Finally, we will attach the application instance to a HTML element with
 * an "id" attribute of "app".
 */
app.mount("#app");

AxiosConfig.axiosSetUp();
