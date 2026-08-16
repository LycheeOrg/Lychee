<template>
	<main id="landing">
		<LandingStudio v-if="initdata && initdata.layout === 'studio'" :data="initdata" />
		<LandingMeridian v-else-if="initdata && initdata.layout === 'meridian'" :data="initdata" />
		<LandingPortfolio v-else-if="initdata && initdata.layout === 'portfolio'" :data="initdata" />
		<LandingClassic v-else-if="initdata" :data="initdata" />
	</main>
</template>
<script setup lang="ts">
import { ref } from "vue";
import { useRouter } from "vue-router";
import InitService from "@/services/init-service";
import LandingClassic from "@/v8/views/landing/LandingClassic.vue";
import LandingPortfolio from "@/v8/views/landing/LandingPortfolio.vue";
import LandingMeridian from "@/v8/views/landing/LandingMeridian.vue";
import LandingStudio from "@/v8/views/landing/LandingStudio.vue";
import { useAppToast } from "@/v8/composables/useAppToast";
import { trans } from "laravel-vue-i18n";

const toast = useAppToast();

const initdata = ref<App.Http.Resources.GalleryConfigs.LandingPageResource | undefined>(undefined);
const router = useRouter();

InitService.fetchLandingData()
	.then((data) => {
		if (data.data.landing_page_enable === false) {
			router.push({ name: "home" });
		} else {
			initdata.value = data.data;
		}
	})
	.catch((e) => {
		toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response?.data?.message, life: 3000 });
		router.push({ name: "home" });
	});
</script>
