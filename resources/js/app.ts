/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */
import axios from "axios";
import { createApp } from "vue";
import { createPinia } from "pinia";
import PrimeVue from "primevue/config";
import Ripple from "primevue/ripple";
import { createRouter, createWebHistory } from "vue-router";
import { routes } from "@/router/routes";
import Aura from "@primevue/themes/aura";
import { i18nVue } from "laravel-vue-i18n";
import ToastService from "primevue/toastservice";
import AxiosConfig from "@/config/axios-config";
import AppComponent from "@/views/App.vue";
import { definePreset } from "@primevue/themes";
import LycheePrimeVueConfig from "./style/preset";
import FocusTrap from "primevue/focustrap";
import Tooltip from "primevue/tooltip";
import "../sass/app.css";

declare global {
	var assets_url: string;
}

// @ts-expect-error
window.axios = axios;
// @ts-expect-error
window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

const router = createRouter({
	history: createWebHistory(),
	routes,
});

const LycheePreset = definePreset(Aura, LycheePrimeVueConfig);

const pinia = createPinia();

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

app.component("app", AppComponent);
app.use(router);
app.use(ToastService);

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

app.use(i18nVue, {
	resolve: async (lang: string) => {
		if (!lang.startsWith("php")) {
			lang = "php_" + lang;
		}
		// @ts-expect-error
		const langs = import.meta.glob("../../lang/*.json");
		return await langs[`../../lang/${lang}.json`]();
	},
});

/**
 * Finally, we will attach the application instance to a HTML element with
 * an "id" attribute of "app".
 */
app.mount("#app");

AxiosConfig.axiosSetUp();
