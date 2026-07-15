<template>
	<UTooltip :text="props.order.transaction_id">
		<a
			:class="{
				'line-through': isStale(props.order),
				'cursor-pointer hover:text-primary-400': canOpen(props.order),
				'cursor-not-allowed text-muted': !canOpen(props.order),
			}"
			@click.prevent="openOrder(props.order)"
			>{{ props.order.transaction_id.slice(0, 12) }}</a
		>
	</UTooltip>
	<UIcon
		v-if="props.order.status === 'closed'"
		name="lucide:copy"
		class="cursor-pointer hover:text-primary-400 ltr:ml-2 rtl:mr-2"
		@click="copyTransactionIdToClipboard(props.order.transaction_id)"
	/>
</template>
<script setup lang="ts">
import { useOrder } from "@/composables/checkout/useOrder";
import { useAppToast } from "@/v8/composables/useAppToast";
import { useRouter } from "vue-router";

const toast = useAppToast();
const router = useRouter();

const props = defineProps<{
	order: App.Http.Resources.Shop.OrderResource;
}>();

const { isStale, canOpen, openOrder, copyTransactionIdToClipboard } = useOrder(toast, router);
</script>
