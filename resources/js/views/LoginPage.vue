<template>
	<WebauthnModal @logged-in="goBack" />
	<div class="absolute top-0 left-0">
		<Button icon="pi pi-angle-left" class="mr-2 border-none" severity="secondary" text @click="goBack" />
	</div>
	<Panel class="border-none p-9 mx-auto max-w-3x" pt:content:class="flex flex-col items-center" pt:header:class="hidden" v-if="is_loaded">
		<div class="my-12" v-if="initdata">
			<h1 class="text-center text-2xl text-surface-0 uppercase font-extralight">
				{{ initdata.landing_title }}
			</h1>
			<h2 class="text-center text-base text-muted-color uppercase font-extralight">
				{{ initdata.landing_subtitle }}
			</h2>
		</div>
		<div class="my-12" v-else>
			<h1 class="text-center text-2xl text-surface-0 uppercase font-extralight">
				{{ title }}
			</h1>
		</div>
		<LoginForm @logged-in="goBack" padding="" />
		<div v-if="is_registration_enabled" class="text-center mt-4">
			<router-link to="/register" class="text-muted-color-emphasis text-sm font-bold hover:underline">
				{{ $t("profile.register.signup") }}
			</router-link>
		</div>
	</Panel>
</template>
<script setup lang="ts">
import LoginForm from "@/components/forms/auth/LoginForm.vue";
import WebauthnModal from "@/components/modals/WebauthnModal.vue";
import InitService from "@/services/init-service";
import { useLeftMenuStateStore } from "@/stores/LeftMenuState";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import Button from "primevue/button";
import Panel from "primevue/panel";
import { ref } from "vue";
import { onMounted } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();
const lycheeStore = useLycheeStateStore();
const leftMenuStore = useLeftMenuStateStore();
const { title, is_registration_enabled } = storeToRefs(lycheeStore);
const is_loaded = ref(false);

function goBack() {
	router.push({ name: "gallery" });
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
