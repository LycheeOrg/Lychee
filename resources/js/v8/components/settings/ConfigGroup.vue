<template>
	<div class="flex flex-col gap-4 w-full">
		<template v-for="config in props.configs" :key="`config-${config.key}`">
			<div v-if="show(config)" class="relative flex gap-2" @mouseenter="onRowEnter(config)" @mouseleave="onRowLeave">
				<div v-if="isHighlighted(config)" class="absolute inset-0 rounded-md bg-primary/20 animate-pulse pointer-events-none" />
				<div class="flex gap-2 w-full" :class="{ 'opacity-50 pointer-events-none': !isConfigActive(config) }">
					<div class="shrink h-8 w-4 flex items-center">
						<UIcon v-if="config.is_expert" name="lucide:graduation-cap" class="text-primary-500" />
					</div>
					<template v-if="is_old_style">
						<OldField :config="config" @filled="filled" @reset="reset" />
					</template>
					<div v-else class="w-full">
						<!-- Special keys -->
						<ZipSliderField v-if="config.key === 'zip_deflate_level'" :config="config" @filled="filled" @reset="reset" />
						<SelectOptionsField
							v-else-if="config.key === 'default_license'"
							:config="config"
							:options="licenseOptions"
							:mapper="SelectBuilders.buildLicense"
							@filled="filled"
							@reset="reset"
						/>
						<SelectOptionsField
							v-else-if="config.key === 'sorting_photos_col'"
							:config="config"
							:options="photoSortingColumnsOptions"
							:mapper="SelectBuilders.buildPhotoSorting"
							@filled="filled"
							@reset="reset"
						/>
						<SelectOptionsField
							v-else-if="config.key === 'sorting_photos_order'"
							:config="config"
							:options="sortingOrdersOptions"
							:mapper="SelectBuilders.buildSortingOrder"
							@filled="filled"
							@reset="reset"
						/>
						<SelectOptionsField
							v-else-if="config.key === 'sorting_albums_col'"
							:config="config"
							:options="albumSortingColumnsOptions"
							:mapper="SelectBuilders.buildAlbumSorting"
							@filled="filled"
							@reset="reset"
						/>
						<SelectOptionsField
							v-else-if="config.key === 'sorting_albums_order'"
							:config="config"
							:options="sortingOrdersOptions"
							:mapper="SelectBuilders.buildSortingOrder"
							@filled="filled"
							@reset="reset"
						/>
						<SelectOptionsField
							v-else-if="config.key === 'sorting_pinned_albums_col'"
							:config="config"
							:options="albumSortingColumnsOptions"
							:mapper="SelectBuilders.buildAlbumSorting"
							@filled="filled"
							@reset="reset"
						/>
						<SelectOptionsField
							v-else-if="config.key === 'sorting_pinned_albums_order'"
							:config="config"
							:options="sortingOrdersOptions"
							:mapper="SelectBuilders.buildSortingOrder"
							@filled="filled"
							@reset="reset"
						/>
						<SelectOptionsField
							v-else-if="config.key === 'default_album_thumb_aspect_ratio'"
							:config="config"
							:options="aspectRatioOptions"
							:mapper="SelectBuilders.buildAspectRatio"
							@filled="filled"
							@reset="reset"
						/>
						<SelectOptionsField
							v-else-if="config.type === 'square|justified|masonry|grid'"
							:config="config"
							:options="photoLayoutOptions"
							:mapper="SelectBuilders.buildPhotoLayout"
							@filled="filled"
							@reset="reset"
						/>
						<SelectOptionsField
							v-else-if="config.key === 'default_album_protection'"
							:config="config"
							:options="defaultAlbumProtectionOptions"
							:mapper="SelectBuilders.buildDefaultAlbumProtection"
							@filled="filled"
							@reset="reset"
						/>
						<SelectOptionsField
							v-else-if="config.key === 'image_overlay_type'"
							:config="config"
							:options="overlayOptions"
							:mapper="SelectBuilders.buildOverlay"
							@filled="filled"
							@reset="reset"
						/>
						<SelectOptionsField
							v-else-if="config.key === 'map_provider'"
							:config="config"
							:options="mapProvidersOptions"
							:mapper="SelectBuilders.buildMapProvider"
							@filled="filled"
							@reset="reset"
						/>
						<SelectOptionsField
							v-else-if="config.key === 'has_exiftool'"
							:config="config"
							:options="toolsOptions"
							:mapper="SelectBuilders.buildToolSelection"
							@filled="filled"
							@reset="reset"
						/>
						<SelectOptionsField
							v-else-if="config.key === 'has_ffmpeg'"
							:config="config"
							:options="toolsOptions"
							:mapper="SelectBuilders.buildToolSelection"
							@filled="filled"
							@reset="reset"
						/>
						<SelectOptionsField
							v-else-if="config.key === 'photos_pagination_ui_mode'"
							:config="config"
							:options="paginationUiModeOptions"
							:mapper="SelectBuilders.buildPaginationUiMode"
							@filled="filled"
							@reset="reset"
						/>
						<SelectOptionsField
							v-else-if="config.key === 'albums_pagination_ui_mode'"
							:config="config"
							:options="paginationUiModeOptions"
							:mapper="SelectBuilders.buildPaginationUiMode"
							@filled="filled"
							@reset="reset"
						/>
						<SelectOptionsField
							v-else-if="config.type === 'currency'"
							:config="config"
							:options="currencyOptions"
							:mapper="SelectBuilders.buildCurrencySelection"
							@filled="filled"
							@reset="reset"
						/>
						<ColorField v-else-if="config.type === 'color'" :config="config" @filled="filled" @reset="reset" />
						<SelectLang v-else-if="config.key === 'lang'" :config="config" @filled="filled" @reset="reset" />
						<SelectField v-else-if="config.key === 'album_decoration'" :config="config" @filled="filled" @reset="reset" />
						<SelectField v-else-if="config.key === 'album_decoration_orientation'" :config="config" @filled="filled" @reset="reset" />
						<SelectField v-else-if="config.key === 'album_subtitle_type'" :config="config" @filled="filled" @reset="reset" />
						<StringField v-else-if="config.key === 'raw_formats'" :config="config" @filled="filled" @reset="reset" />
						<StringField v-else-if="config.key === 'owner_id'" :config="config" @filled="filled" @reset="reset" />
						<StringField v-else-if="config.key === 'local_takestamp_video_formats'" :config="config" @filled="filled" @reset="reset" />
						<SelectField v-else-if="config.key === 'watermark_position'" :config="config" @filled="filled" @reset="reset" />
						<!-- Generic -->
						<StringField v-else-if="config.type.startsWith('string')" :config="config" @filled="filled" @reset="reset" />
						<BoolField v-else-if="config.type === '0|1'" :config="config" @filled="filled" @reset="reset" />
						<NumberField v-else-if="config.type === 'int'" :config="config" :min="0" @filled="filled" @reset="reset" />
						<NumberField v-else-if="config.type === 'positive'" :config="config" :min="1" @filled="filled" @reset="reset" />
						<NumberField
							v-else-if="config.type.startsWith('int:')"
							:config="config"
							:min="intRangeMin(config.type)"
							:max="intRangeMax(config.type)"
							@filled="filled"
							@reset="reset"
						/>
						<SliderField v-else-if="config.type.includes('|')" :config="config" @filled="filled" @reset="reset" />
						<p v-else-if="is_debug_enabled" class="bg-red-500">
							{{ config.key }} -- {{ config.value }} -- {{ config.documentation }} -- {{ config.type }}
						</p>
					</div>
				</div>
			</div>
		</template>
	</div>
</template>
<script setup lang="ts">
import {
	photoSortingColumnsOptions,
	albumSortingColumnsOptions,
	sortingOrdersOptions,
	licenseOptions,
	aspectRatioOptions,
	SelectBuilders,
	photoLayoutOptions,
	defaultAlbumProtectionOptions,
	overlayOptions,
	mapProvidersOptions,
	toolsOptions,
	currencyOptions,
	paginationUiModeOptions,
} from "@/config/constants";
import StringField from "@/v8/components/forms/settings/StringField.vue";
import BoolField from "@/v8/components/forms/settings/BoolField.vue";
import NumberField from "@/v8/components/forms/settings/NumberField.vue";
import SliderField from "@/v8/components/forms/settings/SliderField.vue";
import SelectField from "@/v8/components/forms/settings/SelectField.vue";
import SelectLang from "@/v8/components/forms/settings/SelectLang.vue";
import SelectOptionsField from "@/v8/components/forms/settings/SelectOptionsField.vue";
import ZipSliderField from "@/v8/components/forms/settings/ZipSliderField.vue";
import OldField from "@/v8/components/forms/settings/OldField.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import { ref } from "vue";
import ColorField from "../forms/settings/ColorField.vue";

const lycheeStore = useLycheeStateStore();
const { is_old_style, is_expert_mode, is_debug_enabled } = storeToRefs(lycheeStore);

const props = defineProps<{
	configs: App.Http.Resources.Models.ConfigResource[];
	modified: App.Http.Resources.Editable.EditableConfigResource[];
}>();

const hoveredRequiredKeys = ref<string[]>([]);

function onRowEnter(config: App.Http.Resources.Models.ConfigResource) {
	hoveredRequiredKeys.value = isConfigActive(config) ? [] : config.required_keys;
}

function onRowLeave() {
	hoveredRequiredKeys.value = [];
}

function isHighlighted(config: App.Http.Resources.Models.ConfigResource): boolean {
	return hoveredRequiredKeys.value.includes(config.key);
}

const emits = defineEmits<{
	filled: [key: string, value: string];
	reset: [key: string];
}>();

function intRangeMin(type: string): number {
	return Number(type.split(":")[1] ?? 0);
}

function intRangeMax(type: string): number {
	return Number(type.split(":")[2] ?? undefined);
}

function reset(configKey: string) {
	emits("reset", configKey);
}

function filled(key: string, value: string) {
	emits("filled", key, value);
}

// Prefers the not-yet-saved edit for a key (if any) over its last-saved value, so toggling a
// setting off immediately dims its dependents without requiring a save + reload round-trip.
function currentValue(key: string): string | undefined {
	const pending = props.modified.find((m) => m.key === key);
	if (pending !== undefined) {
		return pending.value ?? undefined;
	}

	return props.configs.find((c) => c.key === key)?.value;
}

function isConfigActive(config: App.Http.Resources.Models.ConfigResource): boolean {
	if (config.required_keys.length === 0) {
		return true;
	}

	return config.required_keys.every((k) => currentValue(k) === "1");
}

function show(config: App.Http.Resources.Models.ConfigResource) {
	// We do not show that yet, may be later...
	if (config.key === "email") {
		return false;
	}

	if (is_old_style.value === false && config.key === "version") {
		return false;
	}

	return config.is_expert === false || is_expert_mode.value;
}
</script>
