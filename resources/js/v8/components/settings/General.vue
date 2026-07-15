<template>
	<Fieldset :legend="$t('settings.system.header')">
		<div class="flex flex-col gap-4 mb-8">
			<BoolField
				v-if="dark_mode_enabled !== undefined"
				:label="$t('settings.system.use_dark_mode')"
				:config="dark_mode_enabled"
				@filled="saveDarkMode"
			/>
			<SelectLang v-if="lang !== undefined" :label="$t('settings.system.language')" :config="lang" @filled="saveLang" />
			<div class="flex flex-wrap justify-between">
				<label for="pp_dialog_nsfw_visible" class="text-highlighted">{{ $t("settings.system.nsfw_album_visibility") }}</label>
				<USwitch id="pp_dialog_nsfw_visible" v-model="nsfwVisible" class="text-sm" @update:model-value="updateNSFW" />
				<p class="my-1.5 text-muted w-full" v-html="$t('settings.system.nsfw_album_explanation')"></p>
			</div>
			<BoolField
				v-if="cache_enabled !== undefined"
				:label="$t('settings.system.cache_enabled')"
				:config="cache_enabled"
				:details="$t('settings.system.cache_enabled_details')"
				@filled="save"
			/>
		</div>
	</Fieldset>
	<Fieldset legend="Lychee SE" v-if="!is_se_enabled && (!is_se_info_hidden || is_se_expired)">
		<template #legend>
			<div class="font-bold" v-html="$t('settings.lychee_se.header')" />
		</template>
		<p v-if="is_se_expired" class="inline-block mb-8 text-error" v-html="$t('dialogs.register.expired_license')" />
		<p class="mb-2" v-html="$t('settings.lychee_se.call4action')" />
		<div v-if="!is_se_enabled" class="mb-8 flex items-start gap-4">
			<UFormField class="w-3/4" :label="$t('dialogs.register.license_key')">
				<UInput id="licenseKey" v-model="licenseKey" class="w-full" @update:model-value="licenseKeyIsInvValid = false" />
				<template #hint>
					<span v-if="licenseKey && licenseKeyIsInvValid" class="inline-block mt-4 font-bold text-error">{{
						$t("dialogs.register.invalid_license")
					}}</span>
				</template>
			</UFormField>
			<UButton
				v-if="!is_se_enabled"
				class="w-1/4 justify-center font-bold bg-primary-500/20 hover:bg-primary-500"
				color="neutral"
				:disabled="!isValidRegistrationForm"
				@click="register"
				>{{ $t("dialogs.register.register") }}
			</UButton>
		</div>
		<p class="flex flex-wrap justify-between my-6">
			<label for="enable_se_preview">{{ $t("settings.lychee_se.preview") }}</label>
			<USwitch id="enable_se_preview" v-model="enable_se_preview" class="text-sm" @update:model-value="savePreview" />
		</p>
		<p class="flex flex-wrap justify-between">
			<label for="disable_se_call_for_actions">{{ $t("settings.lychee_se.hide_call4action") }}</label>
			<USwitch id="disable_se_call_for_actions" v-model="disable_se_call_for_actions" class="text-sm" @update:model-value="saveHideC4A" />
			<span class="mt-1 w-full text-muted flex items-center gap-2"
				><UIcon name="lucide:triangle-alert" class="text-orange-500" />{{ $t("settings.lychee_se.hide_warning") }}</span
			>
		</p>
	</Fieldset>
	<Fieldset
		:legend="$t('settings.dropbox.header')"
		class="border-b-0 ltr:border-r-0 ltr:rounded-r-none rtl:border-l-0 rtl:rounded-l-none rounded-b-none"
		:toggleable="true"
		:collapsed="true"
	>
		<p class="mb-4 text-muted">
			{{ $t("settings.dropbox.instruction") }}
			<a href="https://www.dropbox.com/developers/saver" class="pl-2 border-b border-dashed border-b-primary-500 text-primary-500">
				<UIcon name="lucide:link" />
			</a>
		</p>
		<div class="flex gap-4">
			<UFormField class="w-full grow" :label="$t('settings.dropbox.api_key')">
				<InputPassword id="api_key" v-model="dropbox_key" />
			</UFormField>
			<UButton color="neutral" class="w-full justify-center bg-primary-500/20 hover:bg-primary-500" @click="saveDropboxKey">{{
				$t("settings.dropbox.set_key")
			}}</UButton>
		</div>
	</Fieldset>
	<Fieldset :legend="$t('settings.gallery.header')">
		<div class="flex flex-col mb-6">
			<!-- ALBUM ORDER -->
			<SelectOptionsField
				v-if="photoSortingColumn !== undefined"
				:label="$t('settings.gallery.photo_order_column')"
				:config="photoSortingColumn"
				:options="photoSortingColumnsOptions"
				:mapper="SelectBuilders.buildPhotoSorting"
				@filled="save"
			/>
			<SelectOptionsField
				v-if="photoSortingOrder !== undefined"
				:label="$t('settings.gallery.photo_order_direction')"
				:config="photoSortingOrder"
				:options="sortingOrdersOptions"
				:mapper="SelectBuilders.buildSortingOrder"
				@filled="save"
			/>
			<SelectOptionsField
				v-if="albumSortingColumn !== undefined"
				:label="$t('settings.gallery.album_order_column')"
				:config="albumSortingColumn"
				:options="albumSortingColumnsOptions"
				:mapper="SelectBuilders.buildAlbumSorting"
				@filled="save"
			/>
			<div class="mb-4" />
			<SelectOptionsField
				v-if="albumSortingOrder !== undefined"
				:label="$t('settings.gallery.album_order_direction')"
				:config="albumSortingOrder"
				:options="sortingOrdersOptions"
				:mapper="SelectBuilders.buildSortingOrder"
				@filled="save"
			/>
			<SelectOptionsField
				v-if="aspectRatio !== undefined"
				:label="$t('settings.gallery.aspect_ratio')"
				:config="aspectRatio"
				:options="aspectRatioOptions"
				:mapper="SelectBuilders.buildAspectRatio"
				@filled="save"
			/>
			<SelectOptionsField
				v-if="layout !== undefined"
				:label="$t('settings.gallery.photo_layout')"
				:config="layout"
				:options="photoLayoutOptions"
				:mapper="SelectBuilders.buildPhotoLayout"
				@filled="save"
			/>
			<div class="mb-4" />
			<SelectField
				v-if="album_decoration !== undefined"
				:label="$t('settings.gallery.album_decoration')"
				:config="album_decoration"
				@filled="save"
			/>
			<SelectField
				v-if="album_decoration_orientation !== undefined"
				:label="$t('settings.gallery.album_decoration_direction')"
				:config="album_decoration_orientation"
				@filled="save"
			/>
			<SelectOptionsField
				v-if="image_overlay_type !== undefined"
				:label="$t('settings.gallery.photo_overlay')"
				:config="image_overlay_type"
				:options="overlayOptions"
				:mapper="SelectBuilders.buildOverlay"
				@filled="save"
			/>
			<BoolField
				v-if="rounded_corners_enabled !== undefined"
				:label="$t('settings.gallery.rounded_corners_enabled')"
				:config="rounded_corners_enabled"
				@filled="save"
			/>
			<BoolField
				v-if="album_border_enabled !== undefined"
				:label="$t('settings.gallery.album_border_enabled')"
				:config="album_border_enabled"
				@filled="save"
			/>
		</div>
		<!-- LICENSE -->
		<div class="flex flex-col">
			<SelectOptionsField
				v-if="default_license !== undefined"
				:label="$t('settings.gallery.license_default')"
				:config="default_license"
				:options="licenseOptions"
				:mapper="SelectBuilders.buildLicense"
				@filled="save"
			/>
			<div class="mb-4 text-muted">
				<p>
					{{ $t("settings.gallery.license_help") }}
					<a href="https://creativecommons.org/choose/" target="_blank" class="ml-2 text-primary-500">
						<UIcon name="lucide:link" class="inline" />
					</a>
				</p>
			</div>
		</div>
	</Fieldset>
	<Fieldset :legend="$t('settings.geolocation.header')">
		<div class="flex flex-col gap-4">
			<BoolField v-if="map_display !== undefined" :label="$t('settings.geolocation.map_display')" :config="map_display" @filled="save" />
			<BoolField
				v-if="map_display_public !== undefined"
				:label="$t('settings.geolocation.map_display_public')"
				:config="map_display_public"
				@filled="save"
			/>
			<SelectOptionsField
				v-if="map_provider !== undefined"
				:label="$t('settings.geolocation.map_provider')"
				:config="map_provider"
				:options="mapProvidersOptions"
				:mapper="SelectBuilders.buildMapProvider"
				@filled="save"
			/>
			<BoolField
				v-if="map_include_subalbums !== undefined"
				:label="$t('settings.geolocation.map_include_subalbums')"
				:config="map_include_subalbums"
				@filled="save"
			/>
			<BoolField
				v-if="location_decoding !== undefined"
				:label="$t('settings.geolocation.location_decoding')"
				:config="location_decoding"
				@filled="save"
			/>
			<BoolField v-if="location_show !== undefined" :label="$t('settings.geolocation.location_show')" :config="location_show" @filled="save" />
			<BoolField
				v-if="location_show_public !== undefined"
				:label="$t('settings.geolocation.location_show_public')"
				:config="location_show_public"
				@filled="save"
			/>
			<BoolField
				v-if="gps_coordinate_display !== undefined"
				:label="$t('settings.geolocation.gps_coordinate_display')"
				:config="gps_coordinate_display"
				@filled="save"
			/>
			<BoolField
				v-if="gps_coordinate_display_public !== undefined"
				:label="$t('settings.geolocation.gps_coordinate_display_public')"
				:config="gps_coordinate_display_public"
				@filled="save"
			/>
		</div>
	</Fieldset>
</template>
<script setup lang="ts">
import { computed, ref } from "vue";
import InputPassword from "@/v8/components/forms/basic/InputPassword.vue";
import SettingsService from "@/services/settings-service";
import {
	photoSortingColumnsOptions,
	albumSortingColumnsOptions,
	sortingOrdersOptions,
	licenseOptions,
	aspectRatioOptions,
	SelectBuilders,
	photoLayoutOptions,
	overlayOptions,
	mapProvidersOptions,
} from "@/config/constants";
import { trans } from "laravel-vue-i18n";
import SelectLang from "@/v8/components/forms/settings/SelectLang.vue";
import SelectOptionsField from "@/v8/components/forms/settings/SelectOptionsField.vue";
import SelectField from "@/v8/components/forms/settings/SelectField.vue";
import BoolField from "@/v8/components/forms/settings/BoolField.vue";
import { useAppToast } from "@/v8/composables/useAppToast";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import MaintenanceService from "@/services/maintenance-service";
import { onMounted, watch } from "vue";
import Fieldset from "@/v8/components/forms/basic/Fieldset.vue";
import { loadLanguageAsync } from "laravel-vue-i18n";

const toast = useAppToast();

const props = defineProps<{
	hash: string;
	configs: App.Http.Resources.Models.ConfigCategoryResource[];
}>();
const emits = defineEmits<{ refresh: [] }>();

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
const rounded_corners_enabled = ref<App.Http.Resources.Models.ConfigResource | undefined>(undefined);
const album_border_enabled = ref<App.Http.Resources.Models.ConfigResource | undefined>(undefined);
const nsfwVisible = ref<boolean | undefined>(undefined);

const dark_mode_enabled = ref<App.Http.Resources.Models.ConfigResource | undefined>(undefined);
const cache_enabled = ref<App.Http.Resources.Models.ConfigResource | undefined>(undefined);

const map_display = ref<App.Http.Resources.Models.ConfigResource | undefined>(undefined);
const map_display_public = ref<App.Http.Resources.Models.ConfigResource | undefined>(undefined);
const map_provider = ref<App.Http.Resources.Models.ConfigResource | undefined>(undefined);
const map_include_subalbums = ref<App.Http.Resources.Models.ConfigResource | undefined>(undefined);
const location_decoding = ref<App.Http.Resources.Models.ConfigResource | undefined>(undefined);
const location_show = ref<App.Http.Resources.Models.ConfigResource | undefined>(undefined);
const location_show_public = ref<App.Http.Resources.Models.ConfigResource | undefined>(undefined);
const gps_coordinate_display = ref<App.Http.Resources.Models.ConfigResource | undefined>(undefined);
const gps_coordinate_display_public = ref<App.Http.Resources.Models.ConfigResource | undefined>(undefined);

const disable_se_call_for_actions = ref<boolean | undefined>(undefined);
const enable_se_preview = ref<boolean | undefined>(undefined);
const licenseKey = ref<string | undefined>(undefined);
const licenseKeyIsInvValid = ref(false);
const isValidRegistrationForm = computed(() => {
	return licenseKey.value !== undefined && licenseKey.value !== "";
});

// Map stuff
const lycheeStore = useLycheeStateStore();
const { is_se_preview_enabled, is_se_enabled, is_se_info_hidden, is_se_expired } = storeToRefs(lycheeStore);

function save(configKey: string, value: string) {
	SettingsService.setConfigs({
		configs: [
			{
				key: configKey,
				value: value,
			},
		],
	}).then(() => {
		toast.add({ severity: "success", summary: trans("settings.toasts.change_saved"), detail: trans("settings.toasts.details"), life: 3000 });
		emits("refresh");
	});
}

function saveLang(configKey: string, value: string) {
	save(configKey, value);

	loadLanguageAsync(value)
		.then(() => {
			// Keep SPA + RTL state in sync
			document.documentElement.lang = value;
			document.documentElement.dir = ["ar", "fa"].includes(value) ? "rtl" : "ltr";
		})
		.catch((err) => {
			console.log(`Could not load language file for ${value}`, err);
		});
}

function load(configs: App.Http.Resources.Models.ConfigCategoryResource[]) {
	const configurations = [] as App.Http.Resources.Models.ConfigResource[];
	configs.forEach((config) => (config.configs as App.Http.Resources.Models.ConfigResource[]).forEach((value) => configurations.push(value)));

	lang.value = configurations.find((config) => config.key === "lang");
	dark_mode_enabled.value = configurations.find((config) => config.key === "dark_mode_enabled");
	nsfwVisible.value = configurations.find((config) => config.key === "nsfw_visible")?.value === "1";
	dropbox_key.value = configurations.find((config) => config.key === "dropbox_key")?.value ?? "";
	cache_enabled.value = configurations.find((config) => config.key === "cache_enabled");

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
	rounded_corners_enabled.value = configurations.find((config) => config.key === "rounded_corners_enabled");
	album_border_enabled.value = configurations.find((config) => config.key === "album_border_enabled");

	map_display.value = configurations.find((config) => config.key === "map_display");
	map_display_public.value = configurations.find((config) => config.key === "map_display_public");
	map_provider.value = configurations.find((config) => config.key === "map_provider");
	map_include_subalbums.value = configurations.find((config) => config.key === "map_include_subalbums");
	location_decoding.value = configurations.find((config) => config.key === "location_decoding");
	location_show.value = configurations.find((config) => config.key === "location_show");
	location_show_public.value = configurations.find((config) => config.key === "location_show_public");
	gps_coordinate_display.value = configurations.find((config) => config.key === "gps_coordinate_display");
	gps_coordinate_display_public.value = configurations.find((config) => config.key === "gps_coordinate_display_public");

	disable_se_call_for_actions.value = configurations.find((config) => config.key === "disable_se_call_for_actions")?.value === "1";
	enable_se_preview.value = configurations.find((config) => config.key === "enable_se_preview")?.value === "1";
	licenseKey.value = configurations.find((config) => config.key === "license_key")?.value ?? "";
}

function updateNSFW() {
	save("nsfw_visible", nsfwVisible.value ? "1" : "0");
}

function saveDarkMode(_configKey: string, value: string) {
	if (value === "1") {
		document.body.classList.add("dark");
		save("dark_mode_enabled", "1");
	} else {
		document.body.classList.remove("dark");
		save("dark_mode_enabled", "0");
	}
}

function savePreview() {
	save("enable_se_preview", enable_se_preview.value ? "1" : "0");
}

function saveHideC4A() {
	save("disable_se_call_for_actions", disable_se_call_for_actions.value ? "1" : "0");
}

function register() {
	if (licenseKey.value === undefined || licenseKey.value === "") {
		return;
	}

	MaintenanceService.register(licenseKey.value)
		.then((response) => {
			if (response.data.success) {
				is_se_enabled.value = true;
				is_se_expired.value = false;
				is_se_preview_enabled.value = false;
				is_se_info_hidden.value = false;
				toast.add({
					severity: "success",
					summary: trans("settings.toasts.thank_you"),
					detail: trans("settings.toasts.reload"),
					life: 5000,
				});
			} else {
				licenseKeyIsInvValid.value = true;
			}
		})
		.catch(() => {
			licenseKeyIsInvValid.value = true;
		});
}

function saveDropboxKey() {
	save("dropbox_key", dropbox_key.value ?? "");
}

onMounted(() => {
	load(props.configs);
});

watch(
	() => props.hash,
	() => {
		load(props.configs);
	},
);
</script>
