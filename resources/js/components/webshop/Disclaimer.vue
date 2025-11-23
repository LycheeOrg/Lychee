<template>
	<div class="w-full mb-8" v-if="options?.is_lycheeorg_disclaimer_enabled && initData?.settings.can_edit">
		<h2 class="w-full text-xl font-bold mb-2"><span class="pi pi-exclamation-triangle text-warning-600"></span> <span>Disclaimer</span></h2>
		<p class="text-muted-color">
			Lychee is developed under the <a href="https://lycheeorg.dev/license" class="text-primary-400">MIT license</a>. This means that
			<span class="text-muted-color-emphasis">LycheeOrg is not responsible</span> nor liable <span class="text-muted-color-emphasis">for any issues or losses</span> arising from the use of the webshop
			module and/or the payment processing capabilities. It is critical that you verify and
			<span class="text-muted-color-emphasis"
				>ensure that your setup is working correctly and securely before using it in a production environment.</span
			>
		</p>
		<div class="flex ltr:justify-end rtl:justify-start mt-4">
			<Button label="I understand" icon="pi pi-check" class="border-none" severity="primary" @click="accept" />
		</div>
	</div>
</template>

<script setup lang="ts">
import { useStepOne } from "@/composables/checkout/useStepOne";
import InitService from "@/services/init-service";
import SettingsService from "@/services/settings-service";
import { useLeftMenuStateStore } from "@/stores/LeftMenuState";
import { useOrderManagementStore } from "@/stores/OrderManagement";
import { useUserStore } from "@/stores/UserState";
import { storeToRefs } from "pinia";
import Button from "primevue/button";
import { onMounted } from "vue";

const userStore = useUserStore();
const orderStore = useOrderManagementStore();
const leftMenuStore = useLeftMenuStateStore();
const { options, loadCheckoutOptions } = useStepOne(userStore, orderStore);
const { initData } = storeToRefs(leftMenuStore);

async function load(): Promise<void> {
	return InitService.fetchGlobalRights().then((data) => {
		initData.value = data.data;
	});
}

function accept() {
	SettingsService.setConfigs({
		configs: [
			{
				key: "webshop_lycheeorg_disclaimer_enabled",
				value: "0",
			},
		],
	}).then(() => {
		options.value!.is_lycheeorg_disclaimer_enabled = false;
	});
}

onMounted(() => {
	if (initData.value === undefined) {
		load();
	}

	if (options.value === undefined) {
		loadCheckoutOptions();
	}
});
</script>
