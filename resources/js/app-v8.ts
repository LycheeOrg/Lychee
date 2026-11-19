/**
 * v8 entry point (Nuxt UI). Served instead of `app.ts` when
 * `Features::active('v8')` is true (`V8_ENABLED`, Feature 109).
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
import { applyStoredDarkModePreference } from "@/v8/composables/useDarkMode";
import { resolveLocale } from "@/v8/i18n";
import { useLycheeStateStore } from "@/stores/LycheeState";
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

const app = createApp(AppComponent);
app.config.globalProperties.window = window;
app.use(pinia);
// v8 runs its listings on the v3 struct-of-arrays endpoints only; the shared
// stores keep their v2 path for v7 (Feature 109, FR-109-05).
useLycheeStateStore(pinia).is_struct_of_array_enabled = true;
app.use(router);
app.use(ui);
app.use(i18nVue, { resolve: resolveLocale });

registerIconCollections();
applyStoredDarkModePreference();

AxiosConfig.axiosSetUp();

app.mount("#app");
