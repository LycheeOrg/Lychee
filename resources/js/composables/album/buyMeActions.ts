import { AlbumStore } from "@/stores/AlbumState";
import { CatalogStore } from "@/stores/CatalogState";
import { OrderManagementStateStore } from "@/stores/OrderManagement";
import { ToastServiceMethods } from "primevue/toastservice";
import { ref } from "vue";
import { trans } from "laravel-vue-i18n";
import { sprintf } from "sprintf-js";
import { PhotosStore } from "@/stores/PhotosState";
import WebshopService from "@/services/webshop-service";

const buyablePhotoId = ref<string | undefined>(undefined);
const buyableAlbumId = ref<string | undefined>(undefined);
const prices = ref<App.Http.Resources.Shop.PriceResource[]>([]);
const showBuyMeDialog = ref(false);

// Print/pixel sizes fetched from the catalogue endpoint
const printSizes = ref<App.Http.Resources.Shop.PurchasablePrintSizeResource[]>([]);
const pixelSizes = ref<App.Http.Resources.Shop.PurchasablePixelSizeResource[]>([]);
// Selected item type: 'digital' | 'print' | 'pixel'
const buyMeItemType = ref<"digital" | "print" | "pixel">("digital");

export function useBuyMeActions(
	albumStore: AlbumStore,
	photosStore: PhotosStore,
	orderManagement: OrderManagementStateStore,
	catalogStore: CatalogStore,
	toast: ToastServiceMethods,
) {
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

		const photoToAdd = photosStore.photos.find((p) => p.id === idx);
		if (photoToAdd === undefined) {
			// Photo not found
			return;
		}

		// Filter out the options which do not exists on the photo
		const pricesOptions = getPricesOptionsForPhoto(photoToAdd);

		// Fetch print/pixel catalogue sizes for the purchasable
		const purchasable = catalogStore.catalog?.album_purchasable;
		if (purchasable?.purchasable_id !== undefined) {
			try {
				const sizesResponse = await WebshopService.Catalog.getCatalogueSizes(purchasable.purchasable_id);
				printSizes.value = sizesResponse.data.print_sizes ?? [];
				pixelSizes.value = sizesResponse.data.pixel_sizes ?? [];
			} catch {
				printSizes.value = [];
				pixelSizes.value = [];
			}
		} else {
			printSizes.value = [];
			pixelSizes.value = [];
		}

		const hasSizeVariants = pricesOptions.length > 0;
		const hasPrintSizes = printSizes.value.length > 0;
		const hasPixelSizes = pixelSizes.value.length > 0;

		if (!hasSizeVariants && !hasPrintSizes && !hasPixelSizes) {
			// Nothing is buyable
			return;
		}

		// Now we know we must add it
		buyablePhotoId.value = idx;
		buyableAlbumId.value = albumStore.modelAlbum.id;

		// If exactly one digital price and no print/pixel options, add directly
		if (pricesOptions.length === 1 && !hasPrintSizes && !hasPixelSizes) {
			const sizeVariant = pricesOptions[0].size_variant;
			const licenseType = pricesOptions[0].license_type;
			addPhotoToOrder(sizeVariant, licenseType);
			notify(photoToAdd.title, pricesOptions[0].price);
			return;
		}

		// Multiple options or print/pixel options — show dialog
		prices.value = pricesOptions;
		buyMeItemType.value = hasSizeVariants ? "digital" : hasPrintSizes ? "print" : "pixel";
		showBuyMeDialog.value = true;
	}

	function notify(photoTitle: string, price: string) {
		toast.add({
			severity: "success",
			summary: trans("webshop.buyMeActions.addedToOrder"),
			detail: sprintf(trans("webshop.buyMeActions.photoAddedToOrder"), photoTitle, price),
			life: 3000,
		});
	}

	function addPhotoToOrder(size_variant: App.Enum.PurchasableSizeVariantType, license_type: App.Enum.PurchasableLicenseType) {
		showBuyMeDialog.value = false;

		if (buyablePhotoId.value === undefined || albumStore.modelAlbum === undefined) {
			return;
		}

		const photo = photosStore.photos.find((p) => p.id === buyablePhotoId.value);
		if (photo === undefined) {
			return;
		}

		const priceOption = getPricesOptionsForPhoto(photo).find((p) => p.size_variant === size_variant && p.license_type === license_type);
		if (priceOption === undefined) {
			return;
		}

		orderManagement
			.addPhoto({
				photo_id: buyablePhotoId.value,
				album_id: buyableAlbumId.value,
				size_variant: size_variant,
				license_type: license_type,
			})
			.then(() => {
				notify(photo.title, priceOption.price);
			})
			.finally(resetBuyMeDialog);
	}

	function addPrintPhotoToOrder(printSizeId: number, price: string) {
		showBuyMeDialog.value = false;

		if (buyablePhotoId.value === undefined) {
			return;
		}

		const photo = photosStore.photos.find((p) => p.id === buyablePhotoId.value);

		orderManagement
			.addPrintPhoto({
				photo_id: buyablePhotoId.value,
				album_id: buyableAlbumId.value,
				print_size_id: printSizeId,
			})
			.then(() => {
				notify(photo?.title ?? "", price);
			})
			.finally(resetBuyMeDialog);
	}

	function addPixelPhotoToOrder(pixelSizeId: number, licenseType: App.Enum.PurchasableLicenseType, price: string) {
		showBuyMeDialog.value = false;

		if (buyablePhotoId.value === undefined) {
			return;
		}

		const photo = photosStore.photos.find((p) => p.id === buyablePhotoId.value);

		orderManagement
			.addPixelPhoto({
				photo_id: buyablePhotoId.value,
				album_id: buyableAlbumId.value,
				pixel_size_id: pixelSizeId,
				license_type: licenseType,
			})
			.then(() => {
				notify(photo?.title ?? "", price);
			})
			.finally(resetBuyMeDialog);
	}

	function resetBuyMeDialog() {
		buyablePhotoId.value = undefined;
		buyableAlbumId.value = undefined;
		prices.value = [];
		printSizes.value = [];
		pixelSizes.value = [];
		showBuyMeDialog.value = false;
		buyMeItemType.value = "digital";
	}

	function getPricesOptionsForPhoto(
		photo: App.Http.Resources.Models.PhotoResource,
		pricesOptions?: App.Http.Resources.Shop.PriceResource[] | null,
	): App.Http.Resources.Shop.PriceResource[] {
		if (pricesOptions === undefined || pricesOptions === null) {
			pricesOptions = catalogStore.catalog?.album_purchasable?.prices;
			if (pricesOptions === undefined || pricesOptions === null || pricesOptions.length === 0) {
				// Nothing is buyable
				return [];
			}
		}

		// Filter out the options which do not exists on the photo
		return pricesOptions.filter((price) => {
			if (price.size_variant === "full" || price.size_variant === "original") {
				return true; // Original & Full always exists
			}
			if (price.size_variant === "medium" && photo.size_variants.medium !== null) {
				return true;
			}
			if (price.size_variant === "medium2x" && photo.size_variants.medium2x !== null) {
				return true;
			}
			return false;
		});
	}

	return {
		showBuyMeDialog,
		buyableAlbumId,
		buyablePhotoId,
		prices,
		printSizes,
		pixelSizes,
		buyMeItemType,
		toggleBuyMe,
		addPhotoToOrder,
		addPrintPhotoToOrder,
		addPixelPhotoToOrder,
		resetBuyMeDialog,
		getPricesOptionsForPhoto,
	};
}
