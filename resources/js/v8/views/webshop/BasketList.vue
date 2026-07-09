<template>
	<UHeader :toggle="false">
		<template #left>
			<OpenLeftMenu />
		</template>
		{{ $t("webshop.basketList.basket") }}
	</UHeader>
	<UCard class="border-0 md:max-w-3xl lg:max-w-5xl xl:max-w-7xl mt-9 mx-auto w-full" :ui="{ header: 'hidden' }">
		<div v-if="order && order.items && order.items.length > 0">
			<div
				v-for="item in order.items"
				:key="item.id"
				class="border-b last:border-0 border-default p-4 flex flex-row justify-between items-center gap-4"
			>
				<!-- Later add the image -->
				<div class="flex flex-col grow">
					<span class="font-bold"
						><router-link :to="{ name: 'album', params: { albumId: item.album_id, photoId: item.photo_id } }" target="_blank" class="">{{
							item.title
						}}</router-link></span
					>
					<span v-if="item.is_print" class="text-sm text-muted">
						{{ $t("webshop.basketList.printLabel") }}: {{ item.print_width }} × {{ item.print_height }} {{ item.print_unit }},
						{{ $t("webshop.basketList.paperType") }}: {{ item.print_paper_type }}
					</span>
					<span v-else-if="item.pixel_size_id !== null" class="text-sm text-muted">
						{{ $t("webshop.basketList.pixelLabel") }}: {{ item.pixel_width }} × {{ item.pixel_height }} px,
						{{ $t("webshop.basketList.license") }}: {{ item.license_type }}
					</span>
					<span v-else class="text-sm text-muted">
						{{ $t("webshop.basketList.size") }}: {{ item.size_variant_type }}, {{ $t("webshop.basketList.license") }}:
						{{ item.license_type }}
					</span>
					<span v-if="item.item_notes" class="text-sm text-muted">{{ $t("webshop.basketList.notes") }}: {{ item.item_notes }}</span>
				</div>
				<div class="font-bold shrink">{{ item.price }}</div>
				<UTooltip :text="$t('webshop.basketList.removeItem')">
					<UButton
						icon="prime:trash"
						color="neutral"
						variant="ghost"
						class="h-12"
						:aria-label="$t('webshop.basketList.removeItem')"
						@click="removeItem(item.id)"
					/>
				</UTooltip>
			</div>
			<div class="flex flex-row-reverse p-4 items-center">
				<UTooltip :text="$t('webshop.basketList.clearBasket')">
					<UButton
						icon="prime:trash"
						color="neutral"
						variant="ghost"
						class="h-12"
						:aria-label="$t('webshop.basketList.clearBasket')"
						@click="removeBasket"
					/>
				</UTooltip>
				<div class="flex flex-row justify-end items-center p-4 font-bold text-2xl">
					{{ $t("webshop.basketList.total") }} {{ order.amount }}
				</div>
			</div>
			<div class="flex ltr:justify-end rtl:justify-start">
				<UButton :to="{ name: 'checkout' }" icon="prime:credit-card" class="px-8" :label="$t('webshop.basketList.proceedToCheckout')" />
			</div>
		</div>
		<div v-else class="text-center py-10 text-muted">{{ $t("webshop.basketList.emptyBasket") }}</div>
	</UCard>
</template>

<script setup lang="ts">
import OpenLeftMenu from "@/v8/components/headers/OpenLeftMenu.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useOrderManagementStore } from "@/stores/OrderManagement";
import { storeToRefs } from "pinia";
import { onMounted } from "vue";
import { useRouter } from "vue-router";

const lycheeStateStore = useLycheeStateStore();
const orderStore = useOrderManagementStore();
const { order } = storeToRefs(orderStore);
const router = useRouter();

function removeItem(itemId: number) {
	orderStore.removeItem(itemId);
}

function removeBasket() {
	orderStore.forget();
	router.push({ name: "gallery" });
}

onMounted(async () => {
	await lycheeStateStore.load();
	orderStore.load().then(() => {
		if (order.value === undefined || order.value?.items === null || order.value.items.length === 0) {
			// Redirect to basket if no items
			router.push({ name: "gallery" });
		}

		// Handle order status
		if (order.value?.status === "processing") {
			// Switch to step 2 if payment is in progress
			router.push({ name: "checkout", params: { step: "payment" } });
		}

		if (["completed", "closed", "offline"].includes(order.value?.status || "")) {
			router.push({ name: "checkout", params: { step: "completed" } });
		}
	});
});
</script>
