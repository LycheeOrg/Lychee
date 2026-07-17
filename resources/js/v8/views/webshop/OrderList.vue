<template>
	<UHeader :toggle="false">
		<template #left>
			<OpenLeftMenu />
		</template>
		{{ $t("webshop.orderList.orders") }}
	</UHeader>
	<div class="text-center lg:hidden font-bold text-error py-3" v-html="$t('settings.small_screen')"></div>
	<div class="md:max-w-3xl lg:max-w-5xl xl:max-w-7xl mt-8 mx-auto w-full p-4">
		<div v-if="numOldOrders > 0" class="flex justify-center items-center gap-4 mb-8">
			<p>{{ sprintf($t("webshop.orderList.numStaleOrders"), numOldOrders) }}</p>
			<UButton :label="$t('webshop.orderList.cleanStaleOrders')" icon="lucide:trash" color="warning" @click="clean" />
		</div>
		<Disclaimer />
		<div class="flex justify-end">
			<UCheckbox v-model="showPending" :label="$t('webshop.orderList.showPending')" @update:model-value="load" />
		</div>
		<OrderLegend />
		<UTable :data="orders ?? []" :columns="columns" :loading="orders === undefined" class="mt-4" />
	</div>
</template>
<script setup lang="ts">
import { h } from "vue";
import OpenLeftMenu from "@/v8/components/headers/OpenLeftMenu.vue";
import Disclaimer from "@/v8/components/webshop/Disclaimer.vue";
import OrderLegend from "@/v8/components/webshop/OrderLegend.vue";
import { useLeftMenuStateStore } from "@/stores/LeftMenuState";
import { storeToRefs } from "pinia";
import { onMounted } from "vue";
import { useRouter } from "vue-router";
import { useOrder } from "@/composables/checkout/useOrder";
import { useAppToast } from "@/v8/composables/useAppToast";
import UsernameEmail from "@/v8/components/webshop/UsernameEmail.vue";
import TransactionIdLink from "@/v8/components/webshop/TransactionIdLink.vue";
import OrderStatus from "@/v8/components/webshop/OrderStatus.vue";
import OrderDate from "@/v8/components/webshop/OrderDate.vue";
import OrderListAction from "@/v8/components/webshop/OrderListAction.vue";
import { sprintf } from "sprintf-js";
import type { TableColumn } from "@nuxt/ui";

type Order = App.Http.Resources.Shop.OrderResource;

const router = useRouter();
const toast = useAppToast();

const leftMenuStore = useLeftMenuStateStore();
const { initData } = storeToRefs(leftMenuStore);

const { isZero, load, clean, orders, numOldOrders, showPending } = useOrder(toast, router);

const columns: TableColumn<Order>[] = [
	{
		id: "client",
		header: () => "",
		cell: ({ row }) => h(UsernameEmail, { username: row.original.username, email: row.original.email }),
	},
	...(initData.value?.settings.can_edit
		? [
				{
					id: "transaction_id",
					header: () => "",
					cell: ({ row }: { row: { original: Order } }) => h(TransactionIdLink, { order: row.original }),
				} as TableColumn<Order>,
			]
		: []),
	{
		id: "status",
		header: () => "",
		cell: ({ row }) => h(OrderStatus, { status: row.original.status }),
	},
	{
		id: "amount",
		header: () => "",
		cell: ({ row }) => h("span", { class: isZero(row.original.amount) ? "text-muted" : "font-bold" }, row.original.amount),
	},
	{
		id: "date",
		header: () => "",
		cell: ({ row }) => h(OrderDate, { order: row.original }),
	},
	{
		id: "actions",
		header: () => "",
		cell: ({ row }) => h(OrderListAction, { order: row.original }),
	},
];

onMounted(() => {
	load();
});
</script>
