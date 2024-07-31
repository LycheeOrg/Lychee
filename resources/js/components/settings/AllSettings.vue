<template>
	<div v-if="configs">
		<div class="flex gap-4 w-full">
			<ToggleSwitch v-model="oldStyle" class="text-sm translate-y-1"></ToggleSwitch>
			<p class="text-muted-color">Old settings style</p>
		</div>
		<Fieldset
			v-for="(configGroup, key, index) in configs.configs"
			:legend="key"
			:toggleable="true"
			class="border-b-0 border-r-0 rounded-r-none rounded-b-none mb-4 hover:border-primary-500 pt-2"
		>
			<div class="flex flex-col gap-4">
				<template v-for="config in configGroup">
					<template v-if="oldStyle">
						<OldField :config="config" />
					</template>
					<template v-else>
						<!-- Special keys -->
						<VersionField v-if="config.key === 'version'" :config="config" />
						<ZipSliderField v-else-if="config.key === 'zip_deflate_level'" :config="config" />
						<SelectOptionsField
							v-else-if="config.key === 'default_license'"
							:config="config"
							:options="licenseOptions"
							:mapper="SelectBuilders.buildLicense"
						/>
						<SelectOptionsField
							v-else-if="config.key === 'sorting_photos_col'"
							:config="config"
							:options="photoSortingColumnsOptions"
							:mapper="SelectBuilders.buildPhotoSorting"
						/>
						<SelectOptionsField
							v-else-if="config.key === 'sorting_photos_order'"
							:config="config"
							:options="sortingOrdersOptions"
							:mapper="SelectBuilders.buildSortingOrder"
						/>
						<SelectOptionsField
							v-else-if="config.key === 'sorting_albums_col'"
							:config="config"
							:options="albumSortingColumnsOptions"
							:mapper="SelectBuilders.buildAlbumSorting"
						/>
						<SelectOptionsField
							v-else-if="config.key === 'sorting_albums_order'"
							:config="config"
							:options="sortingOrdersOptions"
							:mapper="SelectBuilders.buildSortingOrder"
						/>
						<SelectOptionsField
							v-else-if="config.key === 'default_album_thumb_aspect_ratio'"
							:config="config"
							:options="aspectRationOptions"
							:mapper="SelectBuilders.buildAspectRatio"
						/>

						<SelectOptionsField
							v-else-if="config.key === 'layout'"
							:config="config"
							:options="photoLayoutOptions"
							:mapper="SelectBuilders.buildPhotoLayout"
						/>
						<SelectOptionsField
							v-else-if="config.key === 'default_album_protection'"
							:config="config"
							:options="defaultAlbumProtectionOptions"
							:mapper="SelectBuilders.buildDefaultAlbumProtection"
						/>
						<SelectOptionsField
							v-else-if="config.key === 'image_overlay_type'"
							:config="config"
							:options="overlayOptions"
							:mapper="SelectBuilders.buildOverlay"
						/>
						<SelectOptionsField
							v-else-if="config.key === 'map_provider'"
							:config="config"
							:options="mapProvidersOptions"
							:mapper="SelectBuilders.buildMapProvider"
						/>
						<SelectOptionsField
							v-else-if="config.key === 'has_exiftool'"
							:config="config"
							:options="toolsOptions"
							:mapper="SelectBuilders.buildToolSelection"
						/>
						<SelectOptionsField
							v-else-if="config.key === 'has_ffmpeg'"
							:config="config"
							:options="toolsOptions"
							:mapper="SelectBuilders.buildToolSelection"
						/>

						<SelectField v-else-if="config.key === 'album_decoration'" :config />
						<SelectField v-else-if="config.key === 'album_decoration_orientation'" :config />
						<StringField v-else-if="config.key === 'raw_formats'" :config="config" />
						<StringField v-else-if="config.key === 'local_takestamp_video_formats'" :config="config" />
						<!-- Generic -->
						<StringField v-else-if="config.type.startsWith('string')" :config="config" />
						<BoolField v-else-if="config.type === '0|1'" :config="config" />
						<NumberField v-else-if="config.type === 'int'" :config="config" :min="0" />
						<NumberField v-else-if="config.type === 'positive'" :config="config" :min="1" />
						<SliderField v-else-if="config.type.includes('|')" :config="config" />
						<p v-else class="bg-red-500">{{ config.key }} -- {{ config.value }} -- {{ config.documentation }} -- {{ config.type }}</p>
					</template>
				</template>
			</div>
		</Fieldset>
	</div>
</template>
<script setup lang="ts">
import SettingsService from "@/services/settings-service";
import { ref } from "vue";
import StringField from "@/components/forms/settings/StringField.vue";
import BoolField from "@/components/forms/settings/BoolField.vue";
import NumberField from "@/components/forms/settings/NumberField.vue";
import VersionField from "@/components/forms/settings/VersionField.vue";
import Fieldset from "primevue/fieldset";
import SliderField from "@/components/forms/settings/SliderField.vue";
import SelectField from "@/components/forms/settings/SelectField.vue";
import SelectOptionsField from "@/components/forms/settings/SelectOptionsField.vue";
import {
	photoSortingColumnsOptions,
	albumSortingColumnsOptions,
	sortingOrdersOptions,
	licenseOptions,
	aspectRationOptions,
	SelectOption,
	SelectBuilders,
	photoLayoutOptions,
	defaultAlbumProtectionOptions,
	overlayOptions,
	mapProvidersOptions,
	toolsOptions,
} from "@/config/constants";
import ZipSliderField from "../forms/settings/ZipSliderField.vue";
import ToggleSwitch from "primevue/toggleswitch";
import OldField from "../forms/settings/OldField.vue";

const oldStyle = ref(false);
const active = ref([] as string[]);
const configs = ref(undefined as undefined | App.Http.Resources.Collections.ConfigCollectionResource);

function load() {
	SettingsService.getAll().then((response) => {
		configs.value = response.data as App.Http.Resources.Collections.ConfigCollectionResource;
		active.value = [...Array(Object.keys(configs.value.configs).length).keys()].map((i: number) => i.toString());
	});
}

load();
</script>
