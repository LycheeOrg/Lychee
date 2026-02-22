// This constant is used to define albums that should not be queried to server (e.g. in search).
export const ALL = "all";

export type SelectOption<T> = {
	value: T;
	label: string;
};

export type CheckoutSteps = "info" | "payment" | "completed" | "cancelled" | "failed";

export const photoSortingColumnsOptions: SelectOption<App.Enum.ColumnSortingPhotoType>[] = [
	{ value: "created_at", label: "gallery.sort.photo_select_1" },
	{ value: "taken_at", label: "gallery.sort.photo_select_2" },
	{ value: "title", label: "gallery.sort.photo_select_3" },
	{ value: "description", label: "gallery.sort.photo_select_4" },
	{ value: "title_strict", label: "gallery.sort.photo_select_3_strict" },
	{ value: "description_strict", label: "gallery.sort.photo_select_4_strict" },
	{ value: "is_highlighted", label: "gallery.sort.photo_select_6" },
	{ value: "type", label: "gallery.sort.photo_select_7" },
];

export const sortingOrdersOptions: SelectOption<App.Enum.OrderSortingType>[] = [
	{ value: "ASC", label: "gallery.sort.ascending" },
	{ value: "DESC", label: "gallery.sort.descending" },
];

export const albumSortingColumnsOptions: SelectOption<App.Enum.ColumnSortingAlbumType>[] = [
	{ value: "created_at", label: "gallery.sort.album_select_1" },
	{ value: "title", label: "gallery.sort.album_select_2" },
	{ value: "description", label: "gallery.sort.album_select_3" },
	{ value: "title_strict", label: "gallery.sort.album_select_2_strict" },
	{ value: "description_strict", label: "gallery.sort.album_select_3_strict" },
	{ value: "min_taken_at", label: "gallery.sort.album_select_6" },
	{ value: "max_taken_at", label: "gallery.sort.album_select_5" },
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
	{ value: "reserved", label: "gallery.album_reserved" },
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
	{ value: "square", label: "gallery.layout.squares" },
	{ value: "justified", label: "gallery.layout.justified" },
	{ value: "masonry", label: "gallery.layout.masonry" },
	{ value: "grid", label: "gallery.layout.grid" },
];

export const defaultAlbumProtectionOptions: SelectOption<string>[] = [
	{ value: "private", label: "gallery.albums_protection.private" },
	{ value: "public", label: "gallery.albums_protection.public" },
	{ value: "inherit", label: "gallery.albums_protection.inherit_from_parent" },
	{ value: "public_hidden", label: "gallery.albums_protection.public_but_hidden" },
];

export const mapProvidersOptions: SelectOption<App.Enum.MapProviders>[] = [
	{ value: "Wikimedia", label: "Wikimedia" },
	{ value: "OpenStreetMap.org", label: "OpenStreetMap.org" },
	{ value: "OpenStreetMap.de", label: "OpenStreetMap.de" },
	{ value: "OpenStreetMap.fr", label: "OpenStreetMap.fr" },
	{ value: "RRZE", label: "RRZE" },
];

export const overlayOptions: SelectOption<App.Enum.ImageOverlayType>[] = [
	{ value: "exif", label: "gallery.overlay.exif" },
	{ value: "desc", label: "gallery.overlay.description" },
	{ value: "date", label: "gallery.overlay.date" },
	{ value: "none", label: "gallery.overlay.none" },
];

export const toolsOptions: SelectOption<string>[] = [
	{ value: "0", label: "settings.tool_option.disabled" },
	{ value: "1", label: "settings.tool_option.enabled" },
	{ value: "2", label: "settings.tool_option.discover" },
];

export const timelinePhotoGranularityOptions: SelectOption<App.Enum.TimelinePhotoGranularity>[] = [
	{ value: "default", label: "gallery.timeline.default" },
	{ value: "disabled", label: "gallery.timeline.disabled" },
	{ value: "year", label: "gallery.timeline.year" },
	{ value: "month", label: "gallery.timeline.month" },
	{ value: "day", label: "gallery.timeline.day" },
	{ value: "hour", label: "gallery.timeline.hour" },
];

export const timelineAlbumGranularityOptions: SelectOption<App.Enum.TimelineAlbumGranularity>[] = [
	{ value: "default", label: "gallery.timeline.default" },
	{ value: "disabled", label: "gallery.timeline.disabled" },
	{ value: "year", label: "gallery.timeline.year" },
	{ value: "month", label: "gallery.timeline.month" },
	{ value: "day", label: "gallery.timeline.day" },
];

export const paginationUiModeOptions: SelectOption<App.Enum.PaginationMode>[] = [
	{ value: "infinite_scroll", label: "gallery.pagination.infinite_scroll" },
	{ value: "load_more_button", label: "gallery.pagination.load_more_button" },
	{ value: "page_navigation", label: "gallery.pagination.page_navigation" },
];

export const timeZoneOptions: SelectOption<string>[] = [
	{ value: "-12:00", label: "-12:00 Uninhabited areas (Baker Island)" },
	{ value: "-11:00", label: "-11:00 Niue Time" },
	{ value: "-10:00", label: "-10:00 HST Hawaii Standard Time" },
	{ value: "-09:00", label: "-09:00 AKST Alaska Standard Time" },
	{ value: "-08:00", label: "-08:00 PST Pacific Standard Time (US)" },
	{ value: "-07:00", label: "-07:00 MST Mountain Standard Time (US)" },
	{ value: "-06:00", label: "-06:00 CST (US) Central Standard Time (US)" },
	{ value: "-05:00", label: "-05:00 EST/ACT Eastern Standard Time (US)/Acre Time" },
	{ value: "-04:00", label: "-04:00 AMT Amazon Time" },
	{ value: "-03:30", label: "-03:30 NST Newfoundland Standard Time" },
	{ value: "-03:00", label: "-03:00 ADT Atlantic Daylight Time" },
	{ value: "-02:00", label: "-02:00 South Georgia and Sandwich Islands" },
	{ value: "-01:00", label: "-01:00 Azores Time" },
	{ value: "+00:00", label: "+00:00 UTC/GMT Coordinated Universal Time/Greenwich Mean Time" },
	{ value: "+01:00", label: "+01:00 CET/BST Central European Time/British Summer Time" },
	{ value: "+02:00", label: "+02:00 EET/CEST Eastern European Time/Central European Summer Time" },
	{ value: "+03:00", label: "+03:00 MSK/AST/EEST Moscow Standard Time/Arabian Standard Time/Eastern European Summer Time" },
	{ value: "+03:30", label: "+03:30 IRST Iran Standard Time" },
	{ value: "+04:00", label: "+04:00 GST/AZT Gulf Standard Time/Azerbaijan Time" },
	{ value: "+04:30", label: "+04:30 AFT Afghanistan Time" },
	{ value: "+05:00", label: "+05:00 PKT Pakistan Standard Time" },
	{ value: "+05:30", label: "+05:30 IST Indian Standard Time" },
	{ value: "+06:00", label: "+06:00 Bhutan Time" },
	{ value: "+07:00", label: "+07:00 ICT Indochina Time" },
	{ value: "+08:00", label: "+08:00 CST (China)/HKT China Standard Time/Hong Kong Time" },
	{ value: "+09:00", label: "+09:00 JST/KST Japan Standard Time/Korea Standard Time" },
	{ value: "+09:30", label: "+09:30 ACST Australian Central Standard Time" },
	{ value: "+10:00", label: "+10:00 AEST Australian Eastern Standard Time" },
	{ value: "+11:00", label: "+11:00 Solomon Islands Time" },
	{ value: "+12:00", label: "+12:00 NZST New Zealand Standard Time" },
];

export const currencyOptions: SelectOption<string>[] = [
	{ value: "ALL", label: "ALL - Albania Lek" },
	{ value: "AFN", label: "AFN - Afghanistan Afghani" },
	{ value: "ARS", label: "ARS - Argentina Peso" },
	{ value: "AWG", label: "AWG - Aruba Guilder" },
	{ value: "AUD", label: "AUD - Australia Dollar" },
	{ value: "AZN", label: "AZN - Azerbaijan New Manat" },
	{ value: "BSD", label: "BSD - Bahamas Dollar" },
	{ value: "BBD", label: "BBD - Barbados Dollar" },
	{ value: "BDT", label: "BDT - Bangladeshi taka" },
	{ value: "BYR", label: "BYR - Belarus Ruble" },
	{ value: "BZD", label: "BZD - Belize Dollar" },
	{ value: "BMD", label: "BMD - Bermuda Dollar" },
	{ value: "BOB", label: "BOB - Bolivia Boliviano" },
	{ value: "BAM", label: "BAM - Bosnia and Herzegovina Convertible Marka" },
	{ value: "BWP", label: "BWP - Botswana Pula" },
	{ value: "BGN", label: "BGN - Bulgaria Lev" },
	{ value: "BRL", label: "BRL - Brazil Real" },
	{ value: "BND", label: "BND - Brunei Darussalam Dollar" },
	{ value: "KHR", label: "KHR - Cambodia Riel" },
	{ value: "CAD", label: "CAD - Canada Dollar" },
	{ value: "KYD", label: "KYD - Cayman Islands Dollar" },
	{ value: "CLP", label: "CLP - Chile Peso" },
	{ value: "CNY", label: "CNY - China Yuan Renminbi" },
	{ value: "COP", label: "COP - Colombia Peso" },
	{ value: "CRC", label: "CRC - Costa Rica Colon" },
	{ value: "HRK", label: "HRK - Croatia Kuna" },
	{ value: "CUP", label: "CUP - Cuba Peso" },
	{ value: "CZK", label: "CZK - Czech Republic Koruna" },
	{ value: "DKK", label: "DKK - Denmark Krone" },
	{ value: "DOP", label: "DOP - Dominican Republic Peso" },
	{ value: "XCD", label: "XCD - East Caribbean Dollar" },
	{ value: "EGP", label: "EGP - Egypt Pound" },
	{ value: "SVC", label: "SVC - El Salvador Colon" },
	{ value: "EUR", label: "EUR - Euro Member Countries" },
	{ value: "FKP", label: "FKP - Falkland Islands (Malvinas) Pound" },
	{ value: "FJD", label: "FJD - Fiji Dollar" },
	{ value: "GHC", label: "GHC - Ghana Cedis" },
	{ value: "GIP", label: "GIP - Gibraltar Pound" },
	{ value: "GTQ", label: "GTQ - Guatemala Quetzal" },
	{ value: "GGP", label: "GGP - Guernsey Pound" },
	{ value: "GYD", label: "GYD - Guyana Dollar" },
	{ value: "HNL", label: "HNL - Honduras Lempira" },
	{ value: "HKD", label: "HKD - Hong Kong Dollar" },
	{ value: "HUF", label: "HUF - Hungary Forint" },
	{ value: "ISK", label: "ISK - Iceland Krona" },
	{ value: "INR", label: "INR - India Rupee" },
	{ value: "IDR", label: "IDR - Indonesia Rupiah" },
	{ value: "IRR", label: "IRR - Iran Rial" },
	{ value: "IMP", label: "IMP - Isle of Man Pound" },
	{ value: "ILS", label: "ILS - Israel Shekel" },
	{ value: "JMD", label: "JMD - Jamaica Dollar" },
	{ value: "JPY", label: "JPY - Japan Yen" },
	{ value: "JEP", label: "JEP - Jersey Pound" },
	{ value: "KZT", label: "KZT - Kazakhstan Tenge" },
	{ value: "KPW", label: "KPW - Korea (North) Won" },
	{ value: "KRW", label: "KRW - Korea (South) Won" },
	{ value: "KGS", label: "KGS - Kyrgyzstan Som" },
	{ value: "LAK", label: "LAK - Laos Kip" },
	{ value: "LBP", label: "LBP - Lebanon Pound" },
	{ value: "LRD", label: "LRD - Liberia Dollar" },
	{ value: "MKD", label: "MKD - Macedonia Denar" },
	{ value: "MYR", label: "MYR - Malaysia Ringgit" },
	{ value: "MUR", label: "MUR - Mauritius Rupee" },
	{ value: "MXN", label: "MXN - Mexico Peso" },
	{ value: "MNT", label: "MNT - Mongolia Tughrik" },
	{ value: "MZN", label: "MZN - Mozambique Metical" },
	{ value: "NAD", label: "NAD - Namibia Dollar" },
	{ value: "NPR", label: "NPR - Nepal Rupee" },
	{ value: "ANG", label: "ANG - Netherlands Antilles Guilder" },
	{ value: "NZD", label: "NZD - New Zealand Dollar" },
	{ value: "NIO", label: "NIO - Nicaragua Cordoba" },
	{ value: "NGN", label: "NGN - Nigeria Naira" },
	{ value: "NOK", label: "NOK - Norway Krone" },
	{ value: "OMR", label: "OMR - Oman Rial" },
	{ value: "PKR", label: "PKR - Pakistan Rupee" },
	{ value: "PAB", label: "PAB - Panama Balboa" },
	{ value: "PYG", label: "PYG - Paraguay Guarani" },
	{ value: "PEN", label: "PEN - Peru Nuevo Sol" },
	{ value: "PHP", label: "PHP - Philippines Peso" },
	{ value: "PLN", label: "PLN - Poland Zloty" },
	{ value: "QAR", label: "QAR - Qatar Riyal" },
	{ value: "RON", label: "RON - Romania New Leu" },
	{ value: "RUB", label: "RUB - Russia Ruble" },
	{ value: "SHP", label: "SHP - Saint Helena Pound" },
	{ value: "SAR", label: "SAR - Saudi Arabia Riyal" },
	{ value: "RSD", label: "RSD - Serbia Dinar" },
	{ value: "SCR", label: "SCR - Seychelles Rupee" },
	{ value: "SGD", label: "SGD - Singapore Dollar" },
	{ value: "SBD", label: "SBD - Solomon Islands Dollar" },
	{ value: "SOS", label: "SOS - Somalia Shilling" },
	{ value: "ZAR", label: "ZAR - South Africa Rand" },
	{ value: "LKR", label: "LKR - Sri Lanka Rupee" },
	{ value: "SEK", label: "SEK - Sweden Krona" },
	{ value: "CHF", label: "CHF - Switzerland Franc" },
	{ value: "SRD", label: "SRD - Suriname Dollar" },
	{ value: "SYP", label: "SYP - Syria Pound" },
	{ value: "TWD", label: "TWD - Taiwan New Dollar" },
	{ value: "THB", label: "THB - Thailand Baht" },
	{ value: "TTD", label: "TTD - Trinidad and Tobago Dollar" },
	{ value: "TRY", label: "TRY - Turkey Lira" },
	{ value: "TRL", label: "TRL - Turkey Lira" },
	{ value: "TVD", label: "TVD - Tuvalu Dollar" },
	{ value: "UAH", label: "UAH - Ukraine Hryvna" },
	{ value: "GBP", label: "GBP - United Kingdom Pound" },
	{ value: "UGX", label: "UGX - Uganda Shilling" },
	{ value: "USD", label: "USD - United States Dollar" },
	{ value: "UYU", label: "UYU - Uruguay Peso" },
	{ value: "UZS", label: "UZS - Uzbekistan Som" },
	{ value: "VEF", label: "VEF - Venezuela Bolivar" },
	{ value: "VND", label: "VND - Viet Nam Dong" },
	{ value: "YER", label: "YER - Yemen Rial" },
	{ value: "ZWD", label: "ZWD - Zimbabwe Dollar" },
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

	buildAspectRatio(value: string | App.Enum.AspectRatioType | undefined): SelectOption<App.Enum.AspectRatioType> | undefined {
		return aspectRationOptions.find((option) => option.value === value) || undefined;
	},

	buildLicense(value: string | App.Enum.LicenseType | undefined): SelectOption<App.Enum.LicenseType> | undefined {
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

	buildCurrencySelection(value: string | undefined): SelectOption<string> | undefined {
		return currencyOptions.find((option) => option.value === value) || undefined;
	},

	buildTimelinePhotoGranularity(
		value: string | App.Enum.TimelinePhotoGranularity | undefined,
	): SelectOption<App.Enum.TimelinePhotoGranularity> | undefined {
		return timelinePhotoGranularityOptions.find((option) => option.value === value) || undefined;
	},

	buildTimelineAlbumGranularity(
		value: string | App.Enum.TimelineAlbumGranularity | undefined,
	): SelectOption<App.Enum.TimelineAlbumGranularity> | undefined {
		return timelineAlbumGranularityOptions.find((option) => option.value === value) || undefined;
	},

	buildPaginationUiMode(value: string | App.Enum.PaginationMode): SelectOption<App.Enum.PaginationMode> | undefined {
		return paginationUiModeOptions.find((option) => option.value === value) || undefined;
	},
};
