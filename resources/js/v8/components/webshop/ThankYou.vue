<template>
	<div class="border-2 border-dashed border-default rounded bg-elevated/50 flex-col gap-4 p-4 flex justify-center items-center font-medium">
		<h2 class="font-bold text-xl">{{ $t("webshop.checkout.thankYou") }}</h2>
		<div class="text-muted">
			<p>
				{{ $t("webshop.checkout.orderNumber") }} <strong class="text-highlighted">{{ order?.id }}</strong>
			</p>
			<p>
				{{ $t("webshop.checkout.transactionId") }}
				<strong class="text-highlighted">{{ order?.transaction_id }}</strong>
			</p>
		</div>
		<div class="text-muted text-center">
			<p class="text-muted">
				<UIcon name="lucide:triangle-alert" class="ltr:mr-2 rtl:mr-2 text-warning" />
				{{ $t("webshop.checkout.noteWarning") }}
				<strong class="text-highlighted">{{ $t("webshop.checkout.noteTransactionId") }}</strong>
				{{ $t("webshop.checkout.noteOrderNumber") }}
			</p>
		</div>
		<QrCodeLink :url="url" />
		<p class="text-muted">
			{{ $t("webshop.checkout.noteReason") }}
		</p>
		<p class="text-muted">{{ $t("webshop.checkout.enjoyPurchase") }}</p>
		<UButton
			v-if="order?.status === 'closed'"
			variant="ghost"
			:label="$t('webshop.checkout.toMyDownloads')"
			icon="lucide:link"
			@click="openOrderPage"
		/>
	</div>
</template>
<script setup lang="ts">
import { useOrderManagementStore } from "@/stores/OrderManagement";
import { storeToRefs } from "pinia";
import { useRouter } from "vue-router";
import QrCodeLink from "./QrCodeLink.vue";
import { computed } from "vue";
import Constants from "@/services/constants";

const orderStore = useOrderManagementStore();
const { order } = storeToRefs(orderStore);
const router = useRouter();

function openOrderPage() {
	const orderId = order.value?.id;
	const transactionId = order.value?.transaction_id;
	orderStore.forget();
	router.push({ name: "order", params: { orderId: orderId, transactionId: transactionId } });
}

const url = computed(() => {
	return (
		Constants.BASE_URL + router.resolve({ name: "order", params: { orderId: order.value?.id, transactionId: order.value?.transaction_id } }).href
	);
});
</script>
