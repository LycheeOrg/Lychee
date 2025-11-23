<template>
	<Dialog
		v-model:visible="showBuyMeDialog"
		:modal="true"
		:closable="true"
		class="w-md"
		pt:root:class="border-none"
		pt:mask:style="backdrop-filter: blur(2px)"
		close-on-escape
		@hide="resetBuyMeDialog"
	>
		<template #container>
			<div class="px-8 pt-6 pb-4">
				<div v-if="catalogStore.description" class="text-center text-muted-color mb-4">
					{{ catalogStore.description }}
				</div>
				<div>
					<div
						v-for="price in prices"
						:key="`${price.size_variant}-${price.license_type}`"
						class="border-b last:border-b-0 border-surface-300 dark:border-surface-700 flex flex-row justify-between items-center gap-4 py-1"
					>
						<div class="flex flex-col w-1/3">
							<div class="font-bold">{{ price.size_variant }}</div>
							<div class="text-sm text-muted-color">{{ price.license_type }}</div>
						</div>
						<div class="font-bold text-center text-lg">{{ price.price }}</div>
						<Button
							severity="primary"
							text
							class="rounded border-none font-bold"
							icon="pi pi-cart-arrow-down"
							@click="addPhotoToOrder(price.size_variant, price.license_type)"
						/>
					</div>
				</div>
			</div>
			<Button severity="secondary" class="rounded-b-xl font-bold" @click="resetBuyMeDialog">Cancel</Button>
			<!-- ADD text later that explains which license to chose -->
			<!-- <div class="text-center text-muted-color mt-4" v-if="[...prices.reduce((acc, e) => acc.set(e.license_type, (acc.get(e.license_type) || 0) + 1), new Map()).keys()].length > 1"> -->
			<!-- </div> -->
		</template>
	</Dialog>
</template>

<script setup lang="ts">
import { useBuyMeActions } from "@/composables/album/buyMeActions";
import { useAlbumStore } from "@/stores/AlbumState";
import { useCatalogStore } from "@/stores/CatalogState";
import { useOrderManagementStore } from "@/stores/OrderManagement";
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import { useToast } from "primevue/usetoast";

const albumStore = useAlbumStore();
const orderManagement = useOrderManagementStore();
const catalogStore = useCatalogStore();
const toast = useToast();

const { prices, addPhotoToOrder, showBuyMeDialog, resetBuyMeDialog } = useBuyMeActions(albumStore, orderManagement, catalogStore, toast);
</script>
