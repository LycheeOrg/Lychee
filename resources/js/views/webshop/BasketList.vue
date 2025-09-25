<template>
	<Toolbar class="w-full border-0 h-14 rounded-none">
		<template #start>
			<OpenLeftMenu />
		</template>

		<template #center>
			{{ "Basket" }}
		</template>

		<template #end> </template>
	</Toolbar>
	<Panel :pt:header:class="'hidden'" class="border-0 md:max-w-3xl lg:max-w-5xl xl:max-w-7xl mt-9 mx-auto w-full">
		<div v-if="order && order.items && order.items.length > 0">
			<div v-for="item in order.items" :key="item.id" class="border-b last:border-0 p-4 flex flex-row justify-between items-center">
				<div class="flex flex-col">
					<span class="font-bold">{{ item.title }}</span>
					<span class="text-sm text-muted-color">Size: {{ item.size_variant_type }}, License: {{ item.license_type }}</span>
					<span class="text-sm text-muted-color" v-if="item.item_notes">Notes: {{ item.item_notes }}</span>
				</div>
				<div class="font-bold">{{ item.price }}</div>
				<Button icon="pi pi-trash" class="p-button-text p-button-danger" aria-label="Remove item" @click="removeItem(item.id)" />
			</div>
			<div class="flex flex-row justify-end items-center p-4 border-t font-bold">Total: {{ order.amount }}</div>
		</div>
		<div v-else class="text-center py-10 text-muted-color">Your basket is empty.</div>
		<div>
			<!-- Future actions like checkout or clear basket can be added here -->
		</div>
	</Panel>
</template>

<script setup lang="ts">
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
import { useOrderManagementStore } from "@/stores/OrderManagement";
import { storeToRefs } from "pinia";
import Panel from "primevue/panel";
import Toolbar from "primevue/toolbar";

const orderStore = useOrderManagementStore();
const { order } = storeToRefs(orderStore);

function removeItem(itemId: number) {
	orderStore.removeItem(itemId);
}
</script>
