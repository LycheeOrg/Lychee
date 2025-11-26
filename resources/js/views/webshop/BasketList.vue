<template>
	<Toolbar class="w-full border-0 h-14 rounded-none">
		<template #start>
			<OpenLeftMenu />
		</template>

		<template #center>
			{{ $t("webshop.basketList.basket") }}
		</template>

		<template #end> </template>
	</Toolbar>
	<Panel :pt:header:class="'hidden'" class="border-0 md:max-w-3xl lg:max-w-5xl xl:max-w-7xl mt-9 mx-auto w-full">
		<div v-if="order && order.items && order.items.length > 0">
			<div
				v-for="item in order.items"
				:key="item.id"
				class="border-b last:border-0 border-b-surface-300 dark:border-b-surface-700 p-4 flex flex-row justify-between items-center gap-4"
			>
				<!-- Later add the image -->
				<!-- <img :src="item." class="h-12 w-12 mr-4 inline-block" v-if="item.photo_url" /> -->
				<div class="flex flex-col grow">
					<span class="font-bold"
						><router-link :to="{ name: 'album', params: { albumId: item.album_id, photoId: item.photo_id } }" target="_blank" class="">{{
							item.title
						}}</router-link></span
					>
					<span class="text-sm text-muted-color"
						>{{ $t("webshop.basketList.size") }}: {{ item.size_variant_type }}, {{ $t("webshop.basketList.license") }}:
						{{ item.license_type }}</span
					>
					<span class="text-sm text-muted-color" v-if="item.item_notes">{{ $t("webshop.basketList.notes") }}: {{ item.item_notes }}</span>
				</div>
				<div class="font-bold shrink">{{ item.price }}</div>
				<Button
					icon="pi pi-trash"
					severity="secondary"
					class="border-0 h-12"
					:aria-label="$t('webshop.basketList.removeItem')"
					@click="removeItem(item.id)"
					v-tooltip.bottom="$t('webshop.basketList.removeItem')"
				/>
			</div>
			<div class="flex flex-row-reverse p-4 items-center">
				<Button
					icon="pi pi-trash"
					severity="secondary"
					class="border-0 h-12"
					:aria-label="$t('webshop.basketList.clearBasket')"
					@click="removeBasket"
					v-tooltip.bottom="$t('webshop.basketList.clearBasket')"
				/>
				<div class="flex flex-row justify-end items-center p-4 font-bold text-2xl">
					{{ $t("webshop.basketList.total") }} {{ order.amount }}
				</div>
			</div>
			<div class="flex ltr:justify-end rtl:justify-start">
				<Button asChild v-slot="slotProps">
					<RouterLink :to="{ name: 'checkout' }" :class="slotProps.class" class="px-8 border-none"
						><i class="pi pi-credit-card" />{{ $t("webshop.basketList.proceedToCheckout") }}</RouterLink
					>
				</Button>
			</div>
		</div>
		<div v-else class="text-center py-10 text-muted-color">{{ $t("webshop.basketList.emptyBasket") }}</div>
		<div>
			<!-- Future actions like checkout or clear basket can be added here -->
		</div>
	</Panel>
</template>

<script setup lang="ts">
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useOrderManagementStore } from "@/stores/OrderManagement";
import { storeToRefs } from "pinia";
import Button from "primevue/button";
import Panel from "primevue/panel";
import Toolbar from "primevue/toolbar";
import { onMounted } from "vue";

const lycheeStateStore = useLycheeStateStore();
const orderStore = useOrderManagementStore();
const { order } = storeToRefs(orderStore);

function removeItem(itemId: number) {
	orderStore.removeItem(itemId);
}

function removeBasket() {
	orderStore.clear();
}

onMounted(async () => {
	await lycheeStateStore.load();
	orderStore.load();
});
</script>
