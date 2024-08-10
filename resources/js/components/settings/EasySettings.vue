<template>
	<div v-if="configs">
		<Fieldset legend="Dropbox" class="border-b-0 border-r-0 rounded-r-none rounded-b-none">
			<p class="mb-4 text-muted-color">
				In order to import photos from your Dropbox, you need a valid drop-ins app key from their website.
				<a href="https://www.dropbox.com/developers/saver" class="pl-2 border-b border-dashed border-b-primary-500 text-primary-500">
					<i class="pi pi-link"></i>
				</a>
			</p>
			<div class="flex gap-4">
				<FloatLabel class="w-full flex-grow">
					<InputPassword id="api_key" type="text" v-model="api_key" />
					<label for="api_key" class="text-muted-color">{{ $t("lychee.SETTINGS_DROPBOX_KEY") }}</label>
				</FloatLabel>
				<Button class="w-full flex-shrink-2">{{ $t("lychee.DROPBOX_TITLE") }}</Button>
			</div>
		</Fieldset>
		<Fieldset legend="System" class="border-b-0 border-r-0 rounded-r-none rounded-b-none">
			<div class="flex flex-col gap-4">
				<div class="pl-9">
					<ToggleSwitch id="pp_dialog_nsfw_visible" v-model="nsfwVisible" class="-ml-10 mr-2 translate-y-1 text-sm" />
					<label class="text-muted-color" for="pp_dialog_nsfw_visible">{{ $t("lychee.NSFW_VISIBLE_TEXT_1") }}</label>
					<p class="my-1.5 text-muted-color" v-html="nsfwText2"></p>
				</div>
				<!--
						<livewire:forms.settings.set-lang-setting />

						<FloatLabel>
				<Select
					id="lang"
					class="w-48 border-none"
					v-model="lang"
					:options="langsOptions"
					optionLabel="label"
					showClear
				>
					<template #value="slotProps">
						<div v-if="slotProps.value" class="flex items-center">
							<div>{{ $t(slotProps.value.label) }}</div>
						</div>
					</template>
					<template #option="slotProps">
						<div class="flex items-center">
							<div>{{ $t(slotProps.option.label) }}</div>
						</div>
					</template>
				</Select>
				<label for="lang">{{ $t("lychee.ALBUM_CHILDREN_ORDERING") }}</label>
			</FloatLabel> -->
				<!-- LANGUAGE -->
				<!-- Sensitive -->
			</div>
		</Fieldset>
		<Fieldset legend="Gallery" class="border-b-0 border-r-0 rounded-r-none rounded-b-none">
			<div class="flex flex-col gap-4">
				<!-- ALBUM ORDER -->
				<div class="flex">
					<FloatLabel>
						<Select
							id="albumSortingColumn"
							class="w-48 border-none"
							v-model="albumSortingColumn"
							:options="albumSortingColumnsOptions"
							optionLabel="label"
							showClear
						>
							<template #value="slotProps">
								<div v-if="slotProps.value" class="flex items-center">
									<div>{{ $t(slotProps.value.label) }}</div>
								</div>
							</template>
							<template #option="slotProps">
								<div class="flex items-center">
									<div>{{ $t(slotProps.option.label) }}</div>
								</div>
							</template>
						</Select>
						<label for="albumSortingColumn">{{ $t("lychee.ALBUM_CHILDREN_ORDERING") }}</label>
					</FloatLabel>
					<FloatLabel>
						<Select
							id="albumSortingOrder"
							class="w-48 border-none"
							v-model="albumSortingOrder"
							:options="sortingOrdersOptions"
							optionLabel="label"
							showClear
						>
							<template #value="slotProps">
								<div v-if="slotProps.value" class="flex items-center">
									<div>{{ $t(slotProps.value.label) }}</div>
								</div>
							</template>
							<template #option="slotProps">
								<div class="flex items-center">
									<div>{{ $t(slotProps.option.label) }}</div>
								</div>
							</template>
						</Select>
						<label for="albumSortingOrder">asc/desc</label>
					</FloatLabel>
				</div>
				<!-- PHOTO ORDER -->
				<div class="flex">
					<FloatLabel>
						<Select
							id="photoSortingColumn"
							class="w-48 border-none"
							v-model="photoSortingColumn"
							:options="photoSortingColumnsOptions"
							optionLabel="label"
							showClear
						>
							<template #value="slotProps">
								<div v-if="slotProps.value" class="flex items-center">
									<div>{{ $t(slotProps.value.label) }}</div>
								</div>
							</template>
							<template #option="slotProps">
								<div class="flex items-center">
									<div>{{ $t(slotProps.option.label) }}</div>
								</div>
							</template>
						</Select>
						<label for="photoSortingColumn">{{ $t("lychee.ALBUM_PHOTO_ORDERING") }}</label>
					</FloatLabel>
					<FloatLabel>
						<Select
							id="albumSortingOrder"
							class="w-48 border-none"
							v-model="albumSortingOrder"
							:options="sortingOrdersOptions"
							optionLabel="label"
							showClear
						>
							<template #value="slotProps">
								<div v-if="slotProps.value" class="flex items-center">
									<div>{{ $t(slotProps.value.label) }}</div>
								</div>
							</template>
							<template #option="slotProps">
								<div class="flex items-center">
									<div>{{ $t(slotProps.option.label) }}</div>
								</div>
							</template>
						</Select>
						<label for="albumSortingOrder">asc/desc</label>
					</FloatLabel>
				</div>
				<!-- PHOTO LAYOUT -->
				<div>
					<FloatLabel>
						<Select id="layout" class="w-96 border-none" v-model="layout" :options="photoLayoutOptions" optionLabel="label" showClear>
							<template #value="slotProps">
								<div v-if="slotProps.value" class="flex items-center">
									<div>{{ $t(slotProps.value.label) }}</div>
								</div>
							</template>
							<template #option="slotProps">
								<div class="flex items-center">
									<div>{{ $t(slotProps.option.label) }}</div>
								</div>
							</template>
						</Select>
						<label for="layout">{{ $t("lychee.LAYOUT_TYPE") }}</label>
					</FloatLabel>
				</div>

				<!-- THUMB ASPECT RATIO -->
				<div>
					<FloatLabel>
						<Select
							id="aspectRatio"
							class="w-96 border-none"
							v-model="aspectRatio"
							:options="aspectRationOptions"
							optionLabel="label"
							showClear
						>
							<template #value="slotProps">
								<div v-if="slotProps.value" class="flex items-center">
									<div>{{ $t(slotProps.value.label) }}</div>
								</div>
							</template>
							<template #option="slotProps">
								<div class="flex items-center">
									<div>{{ $t(slotProps.option.label) }}</div>
								</div>
							</template>
						</Select>
						<label for="aspectRatio">Set album thumbs aspect ratio</label>
					</FloatLabel>
				</div>
				<!-- LICENSE -->
				<div class="flex">
					<FloatLabel>
						<Select id="license" class="w-96 border-none" v-model="license" :options="licenseOptions" optionLabel="label" showClear>
							<template #value="slotProps">
								<div v-if="slotProps.value" class="flex items-center">
									<div>{{ $t(slotProps.value.label) }}</div>
								</div>
							</template>
							<template #option="slotProps">
								<div class="flex items-center">
									<div>{{ $t(slotProps.option.label) }}</div>
								</div>
							</template>
						</Select>
						<label for="license">{{ $t("lychee.ALBUM_SET_LICENSE") }}</label>
					</FloatLabel>
					<div class="mb-4 pl-4 text-muted-color">
						<p>
							{{ $t("lychee.ALBUM_LICENSE_HELP") }}
							<a
								href="https://creativecommons.org/choose/"
								target="_blank"
								class="pl-2 border-b border-dashed border-b-primary-500 text-primary-500"
							>
								<i class="pi pi-link"></i>
							</a>
						</p>
					</div>
				</div>
			</div>
		</Fieldset>
		<Fieldset legend="Geo-location" class="border-b-0 border-r-0 rounded-r-none rounded-b-none">
			<!-- MAP & locations -->
		</Fieldset>

		<!-- 
			<livewire:forms.settings.base.boolean-setting key="set-public_search"
				description="PUBLIC_SEARCH_TEXT" name="search_public" />
			<livewire:forms.settings.set-album-decoration-setting />
			<livewire:forms.settings.set-album-decoration-orientation-setting />
			<livewire:forms.settings.set-photo-overlay-setting />
			<livewire:forms.settings.base.boolean-setting key="set-map_display"
				description="MAP_DISPLAY_TEXT" name="map_display" />
			<livewire:forms.settings.base.boolean-setting key="set-map_display_public"
				description="MAP_DISPLAY_PUBLIC_TEXT" name="map_display_public" />
			<livewire:forms.settings.set-map-provider-setting />
			<livewire:forms.settings.base.boolean-setting key="set-map_include_subalbums"
				description="MAP_INCLUDE_SUBALBUMS_TEXT" name="map_include_subalbums" />
			<livewire:forms.settings.base.boolean-setting key="set-location_decoding"
				description="LOCATION_DECODING" name="location_decoding" />
			<livewire:forms.settings.base.boolean-setting key="set-location_show"
				description="LOCATION_SHOW" name="location_show" />
			<livewire:forms.settings.base.boolean-setting key="set-location_show_public"
				description="LOCATION_SHOW_PUBLIC" name="location_show_public" />
			<livewire:forms.settings.base.boolean-setting key="set-nsfw_visible"
				description="NSFW_VISIBLE_TEXT_1" name="nsfw_visible" footer="NSFW_VISIBLE_TEXT_2" />
			<livewire:forms.settings.base.boolean-setting key="set-new_photos_notification"
				description="NEW_PHOTOS_NOTIFICATION" name="new_photos_notification" /> -->
	</div>
</template>
<script setup lang="ts">
import { computed, ref } from "vue";
import Fieldset from "primevue/fieldset";
import FloatLabel from "primevue/floatlabel";
import Button from "primevue/button";
import Select from "primevue/select";
import ToggleSwitch from "primevue/toggleswitch";
import InputPassword from "@/components/forms/basic/InputPassword.vue";
import SettingsService from "@/services/settings-service";
import {
	photoSortingColumnsOptions,
	albumSortingColumnsOptions,
	sortingOrdersOptions,
	licenseOptions,
	aspectRationOptions,
	photoLayoutOptions,
	SelectBuilders,
	type SelectOption,
} from "@/config/constants";
import { trans } from "laravel-vue-i18n";

type EasySettings = {
	dropbox_key: string | undefined;
	sorting_photo: {
		col: App.Enum.ColumnSortingPhotoType;
		order: App.Enum.OrderSortingType;
	};
	sorting_album: {
		col: App.Enum.ColumnSortingAlbumType;
		order: App.Enum.OrderSortingType;
	};
	default_license: App.Enum.LicenseType;
	default_album_thumb_aspect_ratio: App.Enum.AspectRatioType;
	lang: string;
	layout: App.Enum.PhotoLayoutType;
	image_overlay_type: App.Enum.ImageOverlayType;
	search_public: boolean;
	map_display: boolean;
	location_show: boolean;
	location_show_public: boolean;
	nsfw_visible: boolean;
};
const configs = ref(undefined as undefined | App.Http.Resources.Collections.ConfigCollectionResource);
const api_key = ref(undefined as undefined | string);
const photoSortingColumn = ref(undefined as SelectOption<App.Enum.ColumnSortingPhotoType> | undefined);
const photoSortingOrder = ref(undefined as SelectOption<App.Enum.OrderSortingType> | undefined);
const albumSortingColumn = ref(undefined as SelectOption<App.Enum.ColumnSortingAlbumType> | undefined);
const albumSortingOrder = ref(undefined as SelectOption<App.Enum.OrderSortingType> | undefined);
const license = ref(undefined as SelectOption<App.Enum.LicenseType> | undefined);
const aspectRatio = ref(undefined as SelectOption<App.Enum.AspectRatioType> | undefined);
const lang = ref("en" as string);
const layout = ref(undefined as SelectOption<App.Enum.PhotoLayoutType> | undefined);
const loadedSettings = ref(undefined as undefined | EasySettings);
const nsfwVisible = ref(false);
const nsfwText2 = computed(() => {
	return trans("lychee.NSFW_VISIBLE_TEXT_2");
});

function load() {
	SettingsService.getAll().then((response) => {
		configs.value = response.data as App.Http.Resources.Collections.ConfigCollectionResource;
		const confs: App.Http.Resources.Models.ConfigResource[] = Array.prototype.flat.call(
			configs.value.configs,
		) as App.Http.Resources.Models.ConfigResource[];

		loadedSettings.value = {
			dropbox_key: confs.find((config) => config.key === "dropbox_key")?.value,
			sorting_photo: {
				col: confs.find((config) => config.key === "sorting_photos_col")?.value as App.Enum.ColumnSortingPhotoType,
				order: confs.find((config) => config.key === "sorting_photos_order")?.value as App.Enum.OrderSortingType,
			},
			sorting_album: {
				col: confs.find((config) => config.key === "sorting_albums_col")?.value as App.Enum.ColumnSortingAlbumType,
				order: confs.find((config) => config.key === "sorting_albums_order")?.value as App.Enum.OrderSortingType,
			},
			default_license: confs.find((config) => config.key === "default_license")?.value as App.Enum.LicenseType,
			default_album_thumb_aspect_ratio: confs.find((config) => config.key === "default_album_thumb_aspect_ratio")
				?.value as App.Enum.AspectRatioType,
			lang: confs.find((config) => config.key === "lang")?.value ?? "en",
			layout: confs.find((config) => config.key === "layout")?.value as App.Enum.PhotoLayoutType,
			image_overlay_type: confs.find((config) => config.key === "image_overlay_type")?.value as App.Enum.ImageOverlayType,
			search_public: confs.find((config) => config.key === "search_public")?.value === "1",
			map_display: confs.find((config) => config.key === "map_display")?.value === "1",
			location_show: confs.find((config) => config.key === "location_show")?.value === "1",
			location_show_public: confs.find((config) => config.key === "location_show_public")?.value === "1",
			nsfw_visible: confs.find((config) => config.key === "nsfw_visible")?.value === "1",
		};

		loadingOptions();
		// apiDescription.value = trans('lychee.DROPBOX_TEXT');
	});
}

function loadingOptions() {
	if (loadedSettings.value === undefined) {
		return true;
	}

	photoSortingColumn.value = SelectBuilders.buildPhotoSorting(loadedSettings.value.sorting_photo.col);
	photoSortingOrder.value = SelectBuilders.buildSortingOrder(loadedSettings.value.sorting_photo.order);
	albumSortingColumn.value = SelectBuilders.buildAlbumSorting(loadedSettings.value.sorting_album.col);
	albumSortingOrder.value = SelectBuilders.buildSortingOrder(loadedSettings.value.sorting_album.order);
	license.value = SelectBuilders.buildLicense(loadedSettings.value.default_license);
	aspectRatio.value = SelectBuilders.buildAspectRatio(loadedSettings.value.default_album_thumb_aspect_ratio);
	layout.value = SelectBuilders.buildPhotoLayout(loadedSettings.value.layout);
	nsfwVisible.value = loadedSettings.value.nsfw_visible;
}

// default_album_thumb_aspect_ratio

load();
</script>

<style lang="css" scoped>
kbd {
	color: red;
}
</style>
