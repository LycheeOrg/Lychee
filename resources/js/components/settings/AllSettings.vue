<template>
	<div v-if="configs">
		<div class="flex gap-4 w-full h-11" v-if="!modified.length">
			<ToggleSwitch v-model="oldStyle" class="text-sm translate-y-1"></ToggleSwitch>
			<p class="text-muted-color">Old settings style</p>
		</div>
		<div v-if="modified.length" class="flex h-11">
			<Message severity="warn" class="w-full" v-if="modified.length">Some settings changed.</Message>
			<Button @click="save" class="bg-danger-800 border-none text-white font-bold px-8 hover:bg-danger-700">Save</Button>
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
						<ZipSliderField v-else-if="config.key === 'zip_deflate_level'" :config="config" @filled="update" @reset="reset" />
						<SelectOptionsField
							v-else-if="config.key === 'default_license'"
							:config="config"
							:options="licenseOptions"
							:mapper="SelectBuilders.buildLicense"
							@filled="update"
							@reset="reset"
						/>
						<SelectOptionsField
							v-else-if="config.key === 'sorting_photos_col'"
							:config="config"
							:options="photoSortingColumnsOptions"
							:mapper="SelectBuilders.buildPhotoSorting"
							@filled="update"
							@reset="reset"
						/>
						<SelectOptionsField
							v-else-if="config.key === 'sorting_photos_order'"
							:config="config"
							:options="sortingOrdersOptions"
							:mapper="SelectBuilders.buildSortingOrder"
							@filled="update"
							@reset="reset"
						/>
						<SelectOptionsField
							v-else-if="config.key === 'sorting_albums_col'"
							:config="config"
							:options="albumSortingColumnsOptions"
							:mapper="SelectBuilders.buildAlbumSorting"
							@filled="update"
							@reset="reset"
						/>
						<SelectOptionsField
							v-else-if="config.key === 'sorting_albums_order'"
							:config="config"
							:options="sortingOrdersOptions"
							:mapper="SelectBuilders.buildSortingOrder"
							@filled="update"
							@reset="reset"
						/>
						<SelectOptionsField
							v-else-if="config.key === 'default_album_thumb_aspect_ratio'"
							:config="config"
							:options="aspectRationOptions"
							:mapper="SelectBuilders.buildAspectRatio"
							@filled="update"
							@reset="reset"
						/>

						<SelectOptionsField
							v-else-if="config.key === 'layout'"
							:config="config"
							:options="photoLayoutOptions"
							:mapper="SelectBuilders.buildPhotoLayout"
							@filled="update"
							@reset="reset"
						/>
						<SelectOptionsField
							v-else-if="config.key === 'default_album_protection'"
							:config="config"
							:options="defaultAlbumProtectionOptions"
							:mapper="SelectBuilders.buildDefaultAlbumProtection"
							@filled="update"
							@reset="reset"
						/>
						<SelectOptionsField
							v-else-if="config.key === 'image_overlay_type'"
							:config="config"
							:options="overlayOptions"
							:mapper="SelectBuilders.buildOverlay"
							@filled="update"
							@reset="reset"
						/>
						<SelectOptionsField
							v-else-if="config.key === 'map_provider'"
							:config="config"
							:options="mapProvidersOptions"
							:mapper="SelectBuilders.buildMapProvider"
							@filled="update"
							@reset="reset"
						/>
						<SelectOptionsField
							v-else-if="config.key === 'has_exiftool'"
							:config="config"
							:options="toolsOptions"
							:mapper="SelectBuilders.buildToolSelection"
							@filled="update"
							@reset="reset"
						/>
						<SelectOptionsField
							v-else-if="config.key === 'has_ffmpeg'"
							:config="config"
							:options="toolsOptions"
							:mapper="SelectBuilders.buildToolSelection"
							@filled="update"
							@reset="reset"
						/>

						<SelectField v-else-if="config.key === 'album_decoration'" :config @filled="update" @reset="reset" />
						<SelectField v-else-if="config.key === 'album_decoration_orientation'" :config @filled="update" @reset="reset" />
						<StringField v-else-if="config.key === 'raw_formats'" :config="config" @filled="update" @reset="reset" />
						<StringField v-else-if="config.key === 'local_takestamp_video_formats'" :config="config" @filled="update" @reset="reset" />
						<!-- Generic -->
						<StringField v-else-if="config.type.startsWith('string')" :config="config" @filled="update" @reset="reset" />
						<BoolField v-else-if="config.type === '0|1'" :config="config" @filled="update" @reset="reset" />
						<NumberField v-else-if="config.type === 'int'" :config="config" :min="0" @filled="update" @reset="reset" />
						<NumberField v-else-if="config.type === 'positive'" :config="config" :min="1" @filled="update" @reset="reset" />
						<SliderField v-else-if="config.type.includes('|')" :config="config" @filled="update" @reset="reset" />
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
import Message from "primevue/message";
import Button from "primevue/button";
import { useToast } from "primevue/usetoast";

const toast = useToast();
const oldStyle = ref(false);
const active = ref([] as string[]);
const configs = ref(undefined as undefined | App.Http.Resources.Collections.ConfigCollectionResource);
const modified = ref([] as App.Http.Resources.Editable.EditableConfigResource[]);

function load() {
	SettingsService.getAll().then((response) => {
		configs.value = response.data as App.Http.Resources.Collections.ConfigCollectionResource;
		active.value = [...Array(Object.keys(configs.value.configs).length).keys()].map((i: number) => i.toString());
	});
}

function update(configKey: string, value: string) {
	const config = modified.value.find((c) => c.key === configKey);
	console.log(configKey, value);
	if (config) {
		config.value = value;
	} else {
		const configData = {
			key: configKey,
			value: value,
		};
		modified.value.push(configData);
	}
}

function reset(configKey: string) {
	const index = modified.value.findIndex((c) => c.key === configKey);
	if (index !== -1) {
		modified.value.splice(index, 1);
	}
}

function save() {
	SettingsService.setConfigs({ configs: modified.value }).then(() => {
		modified.value = [];
		toast.add({ severity: "success", summary: "Change saved!", detail: "Settings have been modified as per request", life: 3000 });
		load();
	});
}

load();
</script>
