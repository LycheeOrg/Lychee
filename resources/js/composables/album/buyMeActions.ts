import { AlbumStore } from "@/stores/AlbumState";
import { CatalogStore } from "@/stores/CatalogState";
import { OrderManagementStateStore } from "@/stores/OrderManagement";
import { ref } from "vue";

const buyablePhotoId = ref<string | undefined>(undefined);
const buyableAlbumId = ref<string | undefined>(undefined);
const prices = ref<App.Http.Resources.Shop.PriceResource[]>([]);
const showBuyMeDialog = ref(false);

export function useBuyMeActions(albumStore: AlbumStore, orderManagement: OrderManagementStateStore, catalogStore: CatalogStore) {
	async function toggleBuyMe(idx: string) {
		// Sanity check
		if (albumStore.modelAlbum === undefined) return;

		// Load the order if not already done
		await orderManagement.load();
		if (orderManagement.order === undefined) return;

		// Check if the item is already in the order
		const item = orderManagement.order.items?.find(
			(i: App.Http.Resources.Shop.OrderItemResource) => i.photo_id === idx && i.album_id === albumStore.modelAlbum?.id,
		);
		// If it is, remove it and exit
		if (item !== undefined) {
			orderManagement.removeItem(item.id);
			return;
		}

		// Now we now we must add it.
		buyablePhotoId.value = idx;
		buyableAlbumId.value = albumStore.modelAlbum.id;
		// Either there is only 1 option in the catalog, if so we take that one
		// For now we focus only on album purchasable
		// Later we want to also check if the photo is purchasable individually
		if (catalogStore.catalog?.album_purchasable?.prices?.length === 1) {
			const sizeVariant = catalogStore.catalog.album_purchasable.prices[0].size_variant;
			const licenseType = catalogStore.catalog.album_purchasable.prices[0].license_type;
			addPhotoToOrder(sizeVariant, licenseType);
			return;
		}

		// If we are here, we have multiple options, so we need to ask the user
		prices.value = catalogStore.catalog?.album_purchasable?.prices ?? [];
		showBuyMeDialog.value = true;
	}

	function addPhotoToOrder(size_variant: App.Enum.PurchasableSizeVariantType, license_type: App.Enum.PurchasableLicenseType) {
		showBuyMeDialog.value = false;

		if (buyablePhotoId.value === undefined || albumStore.modelAlbum === undefined) return;

		orderManagement
			.addPhoto({
				photo_id: buyablePhotoId.value,
				album_id: buyableAlbumId.value,
				size_variant: size_variant,
				license_type: license_type,
			})
			.finally(resetBuyMeDialog);
	}

	function resetBuyMeDialog() {
		buyablePhotoId.value = undefined;
		buyableAlbumId.value = undefined;
		prices.value = [];
		showBuyMeDialog.value = false;
	}

	return {
		showBuyMeDialog,
		buyableAlbumId,
		buyablePhotoId,
		prices,
		toggleBuyMe,
		addPhotoToOrder,
		resetBuyMeDialog,
	};
}
