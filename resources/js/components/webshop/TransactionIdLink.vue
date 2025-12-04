<template>
	<a
		:class="{
			'line-through': isStale(props.order),
			'cursor-pointer hover:text-primary-400': canOpen(props.order),
			'cursor-not-allowed text-muted-color': !canOpen(props.order),
		}"
		v-tooltip="props.order.transaction_id"
		@click.prevent="openOrder(props.order)"
		>{{ props.order.transaction_id.slice(0, 12) }}</a
	>
	<i
		v-if="props.order.status === 'closed'"
		class="pi pi-copy cursor-pointer hover:text-primary-400 ltr:ml-2 rtl:mr-2"
		@click="copyTransactionIdToClipboard(props.order.transaction_id)"
	/>
</template>
<script setup lang="ts">
import { useOrder } from "@/composables/checkout/useOrder";
import { useToast } from "primevue/usetoast";
import { useRouter } from "vue-router";

const toast = useToast();
const router = useRouter();

const props = defineProps<{
	order: App.Http.Resources.Shop.OrderResource;
}>();

const { isStale, canOpen, openOrder, copyTransactionIdToClipboard } = useOrder(toast, router);
</script>
