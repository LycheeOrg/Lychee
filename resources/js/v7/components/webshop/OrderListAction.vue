<template>
	<!-- Mark as paid if in offline state -->
	<template v-if="rights?.settings.can_edit">
		<Button
			v-if="props.order.status === 'offline'"
			:label="$t('webshop.orderListAction.markAsPaid')"
			icon="pi pi-check"
			class="border-none py-0"
			severity="secondary"
			text
			@click="markAsPaid(props.order.id)"
		/>
		<Button
			v-else-if="requireAttention(props.order)"
			:label="$t('webshop.orderListAction.requireAttention')"
			icon="pi pi-exclamation-triangle"
			class="border-none py-0"
			severity="warn"
			text
			@click="router.push({ name: 'order', params: { orderId: props.order.id } })"
		/>
		<Button
			v-else-if="props.order.status === 'completed'"
			:label="$t('webshop.orderListAction.markAsDelivered')"
			icon="pi pi-check"
			class="border-none py-0"
			severity="secondary"
			text
			@click="markAsDelivered(props.order.id)"
		/>
	</template>
	<Button
		v-if="props.order.status === 'closed' && !requireAttention(props.order)"
		:label="$t('webshop.orderListAction.viewDetails')"
		icon="pi pi-eye"
		class="border-none py-0"
		severity="primary"
		text
		@click="router.push({ name: 'order', params: { orderId: props.order.id } })"
	/>
</template>
<script setup lang="ts">
import { useOrder } from "@/composables/checkout/useOrder";
import { useGlobalRights } from "@/composables/useGlobalRights";
import Button from "primevue/button";
import { useToast } from "primevue/usetoast";
import { useRouter } from "vue-router";

const props = defineProps<{
	order: App.Http.Resources.Shop.OrderResource;
}>();
const router = useRouter();
const toast = useToast();

const { rights } = useGlobalRights();

const { markAsPaid, markAsDelivered, requireAttention } = useOrder(toast, router);
</script>
