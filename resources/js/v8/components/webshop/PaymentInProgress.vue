<template>
	<div class="w-full flex flex-col p-8 bg-elevated/50 rounded border border-default justify-center">
		<div class="text-error font-bold text-center mb-8">{{ $t("webshop.paymentInProgress.message") }}</div>
		<UButton
			color="neutral"
			variant="soft"
			:label="$t('webshop.paymentInProgress.cancel')"
			class="w-1/2 mx-auto justify-center"
			@click="cancelPayment"
		/>
	</div>
</template>
<script setup lang="ts">
import WebshopService from "@/services/webshop-service";
import { useOrderManagementStore } from "@/stores/OrderManagement";

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
