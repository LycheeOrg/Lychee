<template>
	<div class="w-full mb-8" v-if="options?.is_lycheeorg_disclaimer_enabled && rights?.settings.can_edit">
		<h2 class="w-full text-xl font-bold mb-2">
			<span class="pi pi-exclamation-triangle text-warning-600"></span> <span>{{ $t("webshop.disclaimer.title") }}</span>
		</h2>
		<p class="text-muted-color" v-html="$t('webshop.disclaimer.message')"></p>
		<div class="flex ltr:justify-end rtl:justify-start mt-4">
			<Button :label="$t('webshop.disclaimer.iUnderstand')" icon="pi pi-check" class="border-none" severity="primary" @click="accept" />
		</div>
	</div>
</template>

<script setup lang="ts">
import { useStepOne } from "@/composables/checkout/useStepOne";
import SettingsService from "@/services/settings-service";
import { useGlobalRights } from "@/composables/useGlobalRights";
import { useOrderManagementStore } from "@/stores/OrderManagement";
import { useUserStore } from "@/stores/UserState";
import Button from "primevue/button";
import { onMounted } from "vue";

const userStore = useUserStore();
const orderStore = useOrderManagementStore();
const { options, loadCheckoutOptions } = useStepOne(userStore, orderStore);
const { rights } = useGlobalRights();

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
	if (options.value === undefined) {
		loadCheckoutOptions();
	}
});
</script>
