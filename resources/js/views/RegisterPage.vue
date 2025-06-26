<template>
	<div class="absolute top-0 left-0">
		<Button icon="pi pi-angle-left" class="mr-2 border-none" severity="secondary" text @click="goBack" />
	</div>
	<Panel class="border-none p-9 mx-auto max-w-3x" pt:content:class="flex flex-col items-center" pt:header:class="hidden" v-if="is_loaded">
		<div class="my-12" v-if="initdata">
			<h1 class="text-center text-2xl text-muted-color-emphasis uppercase font-extralight">
				{{ initdata.landing_title }}
			</h1>
			<h2 class="text-center text-base text-muted-color uppercase font-extralight">
				{{ initdata.landing_subtitle }}
			</h2>
		</div>
		<div class="my-12" v-else>
			<h1 class="text-center text-2xl text-muted-color-emphasis uppercase font-extralight">
				{{ title }}
			</h1>
		</div>
		<div class="w-full max-w-md px-9 text-right">
			<router-link :to="{ name: 'login' }" class="hover:text-primary-emphasis">
				{{ $t("left-menu.login") }} <i class="pi pi-angle-double-right ml-1" />
			</router-link>
		</div>
		<Message v-if="errorMessage" severity="error" class="mb-4 text-center">{{ errorMessage }}</Message>
		<form v-focustrap class="flex flex-col gap-4 relative max-w-md w-full text-sm rounded-md pt-9">
			<div class="inline-flex flex-col gap-4 px-9">
				<FloatLabel variant="on">
					<InputText id="username" v-model="username" autocomplete="username" :autofocus="true" />
					<label for="username">{{ $t("profile.login.username") }}</label>
				</FloatLabel>
				<FloatLabel variant="on">
					<InputText id="email" v-model="email" autocomplete="email" />
					<label for="email">{{ $t("profile.login.email") }}</label>
				</FloatLabel>
				<FloatLabel variant="on">
					<InputPassword id="password" v-model="password" autocomplete="new-password" />
					<label for="password">{{ $t("profile.login.new_password") }}</label>
				</FloatLabel>
				<FloatLabel variant="on">
					<InputPassword id="password_confirmation" v-model="passwordConfirmation" autocomplete="new-password" />
					<label for="password_confirmation">{{ $t("profile.login.confirm_new_password") }}</label>
				</FloatLabel>
				<Message v-if="confirmationError" severity="error" class="text-sm mt-2">
					{{ confirmationError }}
				</Message>
			</div>
			<div class="flex items-center mt-9">
				<Button @click="register" :disabled="!isFormValid" severity="contrast" class="w-full font-bold border-none rounded-xl">
					{{ $t("profile.register.signup") }}
				</Button>
			</div>
		</form>
	</Panel>
</template>

<script setup lang="ts">
import FloatLabel from "primevue/floatlabel";
import Button from "primevue/button";
import InputText from "@/components/forms/basic/InputText.vue";
import InputPassword from "@/components/forms/basic/InputPassword.vue";
import InitService from "@/services/init-service";
import ProfileService from "@/services/profile-service";
import { useLeftMenuStateStore } from "@/stores/LeftMenuState";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import Panel from "primevue/panel";
import { ref, computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import { trans } from "laravel-vue-i18n";
import { useToast } from "primevue/usetoast";
import Message from "primevue/message";
import { useAuthStore } from "@/stores/Auth";
import AlbumService from "@/services/album-service";

const router = useRouter();
const authStore = useAuthStore();
const lycheeStore = useLycheeStateStore();
const leftMenuStore = useLeftMenuStateStore();
const { title } = storeToRefs(lycheeStore);
const is_loaded = ref(false);

const username = ref("");
const email = ref("");
const password = ref("");
const passwordConfirmation = ref("");
const errorMessage = ref("");

const confirmationError = computed(() => {
	return password.value !== passwordConfirmation.value ? trans("profile.register.password_mismatch") : "";
});

const isFormValid = computed(() => {
	return username.value && email.value && password.value && passwordConfirmation.value && !confirmationError.value;
});

function goBack() {
	router.push({ name: "gallery" });
}

const toast = useToast();

let signature = "";
let expires = "";

let search = window.location.search;
if (search.startsWith("?")) {
	search = search.substring(1);
	search.split("&").forEach((param) => {
		const [key, value] = param.split("=");
		if (key === "signature") {
			signature = value;
		} else if (key === "expires") {
			expires = value;
		}
	});
}

function register() {
	ProfileService.register(
		{
			username: username.value,
			email: email.value,
			password: password.value,
			password_confirmation: passwordConfirmation.value,
		},
		signature,
		expires,
	)
		.then(() => {
			errorMessage.value = ""; // Clear error message on success
			toast.add({ severity: "success", summary: trans("profile.register.success"), life: 3000 });
			// Clear the cache to trigger reload of user data
			authStore.setUser(null);
			AlbumService.clearCache();
			router.push({ name: "gallery" }); // Redirect to gallery
		})
		.catch((error) => {
			if (error.response?.status === 409) {
				errorMessage.value = trans("profile.register.username_exists");
			} else {
				errorMessage.value = error.response?.data?.message || trans("profile.register.error");
			}
		});
}

const initdata = ref<App.Http.Resources.GalleryConfigs.LandingPageResource | undefined>(undefined);

onMounted(() => {
	// Close the left menu if it is open
	leftMenuStore.left_menu_open = false;

	Promise.all([lycheeStore.init(), InitService.fetchLandingData()]).then(([_lycheeData, initData]) => {
		is_loaded.value = true;
		if (initData.data.landing_page_enable === true) {
			initdata.value = initData.data;
		}
	});
});
</script>
