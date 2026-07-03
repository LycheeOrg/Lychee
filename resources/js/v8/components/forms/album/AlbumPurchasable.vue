<template>
	<UCard class="sm:p-4 xl:px-9 max-w-3xl w-full py-0" :ui="{ body: 'p-0' }">
		<div v-if="albumPurchasable !== undefined" class="flex flex-col gap-4">
			<template v-if="albumPurchasable === null">
				<p class="font-bold text-muted text-lg text-center">{{ $t("webshop.albumPurchasable.notPurchasableYet") }}</p>
				<UTextarea v-model="description" :placeholder="$t('webshop.albumPurchasable.descriptionPlaceholder')" class="w-full" />
				<UTextarea v-model="note" :placeholder="$t('webshop.albumPurchasable.ownerNotePlaceholder')" class="w-full" />
				<PricesInput :prices="prices" />
				<PrintSizePricesInput v-model="printSizes" />
				<PixelSizePricesInput v-model="pixelSizes" />
				<div class="flex gap-4">
					<UButton icon="prime:plus" :label="$t('webshop.albumPurchasable.setPurchasable')" class="w-full justify-center" :disabled="!canSubmit" @click="makePurchasable" />
					<UButton
						icon="prime:forward"
						color="error"
						:label="$t('webshop.albumPurchasable.setPurchasablePropagate')"
						class="font-bold w-full justify-center"
						:disabled="!canSubmit"
						@click="
							appliesToSubalbums = true;
							makePurchasable();
						"
					/>
				</div>
				<UAlert v-if="!canSubmit" color="error" :description="$t('webshop.albumPurchasable.setAtLeastOnePrice')" />
			</template>
			<template v-else>
				<UTextarea v-model="description" :placeholder="$t('webshop.albumPurchasable.descriptionPlaceholder')" class="w-full" />
				<UTextarea v-model="note" :placeholder="$t('webshop.albumPurchasable.ownerNotePlaceholder')" class="w-full" />
				<PricesInput :prices="prices" />
				<PrintSizePricesInput v-model="printSizes" />
				<PixelSizePricesInput v-model="pixelSizes" />
				<div class="flex gap-4">
					<UButton color="error" variant="ghost" class="font-bold w-full justify-center" @click="disable">{{ $t("webshop.albumPurchasable.disable") }}</UButton>
					<UButton class="w-full justify-center font-bold" :disabled="!canSubmit" @click="updatePrices">
						{{ $t("webshop.albumPurchasable.update") }}
					</UButton>
				</div>
				<UAlert v-if="!canSubmit" color="error" :description="$t('webshop.albumPurchasable.setAtLeastOnePrice')" />
			</template>
		</div>
	</UCard>
</template>
<script setup lang="ts">
import ShopManagementService, { Price, PrintSizeAssignment, PixelSizeAssignment } from "@/services/shop-management-service";
import { useAppToast } from "@/v8/composables/useAppToast";
import { onMounted, ref, computed } from "vue";
import PricesInput from "@/v8/components/forms/shop-management/PricesInput.vue";
import PrintSizePricesInput from "@/v8/components/forms/shop-management/PrintSizePricesInput.vue";
import PixelSizePricesInput from "@/v8/components/forms/shop-management/PixelSizePricesInput.vue";
import { useAlbumStore } from "@/stores/AlbumState";
import { trans } from "laravel-vue-i18n";

const toast = useAppToast();
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
