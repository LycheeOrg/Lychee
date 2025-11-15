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
	<Panel :pt:header:class="'hidden'" class="border-0 md:max-w-3xl lg:max-w-5xl xl:max-w-7xl mt-9 mx-auto w-full">
		<!-- Empty panel to keep the same layout as other settings pages -->
		<DataTable :value="orders" :loading="orders === undefined" class="mt-4" selectionMode="single" dataKey="id">
			<Column header="Client" header-class="w-3/12" body-class="w-3/12 align-top">
				<template #body="slotProps">
					<span>{{ slotProps.data.username }}</span>
					<span v-if="slotProps.data.username && slotProps.data.email">({{ slotProps.data.email }})</span>
					<span v-else>{{ slotProps.data.email }}</span>
				</template>
			</Column>
			<Column header="Transaction ID" field="transaction_id" header-class="w-3/12" body-class="w-3/12 align-top"></Column>
			<Column header="Status" field="status" header-class="w-1/12" body-class="w-2/12 align-top">
				<template #body="slotProps">
					<OrderStatus :status="slotProps.data.status" />
				</template>
			</Column>
			<Column header="Amount" field="amount" header-class="w-1/12" body-class="w-1/12 align-top">
				<template #body="slotProps">
					<span :class="isZero(slotProps.data.amount) ? 'text-muted-color' : 'font-bold'">{{ slotProps.data.amount }}</span>
				</template>
			</Column>
			<Column header="" header-class="w-3/12 ltr:text-right rtl:text-left" body-class="w-3/12 align-top ltr:text-right rtl:text-left">
				<template #body="slotProps">
					<span v-if="slotProps.data.paid_at" class="text-create-600"
						><i class="pi pi-check-square ltr:mr-2 rtl:ml-2" /> {{ new Date(slotProps.data.paid_at).toLocaleString() }}</span
					>
					<span v-else-if="slotProps.data.status === 'offline'" class="text-warning-600"
						><i class="pi pi-clock ltr:mr-2 rtl:ml-2" /> {{ new Date(slotProps.data.created_at).toLocaleString() }}
					</span>
					<span v-else-if="isStale(slotProps.data.created_at)" class="text-muted-color"
						><i class="pi pi-bolt ltr:mr-2 rtl:ml-2" /> {{ new Date(slotProps.data.created_at).toLocaleString() }}</span
					>
					<span v-else class="text-muted-color-emphasis"
						><i class="pi pi-bolt ltr:mr-2 rtl:ml-2" /> {{ new Date(slotProps.data.created_at).toLocaleString() }}</span
					>
				</template>
			</Column>
		</DataTable>
	</Panel>
</template>
<script setup lang="ts">
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
import OrderStatus from "@/components/webshop/OrderStatus.vue";
import WebshopService from "@/services/webshop-service";
import Column from "primevue/column";
import DataTable from "primevue/datatable";
import Panel from "primevue/panel";
import Toolbar from "primevue/toolbar";
import { onMounted, ref } from "vue";
import { useRouter } from "vue-router";

const orders = ref<App.Http.Resources.Shop.OrderResource[] | undefined>(undefined);
const router = useRouter();

// Return true if the date is older than 2 weeks
function isStale(date: string): boolean {
	const twoWeeksAgo = new Date();
	twoWeeksAgo.setDate(twoWeeksAgo.getDate() - 1);
	return new Date(date) < twoWeeksAgo;
}

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
}

onMounted(() => {
	load();
});
</script>
