<template>
	<div v-if="configs" class="max-w-2xl mx-auto">
		<Fieldset legend="System" class="border-b-0 border-r-0 rounded-r-none rounded-b-none">
			<div class="flex flex-col gap-4 mb-8">
				<BoolField v-if="dark_mode_enabled !== undefined" :config="dark_mode_enabled" @filled="save" />
				<SelectLang v-if="lang !== undefined" :config="lang" />
				<div class="flex flex-wrap justify-between">
					<label for="pp_dialog_nsfw_visible">{{ $t("lychee.NSFW_VISIBLE_TEXT_1") }}</label>
					<ToggleSwitch id="pp_dialog_nsfw_visible" v-model="nsfwVisible" class="text-sm" @update:model-value="updateNSFW" />
					<p class="my-1.5 text-muted-color w-full" v-html="nsfwText2"></p>
				</div>
			</div>
			<p class="mb-4 text-muted-color">
				In order to import photos from your Dropbox, you need a valid drop-ins app key from their website.
				<a href="https://www.dropbox.com/developers/saver" class="pl-2 border-b border-dashed border-b-primary-500 text-primary-500">
					<i class="pi pi-link"></i>
				</a>
			</p>
			<div class="flex gap-4">
				<FloatLabel class="w-full flex-grow">
					<InputPassword id="api_key" type="text" v-model="dropbox_key" />
					<label for="api_key" class="text-muted-color">{{ $t("lychee.SETTINGS_DROPBOX_KEY") }}</label>
				</FloatLabel>
				<Button severity="primary" class="w-full border-none" @click="saveDropboxKey">{{ $t("lychee.DROPBOX_TITLE") }}</Button>
			</div>
		</Fieldset>
		<Fieldset legend="Gallery" class="border-b-0 border-r-0 rounded-r-none rounded-b-none">
			<div class="flex flex-col mb-6">
				<!-- ALBUM ORDER -->
				<SelectOptionsField
					v-if="photoSortingColumn !== undefined"
					:config="photoSortingColumn"
					:options="photoSortingColumnsOptions"
					:mapper="SelectBuilders.buildPhotoSorting"
					@filled="save"
				/>
				<SelectOptionsField
					v-if="photoSortingOrder !== undefined"
					:config="photoSortingOrder"
					:options="sortingOrdersOptions"
					:mapper="SelectBuilders.buildSortingOrder"
					@filled="save"
				/>
				<SelectOptionsField
					v-if="albumSortingColumn !== undefined"
					:config="albumSortingColumn"
					:options="albumSortingColumnsOptions"
					:mapper="SelectBuilders.buildAlbumSorting"
					@filled="save"
				/>
				<SelectOptionsField
					class="mb-6"
					v-if="albumSortingOrder !== undefined"
					:config="albumSortingOrder"
					:options="sortingOrdersOptions"
					:mapper="SelectBuilders.buildSortingOrder"
					@filled="save"
				/>
				<SelectOptionsField
					v-if="aspectRatio !== undefined"
					:config="aspectRatio"
					:options="aspectRationOptions"
					:mapper="SelectBuilders.buildAspectRatio"
					@filled="save"
				/>
				<SelectOptionsField
					class="mb-6"
					v-if="layout !== undefined"
					:config="layout"
					:options="photoLayoutOptions"
					:mapper="SelectBuilders.buildPhotoLayout"
					@filled="save"
				/>
				<SelectField v-if="album_decoration !== undefined" :config="album_decoration" @filled="save" />
				<SelectField v-if="album_decoration_orientation !== undefined" :config="album_decoration_orientation" @filled="save" />
				<SelectOptionsField
					v-if="image_overlay_type !== undefined"
					:config="image_overlay_type"
					:options="overlayOptions"
					:mapper="SelectBuilders.buildOverlay"
					@filled="save"
				/>
			</div>
			<!-- LICENSE -->
			<div class="flex flex-col">
				<SelectOptionsField
					v-if="default_license !== undefined"
					:config="default_license"
					:options="licenseOptions"
					:mapper="SelectBuilders.buildLicense"
					@filled="save"
				/>
				<div class="mb-4 text-muted-color">
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
		</Fieldset>
		<Fieldset legend="Geo-location" class="border-b-0 border-r-0 rounded-r-none rounded-b-none">
			<div class="flex flex-col gap-4">
				<BoolField v-if="map_display !== undefined" :config="map_display" @filled="save" />
				<BoolField v-if="map_display_public !== undefined" :config="map_display_public" @filled="save" />
				<SelectOptionsField
					v-if="map_provider !== undefined"
					:config="map_provider"
					:options="mapProvidersOptions"
					:mapper="SelectBuilders.buildMapProvider"
					@filled="save"
				/>
				<BoolField v-if="map_include_subalbums !== undefined" :config="map_include_subalbums" @filled="save" />
				<BoolField v-if="location_decoding !== undefined" :config="location_decoding" @filled="save" />
				<BoolField v-if="location_show !== undefined" :config="location_show" @filled="save" />
				<BoolField v-if="location_show_public !== undefined" :config="location_show_public" @filled="save" />
			</div>
		</Fieldset>
		<Fieldset legend="Advanced Customization" class="border-b-0 border-r-0 rounded-r-none rounded-b-none" :toggleable="true" :collapsed="true">
			<div class="flex flex-col gap-4">
				<div>
					<Textarea v-model="css" class="w-full h-48" rows="10" cols="30" />
					<Button severity="primary" class="w-full border-none font-bold" @click="saveCss">{{ $t("lychee.CSS_TITLE") }}</Button>
				</div>
				<div>
					<Textarea v-model="js" class="w-full h-48" rows="10" cols="30" />
					<Button severity="primary" class="w-full border-none font-bold" @click="saveJs">{{ $t("lychee.JS_TITLE") }}</Button>
				</div>
			</div>
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
import { computed, ref, toRaw } from "vue";
import Fieldset from "primevue/fieldset";
import FloatLabel from "primevue/floatlabel";
import Button from "primevue/button";
import ToggleSwitch from "primevue/toggleswitch";
import InputPassword from "@/components/forms/basic/InputPassword.vue";
import SettingsService from "@/services/settings-service";
import {
	photoSortingColumnsOptions,
	albumSortingColumnsOptions,
	sortingOrdersOptions,
	licenseOptions,
	aspectRationOptions,
	SelectBuilders,
	photoLayoutOptions,
	overlayOptions,
	mapProvidersOptions,
} from "@/config/constants";
import { trans } from "laravel-vue-i18n";
import SelectLang from "../forms/settings/SelectLang.vue";
import SelectOptionsField from "../forms/settings/SelectOptionsField.vue";
import SelectField from "../forms/settings/SelectField.vue";
import BoolField from "../forms/settings/BoolField.vue";
import { useToast } from "primevue/usetoast";
import Textarea from "../forms/basic/Textarea.vue";

const toast = useToast();

const configs = ref(undefined as undefined | App.Http.Resources.Collections.ConfigCollectionResource);
const dropbox_key = ref<string | undefined>(undefined);
const photoSortingColumn = ref<App.Http.Resources.Models.ConfigResource | undefined>(undefined);
const photoSortingOrder = ref<App.Http.Resources.Models.ConfigResource | undefined>(undefined);
const albumSortingColumn = ref<App.Http.Resources.Models.ConfigResource | undefined>(undefined);
const albumSortingOrder = ref<App.Http.Resources.Models.ConfigResource | undefined>(undefined);
const album_decoration = ref<App.Http.Resources.Models.ConfigResource | undefined>(undefined);
const album_decoration_orientation = ref<App.Http.Resources.Models.ConfigResource | undefined>(undefined);
const image_overlay_type = ref<App.Http.Resources.Models.ConfigResource | undefined>(undefined);
const default_license = ref<App.Http.Resources.Models.ConfigResource | undefined>(undefined);
const aspectRatio = ref<App.Http.Resources.Models.ConfigResource | undefined>(undefined);
const lang = ref<App.Http.Resources.Models.ConfigResource | undefined>(undefined);
const layout = ref<App.Http.Resources.Models.ConfigResource | undefined>(undefined);
const nsfwVisible = ref<boolean | undefined>(undefined);

const dark_mode_enabled = ref<App.Http.Resources.Models.ConfigResource | undefined>(undefined);

const map_display = ref<App.Http.Resources.Models.ConfigResource | undefined>(undefined);
const map_display_public = ref<App.Http.Resources.Models.ConfigResource | undefined>(undefined);
const map_provider = ref<App.Http.Resources.Models.ConfigResource | undefined>(undefined);
const map_include_subalbums = ref<App.Http.Resources.Models.ConfigResource | undefined>(undefined);
const location_decoding = ref<App.Http.Resources.Models.ConfigResource | undefined>(undefined);
const location_show = ref<App.Http.Resources.Models.ConfigResource | undefined>(undefined);
const location_show_public = ref<App.Http.Resources.Models.ConfigResource | undefined>(undefined);

const css = ref<string | undefined>(undefined);
const js = ref<string | undefined>(undefined);
// Map stuff

function save(configKey: string, value: string) {
	SettingsService.setConfigs({
		configs: [
			{
				key: configKey,
				value: value,
			},
		],
	}).then(() => {
		toast.add({ severity: "success", summary: "Change saved!", detail: "Settings have been modified as per request", life: 3000 });
		load();
	});
}

const nsfwText2 = computed(() => {
	return trans("lychee.NSFW_VISIBLE_TEXT_2");
});

function load() {
	SettingsService.getAll().then((response) => {
		configs.value = response.data;
		const decapsulated: App.Http.Resources.Collections.ConfigCollectionResource = toRaw(configs.value);
		const configurations = [] as App.Http.Resources.Models.ConfigResource[];
		Object.values(decapsulated.configs).forEach((value) => Object.values(value).forEach((value) => configurations.push(value)));

		// console.log(configurations);
		lang.value = configurations.find((config) => config.key === "lang");

		dark_mode_enabled.value = configurations.find((config) => config.key === "dark_mode_enabled");
		nsfwVisible.value = configurations.find((config) => config.key === "nsfw_visible")?.value === "1";
		dropbox_key.value = configurations.find((config) => config.key === "dropbox_key")?.value ?? "";

		photoSortingColumn.value = configurations.find((config) => config.key === "sorting_photos_col");
		photoSortingOrder.value = configurations.find((config) => config.key === "sorting_photos_order");
		albumSortingColumn.value = configurations.find((config) => config.key === "sorting_albums_col");
		albumSortingOrder.value = configurations.find((config) => config.key === "sorting_albums_order");
		default_license.value = configurations.find((config) => config.key === "default_license");
		aspectRatio.value = configurations.find((config) => config.key === "default_album_thumb_aspect_ratio");
		layout.value = configurations.find((config) => config.key === "layout");
		album_decoration.value = configurations.find((config) => config.key === "album_decoration");
		album_decoration_orientation.value = configurations.find((config) => config.key === "album_decoration_orientation");
		image_overlay_type.value = configurations.find((config) => config.key === "image_overlay_type");

		map_display.value = configurations.find((config) => config.key === "map_display");
		map_display_public.value = configurations.find((config) => config.key === "map_display_public");
		map_provider.value = configurations.find((config) => config.key === "map_provider");
		map_include_subalbums.value = configurations.find((config) => config.key === "map_include_subalbums");
		location_decoding.value = configurations.find((config) => config.key === "location_decoding");
		location_show.value = configurations.find((config) => config.key === "location_show");
		location_show_public.value = configurations.find((config) => config.key === "location_show_public");
	});

	SettingsService.getCss()
		.then((response) => {
			css.value = response.data;
		})
		.catch(() => {
			toast.add({ severity: "error", summary: "Error!", detail: "Could not load dist/user.css", life: 3000 });
		});

	SettingsService.getJs()
		.then((response) => {
			js.value = response.data;
		})
		.catch(() => {
			toast.add({ severity: "error", summary: "Error!", detail: "Could not load dist/custom.js", life: 3000 });
		});
}

function updateNSFW() {
	save("nsfw_visible", nsfwVisible.value ? "1" : "0");
}

function saveCss() {
	SettingsService.setCss(css.value ?? "")
		.then(() => {
			toast.add({ severity: "success", summary: "Change saved!", life: 3000 });
		})
		.catch(() => {
			toast.add({ severity: "error", summary: "Error!", detail: "Could not save CSS", life: 3000 });
		});
}

function saveJs() {
	SettingsService.setJs(js.value ?? "")
		.then(() => {
			toast.add({ severity: "success", summary: "Change saved!", life: 3000 });
		})
		.catch(() => {
			toast.add({ severity: "error", summary: "Error!", detail: "Could not save JS", life: 3000 });
		});
}

function saveDropboxKey() {
	save("dropbox_key", dropbox_key.value ?? "");
}

load();
</script>
