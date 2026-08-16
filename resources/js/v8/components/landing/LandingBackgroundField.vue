<template>
	<Fieldset :legend="label">
		<div class="flex flex-col gap-4">
			<div class="flex items-center gap-4">
				<label class="font-semibold w-1/2">{{ $t("landing_config.field_background_mode") }}</label>
				<USelectMenu v-model="selectedMode" :items="modeOptions" label-key="label" class="w-1/2">
					<template #item-label="{ item }">{{ item.label }}</template>
				</USelectMenu>
			</div>

			<UFormField v-if="mode === 'static'" :label="$t('landing_config.field_background_url')">
				<UInput v-model="value" class="w-full" :placeholder="$t('landing_config.field_background_url_placeholder')" />
			</UFormField>

			<div v-else-if="mode === 'photo_id'" class="flex flex-col gap-1">
				<label class="text-sm font-medium">{{ $t("landing_config.field_background_photo_id") }}</label>
				<div class="flex gap-2">
					<UInput
						v-model="value"
						class="flex-1 text-sm"
						:placeholder="$t('landing_config.field_background_photo_id_placeholder')"
						@keydown.enter="loadPhoto"
					/>
					<UButton icon="lucide:refresh-cw" color="neutral" variant="ghost" @click="loadPhoto" />
				</div>
				<small class="text-muted text-xs">{{ $t("landing_config.field_background_photo_id_hint") }}</small>
				<small v-if="loadError" class="text-error text-xs">{{ $t("landing_config.background_load_error") }}</small>
			</div>

			<UFormField v-else-if="mode === 'random_from_album'" :label="$t('landing_config.field_background_album_id')">
				<UInput v-model="value" class="w-full" :placeholder="$t('landing_config.field_background_album_id_placeholder')" />
				<template #hint>{{ $t("landing_config.background_mode_hint.random_from_album") }}</template>
			</UFormField>

			<p v-else class="text-muted text-xs">{{ $t(`landing_config.background_mode_hint.${mode}`) }}</p>
		</div>
	</Fieldset>
</template>
<script setup lang="ts">
import { computed, onMounted, ref, watch } from "vue";
import { trans } from "laravel-vue-i18n";
import Fieldset from "@/v8/components/forms/basic/Fieldset.vue";
import ModerationService from "@/services/moderation-service";

defineProps<{
	label: string;
}>();

const mode = defineModel<App.Enum.LandingBackgroundModeType>("mode", { required: true });
const value = defineModel<string>("value", { required: true });
// The resolved image URL for this mode/value, so the parent can feed it into the actual live
// preview panel instead of duplicating a thumbnail here. Stays null for modes that can't be
// resolved client-side (random / latest_album_cover / random_from_album) — the live preview
// falls back to the last-saved value for those until the admin actually saves.
const resolvedUrl = defineModel<string | null>("resolvedUrl", { default: null });

type Option = { label: string; value: App.Enum.LandingBackgroundModeType };

const modeOptions: Option[] = [
	{ value: "static", label: trans("landing_config.background_mode_options.static") },
	{ value: "photo_id", label: trans("landing_config.background_mode_options.photo_id") },
	{ value: "random", label: trans("landing_config.background_mode_options.random") },
	{ value: "latest_album_cover", label: trans("landing_config.background_mode_options.latest_album_cover") },
	{ value: "random_from_album", label: trans("landing_config.background_mode_options.random_from_album") },
];

const selectedMode = computed<Option | undefined>({
	get: () => modeOptions.find((o) => o.value === mode.value),
	set: (v) => {
		if (!v || v.value === mode.value) {
			return;
		}
		mode.value = v.value;
		value.value = "";
		resolvedUrl.value = null;
		loadError.value = false;
	},
});

const loadError = ref(false);

function loadPhoto(): void {
	const id = value.value.trim();
	if (!id) {
		resolvedUrl.value = null;
		loadError.value = false;
		return;
	}
	loadError.value = false;
	ModerationService.getPhoto(id)
		.then((resp) => {
			const photo = resp.data;
			const url =
				photo.size_variants.medium?.url ??
				photo.size_variants.small?.url ??
				photo.size_variants.thumb2x?.url ??
				photo.size_variants.thumb?.url ??
				photo.size_variants.original?.url ??
				null;
			resolvedUrl.value = url;
			if (!url) {
				loadError.value = true;
			}
		})
		.catch(() => {
			resolvedUrl.value = null;
			loadError.value = true;
		});
}

// Static mode's value IS the URL, so it can resolve instantly without a round-trip.
watch(
	value,
	() => {
		if (mode.value === "static") {
			resolvedUrl.value = value.value !== "" ? value.value : null;
		}
	},
	{ immediate: true },
);

onMounted(() => {
	if (mode.value === "photo_id" && value.value !== "") {
		loadPhoto();
	}
});
</script>
