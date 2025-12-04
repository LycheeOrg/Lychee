<template>
	<div
		class="w-full flex flex-col p-8 bg-surface-50 dark:bg-surface-950/25 rounded border border-surface-200 dark:border-surface-700 justify-center"
	>
		<div class="text-red-600 font-bold text-center mb-8">{{ $t("webshop.paymentInProgress.message") }}</div>
		<Button severity="secondary" :label="$t('webshop.paymentInProgress.cancel')" class="w-1/2 mx-auto" @click="cancelPayment" />
	</div>
</template>
<script setup lang="ts">
import WebshopService from "@/services/webshop-service";
import { useOrderManagementStore } from "@/stores/OrderManagement";
import Button from "primevue/button";

const orderManagementStore = useOrderManagementStore();

function cancelPayment() {
	if (orderManagementStore.order === undefined) {
		return;
	}

	WebshopService.Checkout.cancelPayment(orderManagementStore.order.transaction_id).then((response) => {
		const order = response.data.order;
		if (order !== null) {
			orderManagementStore.order = order;
		} else {
			orderManagementStore.refresh();
		}
	});
}
</script>
