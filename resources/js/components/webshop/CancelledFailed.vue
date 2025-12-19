<template>
	<div
		v-if="order?.status === 'cancelled'"
		class="border-2 border-dashed border-surface-200 dark:border-surface-700 rounded bg-surface-50 dark:bg-surface-950 flex-col gap-4 p-4 flex justify-center items-center font-medium"
	>
		<h2 class="font-bold text-xl">{{ $t("webshop.cancelledFailed.paymentCancelled") }}</h2>
		<div class="text-muted-color">
			<p>
				{{ $t("webshop.cancelledFailed.paymentCancelledMessage") }}
			</p>
		</div>
	</div>
	<div v-else-if="order?.status === 'failed'">
		<h2 class="font-bold text-xl">{{ $t("webshop.cancelledFailed.paymentFailed") }}</h2>
		<div class="text-muted-color">
			<p>
				{{ $t("webshop.cancelledFailed.paymentFailedMessage") }}
			</p>
		</div>
	</div>
</template>
<script setup lang="ts">
import { useOrderManagementStore } from "@/stores/OrderManagement";
import { storeToRefs } from "pinia";

const orderStore = useOrderManagementStore();
const { order } = storeToRefs(orderStore);
</script>
