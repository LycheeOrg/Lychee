<template>
	<WebauthnModal @logged-in="goBack" />
	<div class="absolute top-0 left-0">
		<UButton icon="lucide:chevron-left" class="mr-2" color="neutral" variant="ghost" @click="goBack" />
	</div>
	<UCard v-if="is_loaded" class="p-9 mx-auto max-w-3xl" :ui="{ header: 'hidden', body: 'flex flex-col items-center' }">
		<div v-if="initdata" class="my-12">
			<template v-if="initdata.landing_logo !== ''">
				<img :src="initdata.landing_logo" alt="logo" class="max-h-24 max-w-xs object-contain mx-auto" />
			</template>
			<template v-else>
				<h1 class="text-center text-2xl text-white uppercase font-extralight">
					{{ initdata.landing_title }}
				</h1>
				<h2 class="text-center text-base text-muted uppercase font-extralight">
					{{ initdata.landing_subtitle }}
				</h2>
			</template>
		</div>
		<div v-else class="my-12">
			<template v-if="lycheeStore.site_logo !== ''">
				<img :src="lycheeStore.site_logo" alt="logo" class="max-h-24 max-w-xs object-contain mx-auto" />
			</template>
			<template v-else>
				<h1 class="text-center text-2xl text-white uppercase font-extralight">
					{{ title }}
				</h1>
			</template>
		</div>
		<LoginForm padding="" @logged-in="goBack" />
		<UButton color="neutral" variant="soft" class="w-full max-w-md font-bold justify-center" @click="goBack">
			{{ $t("dialogs.button.cancel") }}
		</UButton>
		<div v-if="is_registration_enabled && is_basic_auth_enabled" class="text-center mt-4">
			<router-link to="/register" class="text-highlighted text-sm font-bold hover:underline">
				{{ $t("profile.register.signup") }}
			</router-link>
		</div>
		<div v-if="!is_white_label_enabled" class="text-muted text-right font-semibold mt-8">
			Lychee <span v-if="is_se_enabled" class="text-primary-500">SE</span>
		</div>
	</UCard>
</template>
<script setup lang="ts">
import LoginForm from "@/v8/components/forms/auth/LoginForm.vue";
import WebauthnModal from "@/v8/components/modals/WebauthnModal.vue";
import InitService from "@/services/init-service";
import { useLeftMenuStateStore } from "@/stores/LeftMenuState";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useUserStore } from "@/stores/UserState";
import { useAdvisoryModal } from "@/composables/modals/useAdvisoryModal";
import { storeToRefs } from "pinia";
import { ref } from "vue";
import { onMounted } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();
const lycheeStore = useLycheeStateStore();
const leftMenuStore = useLeftMenuStateStore();
const userStore = useUserStore();
const { advisoryCheck } = useAdvisoryModal();
const { title, is_registration_enabled, is_basic_auth_enabled, is_white_label_enabled, is_se_enabled } = storeToRefs(lycheeStore);
const is_loaded = ref(false);

async function goBack() {
	await Promise.allSettled([lycheeStore.load(), userStore.refresh()]);
	advisoryCheck();
	router.push({ name: "gallery" });
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
