<template>
	<Card class="sm:p-4 xl:px-9 max-w-3xl w-full py-0">
		<template #content>
			<div
				v-if="albumPurchasable !== undefined && photosPurchasable !== undefined && childrenPurchasables !== undefined"
				class="flex flex-col gap-4"
			>
				<template v-if="albumPurchasable === null">
					<p class="font-bold text-muted-color-emphasis text-lg text-center">Album is not purchasable.</p>
					<Textarea v-model="description" placeholder="Description for clients" />
					<Textarea v-model="note" placeholder="Owner's Note" />
					<PricesInput :prices="prices" />
					<Button label="Make Purchasable" @click="makePurchasable" />
				</template>
				<template v-else> </template>
			</div>
		</template>
	</Card>
</template>
<script setup lang="ts">
import ShopManagementService, { CatalogService, Price } from "@/services/shop-management-service";
import Card from "primevue/card";
import { useToast } from "primevue/usetoast";
import { onMounted, ref } from "vue";
import Textarea from "@/components/forms/basic/Textarea.vue";
import Button from "primevue/button";
import PricesInput from "@/components/forms/shop-management/PricesInput.vue";

const props = defineProps<{
	album: App.Http.Resources.Models.AlbumResource;
}>();

const toast = useToast();

const albumPurchasable = ref<undefined | App.Http.Resources.Shop.PurchasableResource | null>(undefined);
const photosPurchasable = ref<undefined | App.Http.Resources.Shop.PurchasableResource[]>(undefined);
const childrenPurchasables = ref<undefined | App.Http.Resources.Shop.PurchasableResource[]>(undefined);

const description = ref<string | undefined>(undefined);
const note = ref<string | undefined>(undefined);
const appliesToSubalbums = ref<boolean>(false);
const prices = ref<Price[]>([]);

// album_ids: string[];
// description: string | null;
// note: string | null;
// prices: Price[];
// applies_to_subalbums: boolean;

function load() {
	CatalogService.getCatalog(props.album.id)
		.then((response) => {
			albumPurchasable.value = response.data.album_purchasable;
			photosPurchasable.value = response.data.photo_purchasables;
			childrenPurchasables.value = response.data.children_purchasables;
		})
		.catch((error) => {
			toast.add({ severity: "error", summary: "Error", detail: error.message, life: 3000 });
		});
}

function makePurchasable() {
	ShopManagementService.createPurchasableAlbum({
		album_ids: [props.album.id],
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

onMounted(() => {
	load();
});
</script>
