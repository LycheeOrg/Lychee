<template>
	<Card class="sm:p-4 xl:px-9 max-w-3xl w-full py-0" :pt:body:class="'p-0'">
		<template #content>
			<div v-if="albumPurchasable !== undefined" class="flex flex-col gap-4">
				<template v-if="albumPurchasable === null">
					<p class="font-bold text-muted-color text-lg text-center">{{ $t("webshop.albumPurchasable.notPurchasableYet") }}</p>
					<Textarea v-model="description" :placeholder="$t('webshop.albumPurchasable.descriptionPlaceholder')" />
					<Textarea v-model="note" :placeholder="$t('webshop.albumPurchasable.ownerNotePlaceholder')" />
					<PricesInput :prices="prices" />
					<PrintSizePricesInput v-model="printSizes" />
					<PixelSizePricesInput v-model="pixelSizes" />
					<div class="flex gap-4">
						<Button
							icon="pi pi-plus"
							:label="$t('webshop.albumPurchasable.setPurchasable')"
							class="border-none w-full"
							@click="makePurchasable"
							:disabled="!canSubmit"
						>
						</Button>
						<Button
							icon="pi pi-forward"
							severity="danger"
							:label="$t('webshop.albumPurchasable.setPurchasablePropagate')"
							class="font-bold w-full border-none"
							@click="
								appliesToSubalbums = true;
								makePurchasable();
							"
							:disabled="!canSubmit"
						>
						</Button>
					</div>
					<Message severity="error" v-if="!canSubmit">{{ $t("webshop.albumPurchasable.setAtLeastOnePrice") }}</Message>
				</template>
				<template v-else>
					<Textarea v-model="description" :placeholder="$t('webshop.albumPurchasable.descriptionPlaceholder')" />
					<Textarea v-model="note" :placeholder="$t('webshop.albumPurchasable.ownerNotePlaceholder')" />
					<PricesInput :prices="prices" />
					<PrintSizePricesInput v-model="printSizes" />
					<PixelSizePricesInput v-model="pixelSizes" />
					<div class="flex gap-4">
						<Button
							class="text-danger-800 font-bold hover:text-white hover:bg-danger-800 w-full bg-transparent border-none"
							@click="disable"
							>{{ $t("webshop.albumPurchasable.disable") }}</Button
						>
						<Button class="border-none font-bold w-full" @click="updatePrices" :disabled="!canSubmit">
							{{ $t("webshop.albumPurchasable.update") }}
						</Button>
					</div>
					<Message severity="error" v-if="!canSubmit">{{ $t("webshop.albumPurchasable.setAtLeastOnePrice") }}</Message>
				</template>
			</div>
		</template>
	</Card>
</template>
<script setup lang="ts">
import ShopManagementService, { Price, PrintSizeAssignment, PixelSizeAssignment } from "@/services/shop-management-service";
import Card from "primevue/card";
import { useToast } from "primevue/usetoast";
import { onMounted, ref, computed } from "vue";
import Textarea from "@/components/forms/basic/Textarea.vue";
import Button from "primevue/button";
import PricesInput from "@/components/forms/shop-management/PricesInput.vue";
import PrintSizePricesInput from "@/components/forms/shop-management/PrintSizePricesInput.vue";
import PixelSizePricesInput from "@/components/forms/shop-management/PixelSizePricesInput.vue";
import Message from "primevue/message";
import { useAlbumStore } from "@/stores/AlbumState";
import { trans } from "laravel-vue-i18n";

const toast = useToast();
const albumStore = useAlbumStore();

const albumPurchasable = ref<undefined | App.Http.Resources.Shop.EditablePurchasableResource | null>(undefined);

const description = ref<string | undefined>(undefined);
const note = ref<string | undefined>(undefined);
const appliesToSubalbums = ref<boolean>(false);
const prices = ref<Price[]>([]);
const printSizes = ref<PrintSizeAssignment[]>([]);
const pixelSizes = ref<PixelSizeAssignment[]>([]);
const canSubmit = computed(() => {
	return prices.value.length > 0 || printSizes.value.length > 0 || pixelSizes.value.length > 0;
});

function load() {
	if (!albumStore.albumId) {
		return;
	}

	// Reset state
	appliesToSubalbums.value = false;

	ShopManagementService.list([albumStore.albumId])
		.then((response) => {
			albumPurchasable.value = response.data.find((p) => p.album_id === albumStore.albumId && p.photo_id === null) ?? null;

			description.value = albumPurchasable.value?.description ?? undefined;
			note.value = albumPurchasable.value?.owner_notes ?? undefined;
			prices.value =
				albumPurchasable.value?.prices?.map((p: App.Http.Resources.Shop.PriceResource) => {
					return { price: p.price_cents, license_type: p.license_type, size_variant_type: p.size_variant };
				}) ?? [];
			printSizes.value =
				albumPurchasable.value?.print_sizes?.map((ps: App.Http.Resources.Shop.PurchasablePrintSizeResource) => {
					return { print_size_id: ps.print_size_id, price: ps.price_cents };
				}) ?? [];
			pixelSizes.value =
				albumPurchasable.value?.pixel_sizes?.map((ps: App.Http.Resources.Shop.PurchasablePixelSizeResource) => {
					return { pixel_size_id: ps.pixel_size_id, price: ps.price_cents, license_type: ps.license_type };
				}) ?? [];
		})
		.catch((error) => {
			toast.add({ severity: "error", summary: trans("webshop.albumPurchasable.error"), detail: error.message, life: 3000 });
		});
}

function makePurchasable() {
	if (!albumStore.albumId) {
		return;
	}

	ShopManagementService.createPurchasableAlbum({
		album_ids: [albumStore.albumId],
		note: note.value ?? null,
		description: description.value ?? null,
		prices: prices.value,
		print_sizes: printSizes.value,
		pixel_sizes: pixelSizes.value,
		applies_to_subalbums: appliesToSubalbums.value,
	})
		.then(() => {
			toast.add({
				severity: "success",
				summary: trans("webshop.albumPurchasable.success"),
				detail: trans("webshop.albumPurchasable.albumNowPurchasable"),
				life: 3000,
			});
			load();
		})
		.catch((error) => {
			toast.add({ severity: "error", summary: trans("webshop.albumPurchasable.error"), detail: error.message, life: 3000 });
		});
}

function disable() {
	if (albumPurchasable.value === null || albumPurchasable.value === undefined) {
		return;
	}
	ShopManagementService.deletePurchasable(albumPurchasable.value.purchasable_id)
		.then(() => {
			toast.add({
				severity: "success",
				summary: trans("webshop.albumPurchasable.success"),
				detail: trans("webshop.albumPurchasable.albumNoLongerPurchasable"),
				life: 3000,
			});
			load();
		})
		.catch((error) => {
			toast.add({ severity: "error", summary: trans("webshop.albumPurchasable.error"), detail: error.message, life: 3000 });
		});
}

function updatePrices() {
	if (albumPurchasable.value === null || albumPurchasable.value === undefined) {
		return;
	}

	ShopManagementService.updatePurchasablePrices({
		purchasable_id: albumPurchasable.value.purchasable_id,
		note: note.value ?? null,
		description: description.value ?? null,
		prices: prices.value,
		print_sizes: printSizes.value,
		pixel_sizes: pixelSizes.value,
	})
		.then(() => {
			toast.add({
				severity: "success",
				summary: trans("webshop.albumPurchasable.success"),
				detail: trans("webshop.albumPurchasable.albumNowPurchasable"),
				life: 3000,
			});
			load();
		})
		.catch((error) => {
			toast.add({ severity: "error", summary: trans("webshop.albumPurchasable.error"), detail: error.message, life: 3000 });
		});
}

onMounted(() => {
	load();
});
</script>
