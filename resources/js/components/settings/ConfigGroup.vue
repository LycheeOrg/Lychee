<template>
	<div class="flex flex-col gap-4 w-full">
		<template v-for="config in props.configs">
			<div v-if="show(config)" class="flex gap-2">
				<div class="shrink h-8 w-4 flex items-center">
					<i class="pi pi-graduation-cap text-primary-500" v-if="config.is_expert"></i>
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
						v-else-if="config.key === 'default_album_thumb_aspect_ratio'"
						:config="config"
						:options="aspectRationOptions"
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
					<SelectLang v-else-if="config.key === 'lang'" :config="config" @filled="filled" @reset="reset" />
					<SelectField v-else-if="config.key === 'album_decoration'" :config @filled="filled" @reset="reset" />
					<SelectField v-else-if="config.key === 'album_decoration_orientation'" :config @filled="filled" @reset="reset" />
					<StringField v-else-if="config.key === 'raw_formats'" :config="config" @filled="filled" @reset="reset" />
					<StringField v-else-if="config.key === 'local_takestamp_video_formats'" :config="config" @filled="filled" @reset="reset" />
					<!-- Generic -->
					<StringField v-else-if="config.type.startsWith('string')" :config="config" @filled="filled" @reset="reset" />
					<BoolField v-else-if="config.type === '0|1'" :config="config" @filled="filled" @reset="reset" />
					<NumberField v-else-if="config.type === 'int'" :config="config" :min="0" @filled="filled" @reset="reset" />
					<NumberField v-else-if="config.type === 'positive'" :config="config" :min="1" @filled="filled" @reset="reset" />
					<SliderField v-else-if="config.type.includes('|')" :config="config" @filled="filled" @reset="reset" />
					<p v-else class="bg-red-500">{{ config.key }} -- {{ config.value }} -- {{ config.documentation }} -- {{ config.type }}</p>
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
	aspectRationOptions,
	SelectBuilders,
	photoLayoutOptions,
	defaultAlbumProtectionOptions,
	overlayOptions,
	mapProvidersOptions,
	toolsOptions,
} from "@/config/constants";
import StringField from "../forms/settings/StringField.vue";
import BoolField from "../forms/settings/BoolField.vue";
import NumberField from "../forms/settings/NumberField.vue";
import SliderField from "../forms/settings/SliderField.vue";
import SelectField from "../forms/settings/SelectField.vue";
import SelectLang from "../forms/settings/SelectLang.vue";
import SelectOptionsField from "../forms/settings/SelectOptionsField.vue";
import ZipSliderField from "../forms/settings/ZipSliderField.vue";
import OldField from "../forms/settings/OldField.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";

const lycheeStore = useLycheeStateStore();
const { is_old_style, is_expert_mode } = storeToRefs(lycheeStore);

const props = defineProps<{
	configs: App.Http.Resources.Models.ConfigResource[];
}>();

const emits = defineEmits<{
	filled: [key: string, value: string];
	reset: [key: string];
}>();

function reset(configKey: string) {
	emits("reset", configKey);
}

function filled(key: string, value: string) {
	emits("filled", key, value);
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
