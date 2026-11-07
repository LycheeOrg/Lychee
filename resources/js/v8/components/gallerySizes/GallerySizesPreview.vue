<template>
	<div class="flex flex-col gap-3 min-w-0">
		<div class="flex flex-wrap items-center gap-x-3 gap-y-2">
			<span id="gallery-sizes-width-label" class="text-sm font-medium">{{ $t("gallery_sizes.width") }}</span>
			<UFieldGroup role="group" aria-labelledby="gallery-sizes-width-label" class="flex-wrap">
				<UButton
					v-for="option in presetOptions"
					:key="option.id"
					:label="$t(`gallery_sizes.presets.${option.id}`)"
					:icon="option.id === 'window' ? 'lucide:maximize' : undefined"
					size="sm"
					:color="preset === option.id ? 'primary' : 'neutral'"
					:variant="preset === option.id ? 'solid' : 'outline'"
					:aria-pressed="preset === option.id"
					@click="preset = option.id"
				/>
			</UFieldGroup>
			<span class="text-sm text-muted tabular-nums">{{
				$t("gallery_sizes.shown_at", { width: String(Math.round(viewportWidth)), scale: String(Math.round(scale * 100)) })
			}}</span>
		</div>

		<div
			ref="paneRef"
			class="relative w-full overflow-hidden rounded-lg border border-default bg-default"
			:style="{ height: `${Math.ceil(canvasHeight * scale)}px` }"
		>
			<div
				aria-hidden="true"
				class="absolute top-0 ltr:left-0 rtl:right-0 ltr:origin-top-left rtl:origin-top-right"
				:style="{ width: `${viewportWidth}px`, height: `${canvasHeight}px`, transform: `scale(${scale})` }"
			>
				<div class="relative" :style="{ height: `${geometry.albums.height}px`, marginTop: `${PADDING_Y}px` }">
					<div
						v-for="(album, i) in props.placeholders.albums"
						:key="album.id"
						class="absolute flex items-end overflow-hidden shadow-md shadow-black/25"
						:class="{ 'rounded-lg': is_rounded_corners_enabled }"
						:style="tileStyle(geometry.albums.boxes[i], geometry.offsets.albums, album.hue, 35)"
					>
						<span class="w-full truncate bg-black/40 px-2 py-1 text-xs font-semibold text-white">{{
							$t("gallery_sizes.album_title", { n: String(album.n) })
						}}</span>
					</div>
				</div>
				<div class="relative" :style="{ height: `${geometry.photos.height}px`, marginTop: `${SECTION_GAP}px` }">
					<div
						v-for="(photo, i) in visiblePhotos"
						:key="photo.id"
						class="absolute"
						:style="tileStyle(geometry.photos.boxes[i], geometry.offsets.photos, photo.hue, 55)"
					/>
				</div>
			</div>
		</div>

		<p class="text-sm text-muted" role="status">
			{{ $t("gallery_sizes.summary", { albums: String(geometry.albums.itemsPerRow), rows: String(geometry.photos.rows) }) }}
		</p>
	</div>
</template>
<script setup lang="ts">
/**
 * Feature 110 (FR-110-06, FR-110-07, FR-110-09, NFR-110-01/02/06): the
 * placeholders laid out for a simulated window width, scaled down to fit the
 * pane. Geometry comes from `computePreviewGeometry` (the gallery's own
 * functions); tiles are plain coloured blocks, no images.
 */
import { computed, onMounted, ref } from "vue";
import { storeToRefs } from "pinia";
import { useElementSize, useWindowSize } from "@vueuse/core";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { isTouchDevice } from "@/utils/keybindings-utils";
import { ASSUMED_SCROLLBAR_WIDTH } from "@/v8/composables/album/albumTileWidth";
import { initLayouts } from "@/v8/layouts/wasmLayouts";
import type { GallerySizes } from "@/v8/utils/gallerySizes";
import type { PreviewPlaceholders } from "@/v8/utils/galleryPreviewPlaceholders";
import { PREVIEW_PRESETS, computePreviewGeometry, previewScale, type PreviewWidthPreset } from "@/v8/utils/galleryPreviewGeometry";

const props = defineProps<{
	sizes: GallerySizes;
	layout: App.Enum.PhotoLayoutType;
	placeholders: PreviewPlaceholders;
}>();

const preset = defineModel<PreviewWidthPreset>("preset", { default: "window" });

const PADDING_Y = 16;
const SECTION_GAP = 32;

const { is_rounded_corners_enabled } = storeToRefs(useLycheeStateStore());

const presetOptions: { id: PreviewWidthPreset }[] = [...PREVIEW_PRESETS.map((p) => ({ id: p.id })), { id: "window" }];

const paneRef = ref<HTMLElement>();
const { width: paneWidth } = useElementSize(paneRef);
const { width: windowWidth } = useWindowSize();

const viewportWidth = computed(() => PREVIEW_PRESETS.find((p) => p.id === preset.value)?.width ?? windowWidth.value);
const scrollbarWidth = computed(() => {
	const fixed = PREVIEW_PRESETS.find((p) => p.id === preset.value);
	const touch = fixed !== undefined ? fixed.touch : isTouchDevice();
	return touch ? 0 : ASSUMED_SCROLLBAR_WIDTH;
});

// The WASM layout primitives need their module instantiated; photos wait for it.
const layoutsReady = ref(false);
onMounted(() => {
	initLayouts().then(() => {
		layoutsReady.value = true;
	});
});

const visiblePhotos = computed(() => (layoutsReady.value ? props.placeholders.photos : []));

const geometry = computed(() =>
	computePreviewGeometry({
		viewportWidth: viewportWidth.value,
		scrollbarWidth: scrollbarWidth.value,
		sizes: props.sizes,
		layout: props.layout,
		albumCount: props.placeholders.albums.length,
		photoRatios: visiblePhotos.value.map((p) => p.ratio),
	}),
);

const canvasHeight = computed(() => PADDING_Y * 2 + geometry.value.albums.height + SECTION_GAP + geometry.value.photos.height);
const scale = computed(() => previewScale(viewportWidth.value, paneWidth.value));

function tileStyle(box: { top: number; left: number; width: number; height: number } | undefined, offset: number, hue: number, lightness: number) {
	if (box === undefined) {
		return { display: "none" };
	}
	return {
		top: `${box.top}px`,
		insetInlineStart: `${offset + box.left}px`,
		width: `${box.width}px`,
		height: `${box.height}px`,
		backgroundColor: `hsl(${hue} 40% ${lightness}%)`,
	};
}
</script>
