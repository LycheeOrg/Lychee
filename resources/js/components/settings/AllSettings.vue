<template>
	<div v-if="configs">
		<div class="flex gap-4 w-full h-11" v-if="!modified.length">
			<ToggleSwitch v-model="oldStyle" class="text-sm translate-y-1" input-id="oldStyleToggle"></ToggleSwitch>
			<label for="oldStyleToggle" class="text-muted-color">{{ $t("settings.all.old_setting_style") }}</label>
		</div>
		<div v-if="modified.length" class="sticky z-30 w-full top-0 flex bg-white dark:bg-surface-800 h-11">
			<Message severity="warn" class="w-full" v-if="modified.length">{{ $t("settings.all.change_detected") }}</Message>
			<Button @click="save" class="bg-danger-800 border-none text-white font-bold px-8 hover:bg-danger-700">{{
				$t("settings.all.save")
			}}</Button>
		</div>
		<div class="flex relative items-start flex-row-reverse justify-between">
			<Menu :model="sections" class="top-11 border-none hidden sticky sm:block" id="navMain">
				<template #item="{ item, props }">
					<a
						:href="item.link"
						class="nav-link block hover:text-primary-400 border-l border-solid border-surface-700 hover:border-primary-400 px-4 capitalize"
						@click.prevent="goto(item.link)"
					>
						<span>{{ item.label }}</span>
					</a>
				</template>
			</Menu>
			<div class="max-w-3xl" id="allSettings">
				<Fieldset
					v-for="(configGroup, key, index) in configs.configs"
					:legend="key.toString()"
					:toggleable="true"
					class="border-b-0 border-r-0 rounded-r-none rounded-b-none mb-4 hover:border-primary-500 pt-2"
					:pt:legendlabel:class="'capitalize'"
					:id="key"
				>
					<div class="flex flex-col gap-4">
						<template v-for="config in configGroup">
							<template v-if="show(config)">
								<template v-if="oldStyle">
									<OldField :config="config" @filled="update" @reset="reset" />
								</template>
								<template v-else>
									<!-- Special keys -->
									<template v-if="config.key === 'version'">
										<!-- version is not modifiable in easy settings. -->
									</template>
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
										v-else-if="config.type === 'square|justified|masonry|grid'"
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
									<SelectLang v-else-if="config.key === 'lang'" :config="config" @filled="update" @reset="reset" />
									<SelectField v-else-if="config.key === 'album_decoration'" :config @filled="update" @reset="reset" />
									<SelectField v-else-if="config.key === 'album_decoration_orientation'" :config @filled="update" @reset="reset" />
									<StringField v-else-if="config.key === 'raw_formats'" :config="config" @filled="update" @reset="reset" />
									<StringField
										v-else-if="config.key === 'local_takestamp_video_formats'"
										:config="config"
										@filled="update"
										@reset="reset"
									/>
									<!-- Generic -->
									<StringField v-else-if="config.type.startsWith('string')" :config="config" @filled="update" @reset="reset" />
									<BoolField v-else-if="config.type === '0|1'" :config="config" @filled="update" @reset="reset" />
									<NumberField v-else-if="config.type === 'int'" :config="config" :min="0" @filled="update" @reset="reset" />
									<NumberField v-else-if="config.type === 'positive'" :config="config" :min="1" @filled="update" @reset="reset" />
									<SliderField v-else-if="config.type.includes('|')" :config="config" @filled="update" @reset="reset" />
									<p v-else class="bg-red-500">
										{{ config.key }} -- {{ config.value }} -- {{ config.documentation }} -- {{ config.type }}
									</p>
								</template>
							</template>
						</template>
					</div>
				</Fieldset>
			</div>
		</div>
	</div>
</template>
<script setup lang="ts">
import { computed, onUpdated, ref } from "vue";
import Menu from "primevue/menu";
import Message from "primevue/message";
import Button from "primevue/button";
import Fieldset from "primevue/fieldset";
import ToggleSwitch from "primevue/toggleswitch";
import { useToast } from "primevue/usetoast";
import StringField from "@/components/forms/settings/StringField.vue";
import BoolField from "@/components/forms/settings/BoolField.vue";
import NumberField from "@/components/forms/settings/NumberField.vue";
import SliderField from "@/components/forms/settings/SliderField.vue";
import SelectField from "@/components/forms/settings/SelectField.vue";
import SelectOptionsField from "@/components/forms/settings/SelectOptionsField.vue";
import OldField from "@/components/forms/settings/OldField.vue";
import ZipSliderField from "@/components/forms/settings/ZipSliderField.vue";
import SelectLang from "@/components/forms/settings/SelectLang.vue";
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
// @ts-expect-error
import scrollSpy from "@sidsbrmnn/scrollspy";
import SettingsService from "@/services/settings-service";
import { trans } from "laravel-vue-i18n";

const toast = useToast();
const oldStyle = ref(false);
const active = ref<string[]>([]);
const configs = ref<App.Http.Resources.Collections.ConfigCollectionResource | undefined>(undefined);
const modified = ref<App.Http.Resources.Editable.EditableConfigResource[]>([]);
const sections = computed(function () {
	if (!configs.value) {
		return [];
	}
	return Object.keys(configs.value.configs).map((key) => {
		return {
			label: key,
			link: "#" + key,
		};
	});
});

function load() {
	SettingsService.getAll().then((response) => {
		configs.value = response.data as App.Http.Resources.Collections.ConfigCollectionResource;
		active.value = [...Array(Object.keys(configs.value.configs).length).keys()].map((i: number) => i.toString());
	});
}

function update(configKey: string, value: string) {
	const config = modified.value.find((c) => c.key === configKey);
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
	SettingsService.setConfigs({ configs: modified.value })
		.then(() => {
			modified.value = [];
			toast.add({ severity: "success", summary: trans("settings.toasts.change_saved"), detail: trans("settings.toasts.details"), life: 3000 });
			load();
		})
		.catch((e) => {
			toast.add({ severity: "error", summary: trans("settings.toasts.error"), detail: e.response.data.message, life: 3000 });
		});
}

function goto(section: string) {
	const el = document.getElementById(section.slice(1));
	if (el) {
		el.scrollIntoView({ behavior: "smooth" });
	}
}

load();

function show(config: App.Http.Resources.Editable.EditableConfigResource) {
	// We do not show that yet, may be later...
	return config.key !== "email";
}

onUpdated(function () {
	const elem = document.getElementById("navMain");
	if (!elem) {
		return;
	}

	const spy = scrollSpy(document.getElementById("navMain"), {
		sectionSelector: "#allSettings .p-fieldset", // Query selector to your sections
		targetSelector: ".nav-link", // Query select
		activeClass: "!text-primary-500 !border-primary-500",
	});
	// Set the first section as active.
	const admin = spy.sections[0];
	const adminMenuItem = spy.getCurrentMenuItem(admin);
	spy.setActive(adminMenuItem, admin);
});
</script>
