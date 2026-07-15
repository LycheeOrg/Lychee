<template>
	<div class="absolute top-0 left-0">
		<UButton icon="lucide:chevron-left" class="mr-2" color="neutral" variant="ghost" @click="goBack" />
	</div>
	<UCard v-if="is_loaded" class="mx-auto max-w-3xl" :ui="{ header: 'hidden', body: 'flex flex-col items-center' }">
		<div v-if="initdata" class="my-12">
			<h1 class="text-center text-2xl text-highlighted uppercase font-extralight">
				{{ initdata.landing_title }}
			</h1>
			<h2 class="text-center text-base text-muted uppercase font-extralight">
				{{ initdata.landing_subtitle }}
			</h2>
		</div>
		<div v-else class="my-12">
			<h1 class="text-center text-2xl text-highlighted uppercase font-extralight">
				{{ title }}
			</h1>
		</div>
		<div class="w-full max-w-md text-right">
			<router-link :to="{ name: 'login' }" class="hover:text-primary/80">
				{{ $t("left-menu.login") }} <UIcon name="lucide:chevrons-right" class="ml-1" />
			</router-link>
		</div>
		<UAlert v-if="errorMessage" color="error" variant="soft" class="mb-4 text-center" :description="errorMessage" />
		<form class="flex flex-col gap-4 relative max-w-md w-full text-sm rounded-md pt-9">
			<div class="inline-flex flex-col gap-4">
				<UFormField :label="$t('profile.login.username')">
					<UInput id="username" v-model="username" autocomplete="username" :autofocus="true" class="w-full" />
				</UFormField>
				<UFormField :label="$t('profile.login.email')">
					<UInput id="email" v-model="email" autocomplete="email" class="w-full" />
				</UFormField>
				<UFormField :label="$t('profile.login.new_password')">
					<InputPassword id="password" v-model="password" autocomplete="new-password" />
				</UFormField>
				<UFormField :label="$t('profile.login.confirm_new_password')">
					<InputPassword id="password_confirmation" v-model="passwordConfirmation" autocomplete="new-password" />
				</UFormField>
				<UAlert v-if="confirmationError" color="error" variant="soft" class="text-sm mt-2" :description="confirmationError" />
			</div>
			<div class="flex items-center mt-9">
				<UButton :disabled="!isFormValid" color="neutral" class="w-full font-bold justify-center" @click="register">
					{{ $t("profile.register.signup") }}
				</UButton>
			</div>
		</form>
	</UCard>
</template>

<script setup lang="ts">
import InputPassword from "@/v8/components/forms/basic/InputPassword.vue";
import InitService from "@/services/init-service";
import ProfileService from "@/services/profile-service";
import { useLeftMenuStateStore } from "@/stores/LeftMenuState";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import { ref, computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import { trans } from "laravel-vue-i18n";
import { useAppToast } from "@/v8/composables/useAppToast";
import { useUserStore } from "@/stores/UserState";
import AlbumService from "@/services/album-service";

const router = useRouter();
const userStore = useUserStore();
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

const toast = useAppToast();

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
			userStore.setUser(undefined);
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

	Promise.all([lycheeStore.load(), InitService.fetchLandingData()]).then(([_lycheeData, initData]) => {
		is_loaded.value = true;
		if (initData.data.landing_page_enable === true) {
			initdata.value = initData.data;
		}
	});
});
</script>
