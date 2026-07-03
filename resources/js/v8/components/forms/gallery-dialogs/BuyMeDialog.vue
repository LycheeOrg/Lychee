<template>
	<UModal v-model:open="showBuyMeDialog">
		<template #body>
			<div v-if="catalogStore.description" class="text-center text-muted mb-4">
				{{ catalogStore.description }}
			</div>

			<!-- Item type tabs (only shown when multiple types are available) -->
			<div v-if="hasMultipleTypes" class="flex border-b border-default mb-4">
				<button
					v-if="prices.length > 0"
					:class="[
						'px-4 py-2 text-sm font-medium border-b-2 transition-colors',
						buyMeItemType === 'digital' ? 'border-primary text-primary' : 'border-transparent text-muted hover:text-highlighted',
					]"
					@click="buyMeItemType = 'digital'"
				>
					{{ $t("webshop.buyMeDialog.digital") }}
				</button>
				<button
					v-if="printSizes.length > 0"
					:class="[
						'px-4 py-2 text-sm font-medium border-b-2 transition-colors',
						buyMeItemType === 'print' ? 'border-primary text-primary' : 'border-transparent text-muted hover:text-highlighted',
					]"
					@click="buyMeItemType = 'print'"
				>
					{{ $t("webshop.buyMeDialog.print") }}
				</button>
				<button
					v-if="pixelSizes.length > 0"
					:class="[
						'px-4 py-2 text-sm font-medium border-b-2 transition-colors',
						buyMeItemType === 'pixel' ? 'border-primary text-primary' : 'border-transparent text-muted hover:text-highlighted',
					]"
					@click="buyMeItemType = 'pixel'"
				>
					{{ $t("webshop.buyMeDialog.pixel") }}
				</button>
			</div>

			<!-- Digital size variants -->
			<div v-if="buyMeItemType === 'digital'">
				<div
					v-for="price in prices"
					:key="`${price.size_variant}-${price.license_type}`"
					class="border-b last:border-b-0 border-default flex flex-row justify-between items-center gap-4 py-1"
				>
					<div class="flex flex-col w-1/3">
						<div class="font-bold">{{ price.size_variant }}</div>
						<div class="text-sm text-muted">{{ price.license_type }}</div>
					</div>
					<div class="font-bold text-center text-lg">{{ price.price }}</div>
					<UButton
						color="primary"
						variant="ghost"
						class="rounded font-bold"
						icon="prime:cart-arrow-down"
						@click="addPhotoToOrder(price.size_variant, price.license_type)"
					/>
				</div>
			</div>

			<!-- Print sizes -->
			<div v-if="buyMeItemType === 'print'">
				<div
					v-for="ps in printSizes"
					:key="`print-${ps.id}`"
					class="border-b last:border-b-0 border-default flex flex-row justify-between items-center gap-4 py-1"
				>
					<div class="flex flex-col w-1/3">
						<div class="font-bold">{{ ps.label }}</div>
						<div class="text-sm text-muted">
							{{ ps.width }}×{{ ps.height }} {{ ps.unit }}<span v-if="ps.paper_type"> — {{ ps.paper_type }}</span>
						</div>
					</div>
					<div class="font-bold text-center text-lg">{{ ps.price }}</div>
					<UButton
						color="primary"
						variant="ghost"
						class="rounded font-bold"
						icon="prime:cart-arrow-down"
						@click="addPrintPhotoToOrder(ps.print_size_id, ps.price)"
					/>
				</div>
			</div>

			<!-- Pixel sizes -->
			<div v-if="buyMeItemType === 'pixel'">
				<div
					v-for="px in pixelSizes"
					:key="`pixel-${px.id}-${px.license_type}`"
					class="border-b last:border-b-0 border-default flex flex-row justify-between items-center gap-4 py-1"
				>
					<div class="flex flex-col w-1/3">
						<div class="font-bold">{{ px.label }}</div>
						<div class="text-sm text-muted">{{ px.width }}×{{ px.height }} px</div>
						<div class="text-sm text-muted">{{ px.license_type }}</div>
					</div>
					<div class="font-bold text-center text-lg">{{ px.price }}</div>
					<UButton
						color="primary"
						variant="ghost"
						class="rounded font-bold"
						icon="prime:cart-arrow-down"
						@click="addPixelPhotoToOrder(px.pixel_size_id, px.license_type, px.price)"
					/>
				</div>
			</div>
		</template>
		<template #footer>
			<UButton color="neutral" class="font-bold w-full justify-center" @click="resetBuyMeDialog">{{ $t("dialogs.button.cancel") }}</UButton>
		</template>
	</UModal>
</template>

<script setup lang="ts">
import { useBuyMeActions } from "@/composables/album/buyMeActions";
import { useAlbumStore } from "@/stores/AlbumState";
import { useCatalogStore } from "@/stores/CatalogState";
import { useOrderManagementStore } from "@/stores/OrderManagement";
import { usePhotosStore } from "@/stores/PhotosState";
import { useAppToast } from "@/v8/composables/useAppToast";
import { computed } from "vue";

const albumStore = useAlbumStore();
const photosStore = usePhotosStore();
const orderManagement = useOrderManagementStore();
const catalogStore = useCatalogStore();
const toast = useAppToast();

const {
	prices,
	printSizes,
	pixelSizes,
	buyMeItemType,
	addPhotoToOrder,
	addPrintPhotoToOrder,
	addPixelPhotoToOrder,
	showBuyMeDialog,
	resetBuyMeDialog,
} = useBuyMeActions(albumStore, photosStore, orderManagement, catalogStore, toast);

const hasMultipleTypes = computed(() => {
	const types = [prices.value.length > 0, printSizes.value.length > 0, pixelSizes.value.length > 0].filter(Boolean).length;
	return types > 1;
});
</script>
