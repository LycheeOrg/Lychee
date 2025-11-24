<template>
	<Toolbar class="w-full border-0 h-14 rounded-none">
		<template #start>
			<OpenLeftMenu />
		</template>

		<template #center>
			{{ "Orders" }}
		</template>

		<template #end> </template>
	</Toolbar>
	<div class="text-center lg:hidden font-bold text-danger-700 py-3" v-html="$t('settings.small_screen')"></div>
	<Panel :pt:header:class="'hidden'" class="border-0 md:max-w-3xl lg:max-w-5xl xl:max-w-7xl mt-8 mx-auto w-full">
		<div v-if="numOldOrders > 0" class="flex justify-center items-center gap-4 mb-8">
			<p>Number of stale orders: {{ numOldOrders }}</p>
			<Button label="Clean stale orders" icon="pi pi-trash" class="border-none" severity="warn" @click="clean" />
		</div>
		<Disclaimer />
		<OrderLegend />
		<!-- Empty panel to keep the same layout as other settings pages -->
		<DataTable :value="orders" :loading="orders === undefined" class="mt-4" dataKey="id">
			<Column header="Client" header-class="w-3/12" body-class="w-3/12 align-top">
				<template #body="slotProps">
					<UsernameEmail :username="slotProps.data.username" :email="slotProps.data.email" />
				</template>
			</Column>
			<Column
				header="Transaction ID"
				field="transaction_id"
				header-class="w-2/12"
				body-class="w-2/12 align-top"
				v-if="initData?.settings.can_edit"
			>
				<template #body="slotProps">
					<a
						:class="{
							'line-through': isStale(slotProps.data),
							'cursor-pointer hover:text-primary-400': canOpen(slotProps.data),
							'cursor-not-allowed text-muted-color': !canOpen(slotProps.data),
						}"
						v-tooltip="slotProps.data.transaction_id"
						@click.prevent="openOrder(slotProps.data)"
						>{{ slotProps.data.transaction_id.slice(0, 12) }}</a
					>
					<i
						v-if="slotProps.data.status === 'closed'"
						class="pi pi-copy cursor-pointer hover:text-primary-400 ltr:ml-2 rtl:mr-2"
						@click="copyTransactionIdToClipboard(slotProps.data.transaction_id)"
					/>
				</template>
			</Column>
			<Column header="Status" field="status" header-class="w-1/24" body-class="w-1/12 align-top">
				<template #body="slotProps">
					<OrderStatus :status="slotProps.data.status" />
				</template>
			</Column>
			<Column header="Amount" field="amount" header-class="w-1/12" body-class="w-1/12 align-top">
				<template #body="slotProps">
					<span :class="isZero(slotProps.data.amount) ? 'text-muted-color' : 'font-bold'">{{ slotProps.data.amount }}</span>
				</template>
			</Column>
			<!-- export type PaymentStatusType = "pending" | "cancelled" | "failed" | "refunded" | "processing" | "offline" | "completed" | "closed"; -->
			<Column header="" header-class="w-2/12 ltr:text-right rtl:text-left" body-class="w-2/12 align-top ltr:text-right rtl:text-left">
				<template #body="slotProps">
					<span v-if="slotProps.data.status === 'closed'" class="text-create-600">
						{{ new Date(slotProps.data.updated_at).toLocaleString() }}
					</span>
					<span v-else-if="slotProps.data.status === 'completed'" class="text-primary-600" @click="markAsDelivered(slotProps.data.id)">
						{{ new Date(slotProps.data.paid_at).toLocaleString() }}</span
					>
					<span v-else-if="slotProps.data.status === 'offline'" class="text-warning-600" @click="markAsPaid(slotProps.data.id)">
						{{ new Date(slotProps.data.created_at).toLocaleString() }}
					</span>
					<span v-else-if="isStale(slotProps.data)" class="text-muted-color">
						{{ new Date(slotProps.data.created_at).toLocaleString() }}</span
					>
					<span v-else class="text-muted-color-emphasis">{{ new Date(slotProps.data.created_at).toLocaleString() }}</span>
				</template>
			</Column>
			<Column header=" " header-class="w-2/12" body-class="w-2/12">
				<template #body="slotProps">
					<!-- Mark as paid if in offline state -->
					<template v-if="initData?.settings.can_edit">
						<Button
							v-if="slotProps.data.status === 'offline'"
							label="Mark as Paid"
							icon="pi pi-check"
							class="border-none py-0"
							severity="secondary"
							text
							@click="markAsPaid(slotProps.data.id)"
						/>
						<Button
							v-if="slotProps.data.status === 'completed'"
							label="Mark as Delivered"
							icon="pi pi-check"
							class="border-none py-0"
							severity="secondary"
							text
							@click="markAsPaid(slotProps.data.id)"
						/>
					</template>
					<Button
						v-if="slotProps.data.status === 'closed'"
						label="View Details"
						icon="pi pi-eye"
						class="border-none py-0"
						severity="primary"
						text
						@click="router.push({ name: 'order', params: { orderId: slotProps.data.id } })"
					/>
				</template>
			</Column>
		</DataTable>
	</Panel>
</template>
<script setup lang="ts">
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
import Disclaimer from "@/components/webshop/Disclaimer.vue";
import OrderLegend from "@/components/webshop/OrderLegend.vue";
import OrderStatus from "@/components/webshop/OrderStatus.vue";
import MaintenanceService from "@/services/maintenance-service";
import WebshopService from "@/services/webshop-service";
import { useLeftMenuStateStore } from "@/stores/LeftMenuState";
import { storeToRefs } from "pinia";
import Button from "primevue/button";
import Column from "primevue/column";
import DataTable from "primevue/datatable";
import Panel from "primevue/panel";
import Toolbar from "primevue/toolbar";
import { useToast } from "primevue/usetoast";
import { onMounted, ref } from "vue";
import { useRouter } from "vue-router";
import UsernameEmail from "./UsernameEmail.vue";

const orders = ref<App.Http.Resources.Shop.OrderResource[] | undefined>(undefined);
const router = useRouter();
const toast = useToast();

const leftMenuStore = useLeftMenuStateStore();
const { initData } = storeToRefs(leftMenuStore);

function canOpen(order: App.Http.Resources.Shop.OrderResource): boolean {
	return !["pending", "cancelled", "failed", "refunded"].includes(order.status);
}

function openOrder(order: App.Http.Resources.Shop.OrderResource) {
	if (!canOpen(order)) {
		return;
	}
	router.push({ name: "order", params: { orderId: order.id, transactionId: order.transaction_id } });
}

// Return true if the date is older than 2 weeks
function isStale(order: App.Http.Resources.Shop.OrderResource): boolean {
	if (order.created_at === null) {
		return true;
	}
	if (order.status !== "pending" || order.username !== null || (order.items?.length ?? 0) > 0) {
		return false;
	}
	const twoWeeksAgo = new Date();
	twoWeeksAgo.setDate(twoWeeksAgo.getDate() - 14);
	return new Date(order.created_at) < twoWeeksAgo;
}

const numOldOrders = ref(0);

function isZero(string: string): boolean {
	return string.substring(1) === "0.00";
}

function load() {
	WebshopService.Order.list()
		.then((response) => {
			orders.value = response.data;
		})
		.catch((err) => {
			if (err.status === 401 || err.status === 403) {
				router.push({ name: "login" });
			}
		});
	MaintenanceService.oldOrdersCheck().then((response) => {
		numOldOrders.value = response.data;
	});
}

function copyTransactionIdToClipboard(transactionId: string) {
	toast.add({ severity: "info", summary: "Copied to clipboard", detail: "Transaction ID copied to clipboard", life: 3000 });
	navigator.clipboard.writeText(transactionId);
}

function markAsPaid(orderId: number) {
	WebshopService.Order.markAsPaid(orderId).then(() => {
		load();
	});
}

function markAsDelivered(orderId: number) {
	WebshopService.Order.markAsDelivered(orderId).then(() => {
		load();
	});
}

function clean() {
	MaintenanceService.oldOrdersDo().then(() => {
		load();
	});
}

onMounted(() => {
	load();
});
</script>
