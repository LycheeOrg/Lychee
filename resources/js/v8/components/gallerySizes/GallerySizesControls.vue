<template>
	<div class="flex flex-col gap-6">
		<component :is="props.plain ? 'section' : Fieldset" v-if="props.showAlbums" v-bind="sectionProps('gallery_sizes.section_albums')">
			<h3 v-if="props.plain" class="mb-3 text-sm font-semibold text-highlighted">{{ $t("gallery_sizes.section_albums") }}</h3>
			<div class="flex flex-col gap-4">
				<div v-for="row in albumRows" :key="row.key" class="flex flex-col gap-1">
					<div class="flex items-center justify-between gap-2">
						<label
							:for="`gallery-sizes-${row.key}`"
							class="text-sm font-medium select-none"
							:title="$t('gallery_sizes.reset_hint')"
							@dblclick="reset(row.key)"
							>{{ $t(row.label) }}</label
						>
						<UInputNumber
							:id="`gallery-sizes-${row.key}`"
							:model-value="props.sizes[row.key]"
							:min="GALLERY_SIZE_RANGES[row.key].min"
							:max="GALLERY_SIZE_RANGES[row.key].max"
							:step="GALLERY_SIZE_RANGES[row.key].step"
							:step-snapping="false"
							:disabled="props.disabled"
							size="sm"
							class="w-28"
							@update:model-value="(value) => setNumber(row.key, value)"
						/>
					</div>
					<USlider
						:model-value="sliderValue(row.key)"
						:min="GALLERY_SIZE_RANGES[row.key].min"
						:max="GALLERY_SIZE_RANGES[row.key].max"
						:step="GALLERY_SIZE_RANGES[row.key].step"
						:disabled="props.disabled"
						:aria-label="$t(row.label)"
						@update:model-value="(value) => setNumber(row.key, value as number | undefined)"
					/>
				</div>
				<div class="flex items-center justify-between gap-2">
					<label
						for="gallery-sizes-aspect-ratio"
						class="text-sm font-medium select-none"
						:title="$t('gallery_sizes.reset_hint')"
						@dblclick="reset('default_album_thumb_aspect_ratio')"
						>{{ $t("gallery_sizes.album_aspect_ratio") }}</label
					>
					<USelect
						id="gallery-sizes-aspect-ratio"
						:model-value="props.sizes.default_album_thumb_aspect_ratio"
						:items="aspectRatioItems"
						:disabled="props.disabled"
						size="sm"
						class="w-40"
						@update:model-value="(value) => emits('set', 'default_album_thumb_aspect_ratio', value as App.Enum.AspectRatioType)"
					/>
				</div>
				<div v-if="isEditable('number_albums_per_row_mobile')" class="flex items-center justify-between gap-2">
					<span
						id="gallery-sizes-per-row-label"
						class="text-sm font-medium select-none"
						:title="$t('gallery_sizes.reset_hint')"
						@dblclick="reset('number_albums_per_row_mobile')"
						>{{ $t("gallery_sizes.albums_per_row_mobile") }}</span
					>
					<UFieldGroup role="group" aria-labelledby="gallery-sizes-per-row-label">
						<UButton
							v-for="count in PER_ROW_MOBILE"
							:key="count"
							:label="String(count)"
							size="sm"
							:color="props.sizes.number_albums_per_row_mobile === count ? 'primary' : 'neutral'"
							:variant="props.sizes.number_albums_per_row_mobile === count ? 'solid' : 'outline'"
							:aria-pressed="props.sizes.number_albums_per_row_mobile === count"
							:disabled="props.disabled"
							@click="emits('set', 'number_albums_per_row_mobile', count)"
						/>
					</UFieldGroup>
				</div>
			</div>
		</component>

		<component :is="props.plain ? 'section' : Fieldset" v-if="props.showPhotos" v-bind="sectionProps('gallery_sizes.section_photos')">
			<h3 v-if="props.plain" class="mb-3 text-sm font-semibold text-highlighted">{{ $t("gallery_sizes.section_photos") }}</h3>
			<div class="flex flex-col gap-4">
				<div class="flex flex-col gap-1">
					<span id="gallery-sizes-layout-label" class="text-sm font-medium">{{ $t("gallery_sizes.layout") }}</span>
					<UFieldGroup role="group" aria-labelledby="gallery-sizes-layout-label" class="flex-wrap">
						<UButton
							v-for="layout in LAYOUTS"
							:key="layout"
							:label="$t(`gallery_sizes.layouts.${layout}`)"
							size="sm"
							:color="props.layout === layout ? 'primary' : 'neutral'"
							:variant="props.layout === layout ? 'solid' : 'outline'"
							:aria-pressed="props.layout === layout"
							@click="emits('update:layout', layout)"
						/>
					</UFieldGroup>
				</div>
				<div v-for="row in photoRows" :key="row.key" class="flex flex-col gap-1">
					<div class="flex items-center justify-between gap-2">
						<label
							:for="`gallery-sizes-${row.key}`"
							class="text-sm font-medium select-none"
							:class="{ 'text-muted': isInactiveRow(row.key) }"
							:title="$t('gallery_sizes.reset_hint')"
							@dblclick="reset(row.key)"
							>{{ $t(row.label) }}</label
						>
						<UInputNumber
							:id="`gallery-sizes-${row.key}`"
							:model-value="props.sizes[row.key]"
							:min="GALLERY_SIZE_RANGES[row.key].min"
							:max="GALLERY_SIZE_RANGES[row.key].max"
							:step="GALLERY_SIZE_RANGES[row.key].step"
							:step-snapping="false"
							:disabled="props.disabled"
							size="sm"
							class="w-28"
							@update:model-value="(value) => setNumber(row.key, value)"
						/>
					</div>
					<USlider
						:model-value="sliderValue(row.key)"
						:min="GALLERY_SIZE_RANGES[row.key].min"
						:max="GALLERY_SIZE_RANGES[row.key].max"
						:step="GALLERY_SIZE_RANGES[row.key].step"
						:disabled="props.disabled"
						:aria-label="$t(row.label)"
						@update:model-value="(value) => setNumber(row.key, value as number | undefined)"
					/>
				</div>
			</div>
		</component>
	</div>
</template>
<script setup lang="ts">
/**
 * Feature 110 (FR-110-05, FR-110-08, UI-110-05): the controls of the 9 size
 * settings, shared by the preview page and the live drawer. Stateless: the
 * parent owns the draft (`useGallerySizesDraft`) and the shown layout.
 */
import { computed } from "vue";
import { trans } from "laravel-vue-i18n";
import Fieldset from "@/v8/components/forms/basic/Fieldset.vue";
import { aspectRatioOptions } from "@/config/constants";
import {
	GALLERY_SIZE_RANGES,
	layoutOfGallerySize,
	type GallerySizeKey,
	type GallerySizes,
	type NumericGallerySizeKey,
} from "@/v8/utils/gallerySizes";

const props = withDefaults(
	defineProps<{
		sizes: GallerySizes;
		editable: readonly GallerySizeKey[];
		layout: App.Enum.PhotoLayoutType;
		disabled?: boolean;
		toggleable?: boolean;
		/** Plain section headings instead of bordered fieldsets (live drawer, owner request 2026-11-07). */
		plain?: boolean;
		/** Live drawer: show a section only when the page has its tiles (FR-110-14). */
		showAlbums?: boolean;
		showPhotos?: boolean;
	}>(),
	{ showAlbums: true, showPhotos: true },
);

const emits = defineEmits<{
	set: [key: GallerySizeKey, value: GallerySizes[GallerySizeKey]];
	/** Double-click on a label: put back the saved value of that setting. */
	reset: [key: GallerySizeKey];
	"update:layout": [layout: App.Enum.PhotoLayoutType];
}>();

const LAYOUTS: readonly App.Enum.PhotoLayoutType[] = ["justified", "masonry", "square", "grid"];
const PER_ROW_MOBILE: readonly (1 | 2 | 3)[] = [1, 2, 3];

type Row = { key: NumericGallerySizeKey; label: string };
const albumRows: Row[] = [
	{ key: "album_thumb_min_width", label: "gallery_sizes.album_min_width" },
	{ key: "album_thumb_gap", label: "gallery_sizes.gap" },
];
const photoRows: Row[] = [
	{ key: "photo_layout_gap", label: "gallery_sizes.gap" },
	{ key: "photo_layout_justified_row_height", label: "gallery_sizes.justified_row_height" },
	{ key: "photo_layout_masonry_column_width", label: "gallery_sizes.masonry_column_width" },
	{ key: "photo_layout_grid_column_width", label: "gallery_sizes.grid_column_width" },
	{ key: "photo_layout_square_column_width", label: "gallery_sizes.square_column_width" },
];

const aspectRatioItems = computed(() => aspectRatioOptions.map((option) => ({ value: option.value, label: trans(option.label) })));

/** Fieldset props; none for a plain section, which renders its own heading. */
function sectionProps(label: string): Record<string, unknown> {
	return props.plain ? {} : { legend: trans(label), toggleable: props.toggleable };
}

function isEditable(key: GallerySizeKey): boolean {
	return props.editable.includes(key);
}

/**
 * A setting of another layout than the shown one: muted, so the row that matters
 * stands out without colour (owner request 2026-11-07). Shared settings (gap) never are.
 */
function isInactiveRow(key: GallerySizeKey): boolean {
	const layout = layoutOfGallerySize(key);
	return layout !== null && layout !== props.layout;
}

/** A stored value outside the range pins the slider at its end (UI-110-05); the number input shows it as is. */
function sliderValue(key: NumericGallerySizeKey): number {
	const range = GALLERY_SIZE_RANGES[key];
	return Math.min(range.max, Math.max(range.min, props.sizes[key]));
}

/** Shows the layout a layout-specific setting belongs to, so its change is visible. */
function showLayoutOf(key: GallerySizeKey): void {
	const layout = layoutOfGallerySize(key);
	if (layout !== null && layout !== props.layout) {
		emits("update:layout", layout);
	}
}

function reset(key: GallerySizeKey): void {
	emits("reset", key);
	showLayoutOf(key);
}

function setNumber(key: NumericGallerySizeKey, value: number | null | undefined): void {
	if (value === null || value === undefined) {
		return;
	}
	emits("set", key, value);
	showLayoutOf(key);
}
</script>
