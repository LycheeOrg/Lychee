<template>
	<Card class="sm:p-4 xl:px-9 max-w-3xl w-full py-0" :pt:body:class="'p-0'">
		<template #content>
			<div v-if="albumPurchasable !== undefined" class="flex flex-col gap-4">
				<template v-if="albumPurchasable === null">
					<p class="font-bold text-muted-color text-lg text-center">This album is not purchasable (yet).</p>
					<Textarea v-model="description" placeholder="Description for clients" />
					<Textarea v-model="note" placeholder="Owner's Note" />
					<PricesInput :prices="prices" />
					<div class="flex gap-4">
						<Button
							icon="pi pi-plus"
							label="Set Purchasable"
							class="border-none w-full"
							@click="makePurchasable"
							:disabled="prices.length === 0"
						>
						</Button>
						<Button
							icon="pi pi-forward"
							severity="danger"
							label="Set Purchasable and propagate"
							class="font-bold w-full border-none"
							@click="
								appliesToSubalbums = true;
								makePurchasable();
							"
							:disabled="prices.length === 0"
						>
						</Button>
					</div>
					<Message severity="error" v-if="prices.length === 0">Set at least one price.</Message>
				</template>
				<template v-else>
					<Textarea v-model="description" placeholder="Description for clients" />
					<Textarea v-model="note" placeholder="Owner's Note" />
					<PricesInput :prices="prices" />
					<div class="flex gap-4">
						<Button
							class="text-danger-800 font-bold hover:text-white hover:bg-danger-800 w-full bg-transparent border-none"
							@click="disable"
							>Disable</Button
						>
						<Button class="border-none font-bold w-full" @click="makePurchasable" :disabled="prices.length === 0"> Update </Button>
					</div>
					<Message severity="error" v-if="prices.length === 0">Set at least one price.</Message>
				</template>
			</div>
		</template>
	</Card>
</template>
<script setup lang="ts">
import ShopManagementService, { Price } from "@/services/shop-management-service";
import Card from "primevue/card";
import { useToast } from "primevue/usetoast";
import { onMounted, ref } from "vue";
import Textarea from "@/components/forms/basic/Textarea.vue";
import Button from "primevue/button";
import PricesInput from "@/components/forms/shop-management/PricesInput.vue";
import Message from "primevue/message";
import { useAlbumStore } from "@/stores/AlbumState";

const toast = useToast();
const albumStore = useAlbumStore();

const albumPurchasable = ref<undefined | App.Http.Resources.Shop.EditablePurchasableResource | null>(undefined);

const description = ref<string | undefined>(undefined);
const note = ref<string | undefined>(undefined);
const appliesToSubalbums = ref<boolean>(false);
const prices = ref<Price[]>([]);

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
		})
		.catch((error) => {
			toast.add({ severity: "error", summary: "Error", detail: error.message, life: 3000 });
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
		applies_to_subalbums: appliesToSubalbums.value,
	})
		.then(() => {
			toast.add({ severity: "success", summary: "Success", detail: "Album is now purchasable", life: 3000 });
			load();
		})
		.catch((error) => {
			toast.add({ severity: "error", summary: "Error", detail: error.message, life: 3000 });
		});
}

function disable() {
	if (albumPurchasable.value === null || albumPurchasable.value === undefined) {
		return;
	}
	ShopManagementService.deletePurchasable(albumPurchasable.value.purchasable_id)
		.then(() => {
			toast.add({ severity: "success", summary: "Success", detail: "Album is no longer purchasable", life: 3000 });
			load();
		})
		.catch((error) => {
			toast.add({ severity: "error", summary: "Error", detail: error.message, life: 3000 });
		});
}

onMounted(() => {
	load();
});
</script>
