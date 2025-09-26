import { OrderManagementStateStore } from "@/stores/OrderManagement";
import { ref, Ref } from "vue";

const buyablePhotoId = ref<string | undefined>(undefined);
const buyableAlbumId = ref<string | undefined>(undefined);
const prices = ref<App.Http.Resources.Shop.PriceResource[]>([]);
const showBuyMeDialog = ref(false);

export function useBuyMeActions(
	modelAlbum: Ref<App.Http.Resources.Models.AlbumResource | undefined>,
	orderManagement: OrderManagementStateStore,
	catalog: Ref<App.Http.Resources.Shop.CatalogResource | undefined>,
) {
	async function toggleBuyMe(idx: string) {
		// Sanity check
		if (modelAlbum.value === undefined) return;

		// Load the order if not already done
		await orderManagement.load();
		if (orderManagement.order === undefined) return;

		// Check if the item is already in the order
		const item = orderManagement.order.items?.find(
			(i: App.Http.Resources.Shop.OrderItemResource) => i.photo_id === idx && i.album_id === modelAlbum.value?.id,
		);
		// If it is, remove it and exit
		if (item !== undefined) {
			orderManagement.removeItem(item.id);
			return;
		}

		// Now we now we must add it.
		buyablePhotoId.value = idx;
		buyableAlbumId.value = modelAlbum.value.id;
		// Either there is only 1 option in the catalog, if so we take that one
		// For now we focus only on album purchasable
		// Later we want to also check if the photo is purchasable individually
		if (catalog.value?.album_purchasable?.prices?.length === 1) {
			const sizeVariant = catalog.value.album_purchasable.prices[0].size_variant;
			const licenseType = catalog.value.album_purchasable.prices[0].license_type;
			addPhotoToOrder(sizeVariant, licenseType);
			return;
		}

		// If we are here, we have multiple options, so we need to ask the user
		prices.value = catalog.value?.album_purchasable?.prices ?? [];
		showBuyMeDialog.value = true;
	}

	function addPhotoToOrder(size_variant: App.Enum.PurchasableSizeVariantType, license_type: App.Enum.PurchasableLicenseType) {
		showBuyMeDialog.value = false;

		if (buyablePhotoId.value === undefined || modelAlbum.value === undefined) return;

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
