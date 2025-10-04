<template>
	<div
		class="flex flex-col max-h-[75vh] p-8 bg-surface-50 dark:bg-surface-950/25 rounded border border-surface-200 dark:border-surface-700 min-h-[400px]"
		v-if="order"
	>
		<div class="text-lg font-bold text-center mb-12">Summary of your order</div>
		<div class="flex flex-col gap-2 w-full h-full overflow-y-scroll ltr:pr-4 rtl:pl-4" v-if="order">
			<div v-for="item in order.items" :key="item.id" class="flex flex-row justify-between items-center gap-4">
				<div class="flex flex-col grow">
					<span class="font-bold"
						><router-link :to="{ name: 'album', params: { albumId: item.album_id, photoId: item.photo_id } }" target="_blank" class="">{{
							item.title
						}}</router-link></span
					>
					<span class="text-xs text-muted-color">Size: {{ item.size_variant_type }}, License: {{ item.license_type }}</span>
					<span class="text-xs text-muted-color" v-if="item.item_notes">Notes: {{ item.item_notes }}</span>
				</div>
				<div class="font-bold shrink">{{ item.price }}</div>
			</div>
		</div>
		<div class="mt-12 font-bold text-lg ltr:text-right rtl:text-left">Total: {{ order.amount }}</div>
	</div>
</template>
<script setup lang="ts">
import { useOrderManagementStore } from "@/stores/OrderManagement";
import { storeToRefs } from "pinia";

const orderStore = useOrderManagementStore();
const { order } = storeToRefs(orderStore);
</script>
