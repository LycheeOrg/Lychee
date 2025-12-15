<template>
	<div
		class="border-2 border-dashed border-surface-200 dark:border-surface-700 rounded bg-surface-50 dark:bg-surface-950 flex-col gap-4 p-4 flex justify-center items-center font-medium"
	>
		<h2 class="font-bold text-xl">{{ $t("webshop.checkout.thankYou") }}</h2>
		<div class="text-muted-color">
			<p>
				{{ $t("webshop.checkout.orderNumber") }} <strong class="text-muted-color-emphasis">{{ order?.id }}</strong>
			</p>
			<p>
				{{ $t("webshop.checkout.transactionId") }}
				<strong class="text-muted-color-emphasis">{{ order?.transaction_id }}</strong>
			</p>
		</div>
		<div class="text-muted-color text-center">
			<p class="text-muted-color">
				<i class="pi pi-exclamation-triangle ltr:mr-2 rtl:mr-2 text-warning-600" />
				{{ $t("webshop.checkout.noteWarning") }}
				<strong class="text-muted-color-emphasis">{{ $t("webshop.checkout.noteTransactionId") }}</strong>
				{{ $t("webshop.checkout.noteOrderNumber") }}
			</p>
		</div>
		<QrCodeLink :url="url" />
		<p class="text-muted-color">
			{{ $t("webshop.checkout.noteReason") }}
		</p>
		<p class="text-muted-color">{{ $t("webshop.checkout.enjoyPurchase") }}</p>
		<Button
			v-if="order?.status === 'closed'"
			text
			:label="$t('webshop.checkout.toMyDownloads')"
			icon="pi pi-link"
			@click="openOrderPage"
			class="border-none"
		/>
	</div>
</template>
<script setup lang="ts">
import { useOrderManagementStore } from "@/stores/OrderManagement";
import { storeToRefs } from "pinia";
import { useRouter } from "vue-router";
import QrCodeLink from "./QrCodeLink.vue";
import { computed } from "vue";
import Button from "primevue/button";
import Constants from "@/services/constants";

const orderStore = useOrderManagementStore();
const { order } = storeToRefs(orderStore);
const router = useRouter();

function openOrderPage() {
	router.push({ name: "order", params: { orderId: order.value?.id, transactionId: order.value?.transaction_id } });
}

const url = computed(() => {
	return (
		Constants.BASE_URL + router.resolve({ name: "order", params: { orderId: order.value?.id, transactionId: order.value?.transaction_id } }).href
	);
});
</script>
