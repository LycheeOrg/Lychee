<template>
	<div ref="containerRef" class="w-full">
		<div data-photo-grid-root role="list" class="relative w-full" :style="{ height: `${layout.totalHeight}px` }">
			<!-- Sticky pinned header: mirrors AlbumThumbGridVirtual.vue's own
			     mechanism exactly — only rendered once the active bucket's own
			     real header row has scrolled past the top. -->
			<div
				v-if="activeHeaderLabel !== null"
				class="sticky top-(--ui-header-height) z-10 pointer-events-none"
				:style="{ height: `${HEADER_ROW_HEIGHT}px`, marginBottom: `-${HEADER_ROW_HEIGHT}px` }"
			>
				<div class="w-full h-full flex items-center font-semibold text-toned text-lg bg-default/50 backdrop-blur">
					{{ activeHeaderLabel }}
				</div>
			</div>
			<div
				v-for="item in virtualChunks"
				:key="String(item.key)"
				class="absolute top-0 left-0 w-full"
				:style="{
					height: `${item.size}px`,
					transform: `translate3d(0, ${item.start - scrollMargin}px, 0)`,
					contain: 'layout size',
				}"
			>
				<div
					v-if="item.chunk?.header !== null && item.chunk !== undefined"
					class="w-full flex items-center font-semibold text-toned text-lg"
					:style="{ height: `${HEADER_ROW_HEIGHT}px` }"
				>
					{{ item.chunk.header.label }}
				</div>
				<template v-if="item.chunk !== undefined">
					<template v-for="tile in tilesForChunk(item.chunk)" :key="tile.photo.id">
						<PhotoListItemVirtual
							v-if="mode === 'list'"
							:photo="tile.photo"
							:box="relativeBox(tile.box, item.chunk)"
							:album-id="albumId"
							:is-selected="props.selectedPhotos.includes(tile.photo.id)"
							:is-cover-id="albumStore.coverId === tile.photo.id"
							:is-header-id="albumStore.modelAlbum?.header_id === tile.photo.id"
							:aria-posinset="tile.index + 1"
							:aria-setsize="layout.boxes.length"
							@clicked="(e) => maySelect(tile.photo.id, e as MouseEvent)"
							@contexted="(e) => menuOpen(tile.photo.id, e)"
						/>
						<PhotoThumbVirtual
							v-else
							:photo="tile.photo"
							:box="relativeBox(tile.box, item.chunk)"
							:album-id="albumId"
							:is-selected="props.selectedPhotos.includes(tile.photo.id)"
							:is-cover-id="albumStore.coverId === tile.photo.id"
							:is-header-id="albumStore.modelAlbum?.header_id === tile.photo.id"
							:is-buyable="isBuyable"
							:aria-posinset="tile.index + 1"
							:aria-setsize="layout.boxes.length"
							@click="(e: MouseEvent) => maySelect(tile.photo.id, e)"
							@contextmenu="(e: MouseEvent) => menuOpen(tile.photo.id, e)"
							@toggle-buy-me="emits('toggleBuyMe', tile.photo.id)"
						/>
					</template>
				</template>
			</div>
		</div>
	</div>
</template>
<script setup lang="ts">
/**
 * Unified virtualized photo grid — covers all five layout modes
 * (`justified`/`square`/`masonry`/`grid`/`list`) via one shared mechanism
 * (FR-065-09, Decision Cards Q-065-01/Q-065-04): every tile's exact box is
 * computed analytically up front (`computePhotoLayout()`, calling the
 * matching WASM primitive directly, zero DOM dependency — `list` needs no
 * WASM call at all), then grouped into fixed-tile-count "chunks"
 * (`buildPhotoChunks()`) fed to `useWindowVirtualizer` as its row unit. A
 * chunk is a pure virtualization bookkeeping unit, not a layout concept —
 * this is what lets a row-indexed virtualizer drive `masonry`/`grid`'s
 * genuinely non-row-uniform geometry.
 *
 * `containerWidth` is read from this component's own outer wrapper via
 * `useElementSize` — a single, cheap, one-time-per-resize measurement of
 * one bounded-height element, not the (potentially enormous) inner
 * virtualized content div, so it doesn't hit the ResizeObserver
 * stall `albumTileWidth.ts` documents for a different, much taller element.
 *
 * The `useWindowVirtualizer`/two-layer sticky-header/`scrollMargin` wiring
 * pattern is lifted directly from `AlbumThumbGridVirtual.vue` — the photo
 * section shares the same page/window scroll `AlbumPanel.vue`'s other
 * content does.
 */
import { computed, onMounted, ref, watch } from "vue";
import { useElementBounding, useElementSize } from "@vueuse/core";
import { useWindowVirtualizer } from "@tanstack/vue-virtual";
import { useAlbumStore } from "@/stores/AlbumState";
import { usePhotosStore } from "@/stores/PhotosState";
import { useLayoutStore } from "@/stores/LayoutState";
import { useCatalogStore } from "@/stores/CatalogState";
import { isTouchDevice, ctrlKeyState, metaKeyState, shiftKeyState } from "@/utils/keybindings-utils";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { storeToRefs } from "pinia";
import { initLayouts } from "@/v8/layouts/wasmLayouts";
import { HEADER_ROW_HEIGHT } from "@/v8/composables/album/virtualAlbumRows";
import {
	buildPhotoChunks,
	computeVisiblePhotoLayout,
	type PhotoLayoutMode,
	type PhotoBox,
	type PhotoChunk,
} from "@/v8/composables/photo/analyticPhotoLayout";
import { resolveCssLengthPx } from "@/v8/utils/resolveCssLengthPx";
import type { AlbumBucketBoundary } from "@/v8/utils/albumBucketBoundaries";
import PhotoThumbVirtual from "@/v8/components/gallery/albumModule/Virtualized/PhotoThumbVirtual.vue";
import PhotoListItemVirtual from "@/v8/components/gallery/albumModule/Virtualized/PhotoListItemVirtual.vue";

const props = defineProps<{
	selectedPhotos: string[];
}>();

const emits = defineEmits<{
	clicked: [id: string, event: MouseEvent];
	selected: [id: string, event: MouseEvent];
	contexted: [id: string, event: MouseEvent];
	toggleBuyMe: [id: string];
}>();

const albumStore = useAlbumStore();
const photosStore = usePhotosStore();
const layoutStore = useLayoutStore();
const catalogStore = useCatalogStore();
const togglableStore = useTogglablesStateStore();
const { is_touch_select_mode } = storeToRefs(togglableStore);

const albumId = computed(() => albumStore.albumId ?? "");
const isBuyable = computed(() => catalogStore.catalog?.album_purchasable !== undefined && catalogStore.catalog.album_purchasable !== null);

const containerRef = ref<HTMLElement>();
const { width: elementWidth } = useElementSize(containerRef);
const ASSUMED_SCROLLBAR_WIDTH = 15;
const containerWidth = computed(() => Math.max(0, elementWidth.value - (isTouchDevice() ? 0 : ASSUMED_SCROLLBAR_WIDTH)));

const ready = ref(false);
onMounted(async () => {
	await initLayouts();
	ready.value = true;
});

const mode = computed<PhotoLayoutMode>(() => layoutStore.layout as PhotoLayoutMode);

const targetAndGap = computed<{ target: number; gap: number }>(() => {
	const config = layoutStore.config;
	if (config === undefined) {
		return { target: 200, gap: 12 };
	}
	switch (mode.value) {
		case "justified":
			return { target: config.photo_layout_justified_row_height, gap: config.photo_layout_gap };
		case "square":
			return { target: config.photo_layout_square_column_width, gap: config.photo_layout_gap };
		case "masonry":
			return { target: config.photo_layout_masonry_column_width, gap: config.photo_layout_gap };
		case "grid":
			return { target: config.photo_layout_grid_column_width, gap: config.photo_layout_gap };
		case "list":
		default:
			return { target: 0, gap: 0 };
	}
});

// Rating-filter-aware boundary recompute (FR-065-19, discovered necessity —
// no Feature 063 precedent, albums have no equivalent client-side filter) —
// shared with `dragAndSelect.ts`'s `getPhotoBoxesV3()` via
// `computeVisiblePhotoLayout()` so this logic exists in exactly one place.
const ratingFilterActive = computed(() => photosStore.photoRatingFilter !== null);
const filteredPhotoIds = computed(() => (ratingFilterActive.value ? new Set(photosStore.filteredPhotos.map((p) => p.id)) : null));

const layoutResult = computed(() => {
	// `containerWidth` starts at 0 until `useElementSize`'s ResizeObserver
	// fires its first measurement, independently of `ready`. Computing the
	// real layout against a 0px width isn't just a cosmetic 0-size flash —
	// the WASM primitives can return fewer boxes than input tiles for a
	// degenerate width, leaving holes in `computePhotoLayout()`'s `boxes`
	// array that later crash the render (`relativeBox()` reading `.top` off
	// an undefined box). Stay in the same empty state as `!ready` until a
	// real width is known.
	if (!ready.value || containerWidth.value <= 0) {
		return {
			positioned: [] as { photo: App.Http.Resources.Models.PhotoResource; box: PhotoBox }[],
			totalHeight: 0,
			headerTops: [] as { top: number; label: string; bucketId: string }[],
			boundaries: [] as AlbumBucketBoundary[],
		};
	}
	return computeVisiblePhotoLayout(
		mode.value,
		photosStore.photos,
		albumStore.photoRatiosV3,
		albumStore.photoBoundariesV3,
		albumStore.photoBucketableV3,
		ratingFilterActive.value,
		filteredPhotoIds.value,
		containerWidth.value,
		targetAndGap.value.target,
		targetAndGap.value.gap,
	);
});

// Headers still render when a rating filter is active (FR-065-19) —
// `computeVisiblePhotoLayout()`'s own returned `boundaries` already reflect
// the filtered counts (and drop an entirely-emptied bucket entirely).
const showHeaders = computed(() => albumStore.photoBucketableV3);

const layout = computed(() => ({
	boxes: layoutResult.value.positioned.map((p) => p.box),
	totalHeight: layoutResult.value.totalHeight,
	headerTops: layoutResult.value.headerTops,
}));

const chunks = computed<PhotoChunk[]>(() =>
	buildPhotoChunks(layout.value.boxes, layoutResult.value.boundaries, showHeaders.value, layout.value.headerTops),
);

const { top: viewportTop } = useElementBounding(containerRef);
const scrollMargin = computed(() => viewportTop.value + window.scrollY);

const uiHeaderHeightPx = resolveCssLengthPx("var(--ui-header-height)");

const virtualizer = useWindowVirtualizer(
	computed(() => ({
		count: chunks.value.length,
		estimateSize: (index: number) => chunks.value[index]?.size ?? 0,
		overscan: 2,
		getItemKey: (index: number) => chunks.value[index]?.key ?? index,
		scrollMargin: scrollMargin.value,
	})),
);

const virtualChunks = computed(() => virtualizer.value.getVirtualItems().map((item) => ({ ...item, chunk: chunks.value[item.index] })));

// Same rationale as AlbumThumbGridVirtual.vue's own watcher: chunk keys can
// survive a reshape (e.g. containerWidth/mode change reflows every box
// without necessarily changing chunk 0's key) while their true size
// changes — force a fresh measurement whenever the underlying layout
// changes.
watch([chunks, scrollMargin], () => virtualizer.value.measure());

type PositionedTile = { photo: App.Http.Resources.Models.PhotoResource; box: PhotoBox; index: number };

function tilesForChunk(chunk: PhotoChunk): PositionedTile[] {
	const result: PositionedTile[] = [];
	for (let i = 0; i < chunk.tileCount; i++) {
		const index = chunk.tileStart + i;
		const entry = layoutResult.value.positioned[index];
		if (entry !== undefined) {
			result.push({ photo: entry.photo, box: entry.box, index });
		}
	}
	return result;
}

function relativeBox(box: PhotoBox, chunk: PhotoChunk): PhotoBox {
	return { top: box.top - chunk.top, left: box.left, width: box.width, height: box.height };
}

function maySelect(id: string, e: MouseEvent) {
	if (is_touch_select_mode.value || ctrlKeyState.value || metaKeyState.value || shiftKeyState.value) {
		emits("selected", id, e);
		return;
	}
	emits("clicked", id, e);
}

function menuOpen(id: string, e: MouseEvent) {
	emits("contexted", id, e);
}

// Sticky pinned bucket label — identical mechanism to
// AlbumThumbGridVirtual.vue's own `headerTops`/`activeHeaderLabel` pair,
// generalized here from `rowsResult.rows`/`rowHeights` to the chunk-based
// `layout.headerTops` this feature computes instead.
const activeHeaderLabel = computed<string | null>(() => {
	const offset = (virtualizer.value.scrollOffset ?? 0) - scrollMargin.value;
	const tops = layout.value.headerTops;
	let current: { top: number; label: string } | null = null;
	for (const h of tops) {
		if (h.top - uiHeaderHeightPx <= offset) {
			current = h;
		} else {
			break;
		}
	}
	if (current === null || offset < current.top + HEADER_ROW_HEIGHT - uiHeaderHeightPx) {
		return null;
	}
	return current.label;
});
</script>
