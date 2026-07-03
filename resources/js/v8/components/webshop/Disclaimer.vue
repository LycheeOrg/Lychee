<template>
	<div class="w-full mb-8" v-if="options?.is_lycheeorg_disclaimer_enabled && initData?.settings.can_edit">
		<h2 class="w-full text-xl font-bold mb-2 flex items-center gap-2">
			<UIcon name="prime:exclamation-triangle" class="text-warning-600" /> <span>{{ $t("webshop.disclaimer.title") }}</span>
		</h2>
		<p class="text-muted" v-html="$t('webshop.disclaimer.message')"></p>
		<div class="flex ltr:justify-end rtl:justify-start mt-4">
			<UButton :label="$t('webshop.disclaimer.iUnderstand')" icon="prime:check" color="primary" @click="accept" />
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
