export type SelectOption<T> = {
	value: T;
	label: string;
};

export const photoSortingColumnsOptions: SelectOption<App.Enum.ColumnSortingPhotoType>[] = [
	{ value: "created_at", label: "lychee.SORT_PHOTO_SELECT_1" },
	{ value: "taken_at", label: "lychee.SORT_PHOTO_SELECT_2" },
	{ value: "title", label: "lychee.SORT_PHOTO_SELECT_3" },
	{ value: "description", label: "lychee.SORT_PHOTO_SELECT_4" },
	{ value: "is_starred", label: "lychee.SORT_PHOTO_SELECT_6" },
	{ value: "type", label: "lychee.SORT_PHOTO_SELECT_7" },
];

export const sortingOrdersOptions: SelectOption<App.Enum.OrderSortingType>[] = [
	{ value: "ASC", label: "lychee.SORT_ASCENDING" },
	{ value: "DESC", label: "lychee.SORT_DESCENDING" },
];

export const albumSortingColumnsOptions: SelectOption<App.Enum.ColumnSortingAlbumType>[] = [
	{ value: "created_at", label: "lychee.SORT_ALBUM_SELECT_1" },
	{ value: "title", label: "lychee.SORT_ALBUM_SELECT_2" },
	{ value: "description", label: "lychee.SORT_ALBUM_SELECT_3" },
	{ value: "min_taken_at", label: "lychee.SORT_ALBUM_SELECT_6" },
	{ value: "max_taken_at", label: "lychee.SORT_ALBUM_SELECT_5" },
];

export const aspectRationOptions: SelectOption<App.Enum.AspectRatioType>[] = [
	{ value: "5/4", label: "aspect_ratio.5by4" },
	{ value: "3/2", label: "aspect_ratio.3by2" },
	{ value: "1/1", label: "aspect_ratio.1by1" },
	{ value: "2/3", label: "aspect_ratio.2by3" },
	{ value: "4/5", label: "aspect_ratio.4by5" },
	{ value: "16/9", label: "aspect_ratio.1byx9" },
];

export const licenseOptions: SelectOption<App.Enum.LicenseType>[] = [
	{ value: "none", label: "None" },
	{ value: "reserved", label: "lychee.ALBUM_RESERVED" },
	{ value: "CC0", label: "CC0 - Public Domain" },
	{ value: "CC-BY-1.0", label: "CC Attribution 1.0" },
	{ value: "CC-BY-2.0", label: "CC Attribution 2.0" },
	{ value: "CC-BY-2.5", label: "CC Attribution 2.5" },
	{ value: "CC-BY-3.0", label: "CC Attribution 3.0" },
	{ value: "CC-BY-4.0", label: "CC Attribution 4.0" },
	{ value: "CC-BY-ND-1.0", label: "CC Attribution-NoDerivatives 1.0" },
	{ value: "CC-BY-ND-2.0", label: "CC Attribution-NoDerivatives 2.0" },
	{ value: "CC-BY-ND-2.5", label: "CC Attribution-NoDerivatives 2.5" },
	{ value: "CC-BY-ND-3.0", label: "CC Attribution-NoDerivatives 3.0" },
	{ value: "CC-BY-ND-4.0", label: "CC Attribution-NoDerivatives 4.0" },
	{ value: "CC-BY-SA-1.0", label: "CC Attribution-ShareAlike 1.0" },
	{ value: "CC-BY-SA-2.0", label: "CC Attribution-ShareAlike 2.0" },
	{ value: "CC-BY-SA-2.5", label: "CC Attribution-ShareAlike 2.5" },
	{ value: "CC-BY-SA-3.0", label: "CC Attribution-ShareAlike 3.0" },
	{ value: "CC-BY-SA-4.0", label: "CC Attribution-ShareAlike 4.0" },
	{ value: "CC-BY-NC-1.0", label: "CC Attribution-NonCommercial 1.0" },
	{ value: "CC-BY-NC-2.0", label: "CC Attribution-NonCommercial 2.0" },
	{ value: "CC-BY-NC-2.5", label: "CC Attribution-NonCommercial 2.5" },
	{ value: "CC-BY-NC-3.0", label: "CC Attribution-NonCommercial 3.0" },
	{ value: "CC-BY-NC-4.0", label: "CC Attribution-NonCommercial 4.0" },
	{ value: "CC-BY-NC-ND-1.0", label: "CC Attribution-NonCommercial-NoDerivatives 1.0" },
	{ value: "CC-BY-NC-ND-2.0", label: "CC Attribution-NonCommercial-NoDerivatives 2.0" },
	{ value: "CC-BY-NC-ND-2.5", label: "CC Attribution-NonCommercial-NoDerivatives 2.5" },
	{ value: "CC-BY-NC-ND-3.0", label: "CC Attribution-NonCommercial-NoDerivatives 3.0" },
	{ value: "CC-BY-NC-ND-4.0", label: "CC Attribution-NonCommercial-NoDerivatives 4.0" },
	{ value: "CC-BY-NC-SA-1.0", label: "CC Attribution-NonCommercial-ShareAlike 1.0" },
	{ value: "CC-BY-NC-SA-2.0", label: "CC Attribution-NonCommercial-ShareAlike 2.0" },
	{ value: "CC-BY-NC-SA-2.5", label: "CC Attribution-NonCommercial-ShareAlike 2.5" },
	{ value: "CC-BY-NC-SA-3.0", label: "CC Attribution-NonCommercial-ShareAlike 3.0" },
	{ value: "CC-BY-NC-SA-4.0", label: "CC Attribution-NonCommercial-ShareAlike 4.0" },
];

export const photoLayoutOptions: SelectOption<App.Enum.PhotoLayoutType>[] = [
	{ value: "square", label: "lychee.LAYOUT_SQUARES" },
	{ value: "justified", label: "lychee.LAYOUT_JUSTIFIED" },
	{ value: "masonry", label: "lychee.LAYOUT_MASONRY" },
	{ value: "grid", label: "lychee.LAYOUT_GRID" },
];

export const defaultAlbumProtectionOptions: SelectOption<string>[] = [
	{ value: "1", label: "private" },
	{ value: "2", label: "public" },
	{ value: "3", label: "inherit from parent" },
];

export const mapProvidersOptions: SelectOption<App.Enum.MapProviders>[] = [
	{ value: "Wikimedia", label: "Wikimedia" },
	{ value: "OpenStreetMap.org", label: "OpenStreetMap.org" },
	{ value: "OpenStreetMap.de", label: "OpenStreetMap.de" },
	{ value: "OpenStreetMap.fr", label: "OpenStreetMap.fr" },
	{ value: "RRZE", label: "RRZE" },
];

export const overlayOptions: SelectOption<App.Enum.ImageOverlayType>[] = [
	{ value: "exif", label: "lychee.OVERLAY_EXIF" },
	{ value: "desc", label: "lychee.OVERLAY_DESCRIPTION" },
	{ value: "date", label: "lychee.OVERLAY_DATE" },
	{ value: "none", label: "lychee.OVERLAY_NONE" },
];

export const toolsOptions: SelectOption<string>[] = [
	{ value: "0", label: "disabled" },
	{ value: "1", label: "enabled" },
	{ value: "2", label: "discover" },
];

export const SelectBuilders = {
	buildPhotoSorting(value: string | App.Enum.ColumnSortingType | undefined): SelectOption<App.Enum.ColumnSortingPhotoType> | undefined {
		return photoSortingColumnsOptions.find((option) => option.value === value) || undefined;
	},

	buildSortingOrder(value: string | App.Enum.OrderSortingType | undefined): SelectOption<App.Enum.OrderSortingType> | undefined {
		return sortingOrdersOptions.find((option) => option.value === value) || undefined;
	},

	buildAlbumSorting(value: string | App.Enum.ColumnSortingType | undefined): SelectOption<App.Enum.ColumnSortingAlbumType> | undefined {
		return albumSortingColumnsOptions.find((option) => option.value === value) || undefined;
	},

	buildAspectRatio(value: string | App.Enum.AspectRatioType | null): SelectOption<App.Enum.AspectRatioType> | undefined {
		return aspectRationOptions.find((option) => option.value === value) || undefined;
	},

	buildLicense(value: string | App.Enum.LicenseType | null): SelectOption<App.Enum.LicenseType> | undefined {
		return licenseOptions.find((option) => option.value === value) || undefined;
	},

	buildPhotoLayout(value: string | App.Enum.PhotoLayoutType | undefined): SelectOption<App.Enum.PhotoLayoutType> | undefined {
		return photoLayoutOptions.find((option) => option.value === value) || undefined;
	},

	buildMapProvider(value: string | App.Enum.MapProviders | undefined): SelectOption<App.Enum.MapProviders> | undefined {
		return mapProvidersOptions.find((option) => option.value === value) || undefined;
	},

	buildDefaultAlbumProtection(value: string | undefined): SelectOption<string> | undefined {
		return defaultAlbumProtectionOptions.find((option) => option.value === value) || undefined;
	},

	buildOverlay(value: string | App.Enum.ImageOverlayType | undefined): SelectOption<App.Enum.ImageOverlayType> | undefined {
		return overlayOptions.find((option) => option.value === value) || undefined;
	},

	buildToolSelection(value: string | undefined): SelectOption<string> | undefined {
		return toolsOptions.find((option) => option.value === value) || undefined;
	},
};
