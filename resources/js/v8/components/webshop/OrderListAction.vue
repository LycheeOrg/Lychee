<template>
	<!-- Mark as paid if in offline state -->
	<template v-if="rights?.settings.can_edit">
		<UButton
			v-if="props.order.status === 'offline'"
			:label="$t('webshop.orderListAction.markAsPaid')"
			icon="lucide:check"
			variant="ghost"
			color="neutral"
			@click="markAsPaid(props.order.id)"
		/>
		<UButton
			v-else-if="requireAttention(props.order)"
			:label="$t('webshop.orderListAction.requireAttention')"
			icon="lucide:triangle-alert"
			variant="ghost"
			color="warning"
			@click="
				() => {
					router.push({ name: 'order', params: { orderId: props.order.id } });
				}
			"
		/>
		<UButton
			v-else-if="props.order.status === 'completed'"
			:label="$t('webshop.orderListAction.markAsDelivered')"
			icon="lucide:check"
			variant="ghost"
			color="neutral"
			@click="markAsDelivered(props.order.id)"
		/>
	</template>
	<UButton
		v-if="props.order.status === 'closed' && !requireAttention(props.order)"
		:label="$t('webshop.orderListAction.viewDetails')"
		icon="lucide:eye"
		variant="ghost"
		color="primary"
		@click="
			() => {
				router.push({ name: 'order', params: { orderId: props.order.id } });
			}
		"
	/>
</template>
<script setup lang="ts">
import { useOrder } from "@/composables/checkout/useOrder";
import { useGlobalRights } from "@/composables/useGlobalRights";
import { useAppToast } from "@/v8/composables/useAppToast";
import { useRouter } from "vue-router";

const props = defineProps<{
	order: App.Http.Resources.Shop.OrderResource;
}>();
const router = useRouter();
const toast = useAppToast();

const { rights } = useGlobalRights();

const { markAsPaid, markAsDelivered, requireAttention } = useOrder(toast, router);
</script>
