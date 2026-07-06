<template>
	<span v-if="props.order.status === 'closed' && props.order.updated_at" class="text-create-600">
		{{ new Date(props.order.updated_at).toLocaleString() }}
	</span>
	<span v-else-if="props.order.status === 'completed' && props.order.paid_at" class="text-primary-600" @click="markAsDelivered(props.order.id)">
		{{ new Date(props.order.paid_at).toLocaleString() }}</span
	>
	<span v-else-if="props.order.status === 'offline' && props.order.created_at" class="text-warning-600" @click="markAsPaid(props.order.id)">
		{{ new Date(props.order.created_at).toLocaleString() }}
	</span>
	<span v-else-if="isStale(props.order) && props.order.created_at" class="text-muted-color">
		{{ new Date(props.order.created_at).toLocaleString() }}</span
	>
	<span v-else-if="props.order.created_at" class="text-muted-color-emphasis">{{ new Date(props.order.created_at).toLocaleString() }}</span>
	<span v-else>{{ $t("webshop.errors.noData") }}</span>
</template>
<script setup lang="ts">
import { useOrder } from "@/composables/checkout/useOrder";
import { useToast } from "primevue/usetoast";
import { useRouter } from "vue-router";

const props = defineProps<{
	order: App.Http.Resources.Shop.OrderResource;
}>();

const toast = useToast();
const router = useRouter();
const { markAsPaid, markAsDelivered, isStale } = useOrder(toast, router);
</script>
