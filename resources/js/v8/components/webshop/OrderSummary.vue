<template>
	<div v-if="order" class="flex flex-col max-h-[75vh] p-8 bg-elevated/50 rounded border border-default min-h-100">
		<div class="text-lg font-bold text-center mb-12">{{ $t("webshop.orderSummary.title") }}</div>
		<div v-if="order" class="flex flex-col gap-2 w-full h-full overflow-y-scroll ltr:pr-4 rtl:pl-4">
			<div v-for="item in order.items" :key="item.id" class="flex flex-row justify-between items-center gap-4">
				<div class="flex flex-col grow">
					<span class="font-bold"
						><router-link :to="{ name: 'album', params: { albumId: item.album_id, photoId: item.photo_id } }" target="_blank" class="">{{
							item.title
						}}</router-link></span
					>
					<span v-if="item.is_print" class="text-xs text-muted">
						{{ $t("webshop.basketList.printLabel") }}: {{ item.print_width }} × {{ item.print_height }} {{ item.print_unit }},
						{{ $t("webshop.basketList.paperType") }}: {{ item.print_paper_type }}
					</span>
					<span v-else-if="item.pixel_size_id !== null" class="text-xs text-muted">
						{{ $t("webshop.basketList.pixelLabel") }}: {{ item.pixel_width }} × {{ item.pixel_height }} px,
						{{ $t("webshop.orderSummary.license") }} {{ item.license_type }}
					</span>
					<span v-else class="text-xs text-muted">
						{{ $t("webshop.orderSummary.size") }} {{ item.size_variant_type }}, {{ $t("webshop.orderSummary.license") }}
						{{ item.license_type }}
					</span>
					<span v-if="item.item_notes" class="text-xs text-muted">{{ $t("webshop.orderSummary.notes") }} {{ item.item_notes }}</span>
				</div>
				<div class="font-bold shrink">{{ item.price }}</div>
			</div>
		</div>
		<div class="mt-12 font-bold text-lg ltr:text-right rtl:text-left">{{ $t("webshop.orderSummary.total") }} {{ order.amount }}</div>
	</div>
</template>
<script setup lang="ts">
import { useOrderManagementStore } from "@/stores/OrderManagement";
import { storeToRefs } from "pinia";

const orderStore = useOrderManagementStore();
const { order } = storeToRefs(orderStore);
</script>
