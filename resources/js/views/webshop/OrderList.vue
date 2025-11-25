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
					<TransactionIdLink :order="slotProps.data" />
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
					<OrderDate :order="slotProps.data" />
				</template>
			</Column>
			<Column header=" " header-class="w-2/12" body-class="w-2/12">
				<template #body="slotProps">
					<OrderListAction :order="slotProps.data" />
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
import { useLeftMenuStateStore } from "@/stores/LeftMenuState";
import { storeToRefs } from "pinia";
import Button from "primevue/button";
import Column from "primevue/column";
import DataTable from "primevue/datatable";
import Panel from "primevue/panel";
import Toolbar from "primevue/toolbar";
import { useToast } from "primevue/usetoast";
import { onMounted } from "vue";
import { useRouter } from "vue-router";
import { useOrder } from "@/composables/checkout/useOrder";
import UsernameEmail from "@/components/webshop/UsernameEmail.vue";
import TransactionIdLink from "@/components/webshop/TransactionIdLink.vue";
import OrderDate from "@/components/webshop/OrderDate.vue";
import OrderListAction from "@/components/webshop/OrderListAction.vue";

const router = useRouter();
const toast = useToast();

const leftMenuStore = useLeftMenuStateStore();
const { initData } = storeToRefs(leftMenuStore);

const { isZero, load, clean, orders, numOldOrders } = useOrder(toast, router);

onMounted(() => {
	load();
});
</script>
