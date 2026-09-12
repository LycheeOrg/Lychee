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
							:is-cover-id="isCoverId(tile.photo.id)"
							:is-header-id="isHeaderId(tile.photo.id)"
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
							:is-cover-id="isCoverId(tile.photo.id)"
							:is-header-id="isHeaderId(tile.photo.id)"
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
 *
 * Feature 066 (`source="timeline"`): this same component also renders the
 * v8 global Timeline, sourced from `TimelineState.ts`'s incremental,
 * bucket-windowed v3 state instead of `AlbumState.ts`'s whole-album-at-once
 * one — see every `source.value === "timeline"` branch below. The album
 * path (`source` omitted/`"album"`) is deliberately left untouched
 * wherever the two diverge, so Feature 065's proven behaviour never
 * regresses (NFR-066-06's spirit extended defensively to the v3 album path
 * too, even though that NFR is only actually about the v2 Timeline path).
 */
import { computed, nextTick, onMounted, ref, watch } from "vue";
import { useElementBounding, useElementSize } from "@vueuse/core";
import { useWindowVirtualizer } from "@tanstack/vue-virtual";
import { useRoute } from "vue-router";
import { useAlbumStore } from "@/stores/AlbumState";
import { usePhotosStore } from "@/stores/PhotosState";
import { useTimelineStore } from "@/stores/TimelineState";
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
	computeTimelinePhotoLayout,
	buildTimelineChunks,
	type PhotoLayoutMode,
	type PhotoBox,
	type PhotoChunk,
	type TimelineChunk,
	type TimelineLayoutCacheEntry,
} from "@/v8/composables/photo/analyticPhotoLayout";
import { resolveCssLengthPx } from "@/v8/utils/resolveCssLengthPx";
import PhotoThumbVirtual from "@/v8/components/gallery/albumModule/Virtualized/PhotoThumbVirtual.vue";
import PhotoListItemVirtual from "@/v8/components/gallery/albumModule/Virtualized/PhotoListItemVirtual.vue";

const props = defineProps<{
	selectedPhotos: string[];
	/**
	 * Feature 066 — which incremental store this grid renders from.
	 * Defaults to `"album"` (Feature 065's own `AlbumState.ts`-sourced
	 * path, unchanged). `"timeline"` sources `TimelineState.ts`'s v3
	 * (bucket-windowed, placeholder-aware) state instead — the two paths
	 * can't share one flat, whole-scope, `photosStore.photos`-indexed
	 * layout pass the way album mode does, since Timeline's tiles/ratios
	 * are only ever partially loaded (FR-066-12).
	 */
	source?: "album" | "timeline";
}>();

const emits = defineEmits<{
	clicked: [id: string, event: MouseEvent];
	selected: [id: string, event: MouseEvent];
	contexted: [id: string, event: MouseEvent];
	toggleBuyMe: [id: string];
}>();

const source = computed(() => props.source ?? "album");

const route = useRoute();
const albumStore = useAlbumStore();
const timelineStore = useTimelineStore();
const photosStore = usePhotosStore();
const layoutStore = useLayoutStore();
const catalogStore = useCatalogStore();
const togglableStore = useTogglablesStateStore();
const { is_touch_select_mode } = storeToRefs(togglableStore);

const albumId = computed(() => (source.value === "timeline" ? "timeline" : (albumStore.albumId ?? "")));
const isBuyable = computed(() => catalogStore.catalog?.album_purchasable !== undefined && catalogStore.catalog.album_purchasable !== null);

// Timeline has no "cover"/"header" photo concept (those are per-Album
// fields) — always false on that path, regardless of what `albumStore`
// happens to still hold from a previous album visit.
function isCoverId(photoId: string): boolean {
	return source.value !== "timeline" && albumStore.coverId === photoId;
}
function isHeaderId(photoId: string): boolean {
	return source.value !== "timeline" && albumStore.modelAlbum?.header_id === photoId;
}

const containerRef = ref<HTMLElement>();
const { width: elementWidth } = useElementSize(containerRef);
const ASSUMED_SCROLLBAR_WIDTH = 15;
const containerWidth = computed(() => Math.max(0, elementWidth.value - (isTouchDevice() ? 0 : ASSUMED_SCROLLBAR_WIDTH)));

const ready = ref(false);

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
// Album path only — Timeline has no rating-filter control mounted anywhere
// in its own header/panel (`TimelineHeader.vue` was checked), so applying
// this to a partially-loaded, hole-having `tilesV3` would add real
// complexity for a control that can't currently be triggered from this
// route.
const ratingFilterActive = computed(() => source.value === "album" && photosStore.photoRatingFilter !== null);
const filteredPhotoIds = computed(() => (ratingFilterActive.value ? new Set(photosStore.filteredPhotos.map((p) => p.id)) : null));

// Also gated on the album's own `is_photo_timeline_enabled` display toggle —
// mirrors the flag-off `AlbumPanel.vue`'s identical
// `albumStore.config.is_photo_timeline_enabled` prop passed to
// `PhotoThumbPanel`, which this SoA path had been missing (same class of bug
// as the album-listing one, see `AlbumThumbGridVirtual.vue`). Fed into
// `computeVisiblePhotoLayout()` below as its own `bucketable` param (not just
// read separately for the header label itself) — otherwise the underlying
// row-packing would still chunk photos into separate per-bucket runs (with
// reserved header space) even with the label text suppressed, instead of one
// continuous flow. Timeline has no such per-album toggle — grouping by date
// is the entire point of the view, gated only on `bucketableV3` itself.
const showHeaders = computed(() => {
	if (source.value === "timeline") {
		return timelineStore.bucketableV3;
	}
	return albumStore.photoBucketableV3 && (albumStore.config?.is_photo_timeline_enabled ?? false);
});

const layoutResult = computed(() => {
	// `containerWidth` starts at 0 until `useElementSize`'s ResizeObserver
	// fires its first measurement, independently of `ready`. Computing the
	// real layout against a 0px width isn't just a cosmetic 0-size flash —
	// the WASM primitives can return fewer boxes than input tiles for a
	// degenerate width, leaving holes in `computePhotoLayout()`'s `boxes`
	// array that later crash the render (`relativeBox()` reading `.top` off
	// an undefined box). Stay in the same empty state as `!ready` until a
	// real width is known.
	if (source.value !== "album" || !ready.value || containerWidth.value <= 0) {
		return {
			positioned: [] as { photo: App.Http.Resources.Models.PhotoResource; box: PhotoBox }[],
			totalHeight: 0,
			headerTops: [] as { top: number; label: string; bucketId: string }[],
			boundaries: [] as import("@/v8/utils/albumBucketBoundaries").AlbumBucketBoundary[],
		};
	}
	return computeVisiblePhotoLayout(
		mode.value,
		photosStore.photos,
		albumStore.photoRatiosV3,
		albumStore.photoBoundariesV3,
		showHeaders.value,
		ratingFilterActive.value,
		filteredPhotoIds.value,
		containerWidth.value,
		targetAndGap.value.target,
		targetAndGap.value.gap,
	);
});

// Feature 066 — per-bucket WASM layout cache (T-066-24). A plain
// (non-reactive) Map, not Pinia state: it's a pure memoization side-channel
// for `timelinePixelLayout` below, never read by a template directly, so
// giving it Vue's Map-proxy reactivity tracking would be pure overhead.
// Lives for this component instance's lifetime — dropped implicitly on
// unmount (navigating away from Timeline).
const timelineLayoutCache = new Map<string, TimelineLayoutCacheEntry>();

const timelinePixelLayout = computed(() => {
	if (source.value !== "timeline" || !ready.value || containerWidth.value <= 0) {
		return {
			boxes: [] as (PhotoBox | undefined)[],
			totalHeight: 0,
			headerTops: [] as { top: number; label: string; bucketId: string }[],
			buckets: [] as ReturnType<typeof computeTimelinePhotoLayout>["buckets"],
		};
	}
	const loadedBucketIds = new Set(Object.keys(timelineStore.loadedBucketsV3).filter((id) => timelineStore.loadedBucketsV3[id]));
	return computeTimelinePhotoLayout(
		mode.value,
		timelineStore.boundariesV3,
		timelineStore.ratiosV3,
		loadedBucketIds,
		timelineLayoutCache,
		showHeaders.value,
		containerWidth.value,
		targetAndGap.value.target,
		targetAndGap.value.gap,
	);
});

// Headers still render when a rating filter is active (FR-065-19) —
// `computeVisiblePhotoLayout()`'s own returned `boundaries` already reflect
// the filtered counts (and drop an entirely-emptied bucket entirely).

const layout = computed(() => {
	if (source.value === "timeline") {
		return {
			boxes: timelinePixelLayout.value.boxes,
			totalHeight: timelinePixelLayout.value.totalHeight,
			headerTops: timelinePixelLayout.value.headerTops,
		};
	}
	return {
		boxes: layoutResult.value.positioned.map((p) => p.box) as (PhotoBox | undefined)[],
		totalHeight: layoutResult.value.totalHeight,
		headerTops: layoutResult.value.headerTops,
	};
});

const chunks = computed<(PhotoChunk | TimelineChunk)[]>(() => {
	if (source.value === "timeline") {
		return buildTimelineChunks(timelinePixelLayout.value.boxes, timelinePixelLayout.value.buckets, showHeaders.value);
	}
	return buildPhotoChunks(layout.value.boxes as PhotoBox[], layoutResult.value.boundaries, showHeaders.value, layout.value.headerTops);
});

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

/** Sum of `chunk.size` for every chunk entirely above `offset` (i.e. its own bottom is at/before `offset`) — the cumulative height of "already scrolled past" content. */
function cumulativeChunkHeightAbove(chunkList: (PhotoChunk | TimelineChunk)[], offset: number): number {
	let total = 0;
	for (const c of chunkList) {
		if (c.top + c.size <= offset) {
			total = c.top + c.size;
		} else {
			break;
		}
	}
	return total;
}

// Reflow watcher — chunk keys can survive a reshape (e.g.
// containerWidth/mode change reflows every box without necessarily changing
// chunk 0's key) while their true size changes — force a fresh measurement
// whenever the underlying layout changes (same rationale as
// AlbumThumbGridVirtual.vue's own watcher).
//
// For the Timeline path specifically (T-066-29): `.measure()` alone is NOT
// enough to avoid a visible jump when a bucket strictly above the viewport
// transitions placeholder→real. TanStack's own built-in scroll-offset
// compensation (`shouldAdjustScrollPositionOnItemSizeChange`) only ever
// engages through its DOM-measured `resizeItem()`/`measureElement()` path
// (confirmed against the installed `@tanstack/virtual-core@3.17.8`'s own
// source) — this grid's sizes are always analytically known upfront
// (`estimateSize()`), so that built-in mechanism never fires here. Instead:
// compare how much content is "fully above" the current, unmoved, scroll
// offset before vs. after the chunk list changes (`cumulativeChunkHeightAbove()`
// against the OLD and NEW chunk tops, same offset) — any transition
// strictly above the fold shows up as a non-zero delta, compensated by an
// equal `scrollToOffset()` nudge so already-scrolled-past content never
// visibly moves (NFR-066-05/S-066-15). A no-op for the album path (`oldAbove`
// stays `null`).
//
// UNVERIFIED — no browser/dev environment available this authoring session
// (`[[feedback_no_mariadb_mysql_access]]`); this is a from-first-principles
// implementation against the installed TanStack version's actual source,
// not something that could be scroll-tested. Flagged pending manual
// verification (T-066-29).
watch([chunks, scrollMargin], (_new, [oldChunks]) => {
	const offset = (virtualizer.value.scrollOffset ?? 0) - scrollMargin.value;
	const oldAbove = source.value === "timeline" ? cumulativeChunkHeightAbove(oldChunks, offset) : null;

	virtualizer.value.measure();

	if (oldAbove !== null) {
		const newAbove = cumulativeChunkHeightAbove(chunks.value, offset);
		const delta = newAbove - oldAbove;
		if (delta !== 0) {
			virtualizer.value.scrollToOffset((virtualizer.value.scrollOffset ?? 0) + delta);
		}
	}
});

type PositionedTile = { photo: App.Http.Resources.Models.PhotoResource; box: PhotoBox; index: number };

function tilesForChunk(chunk: PhotoChunk | TimelineChunk): PositionedTile[] {
	if (source.value === "timeline") {
		const result: PositionedTile[] = [];
		for (let i = 0; i < chunk.tileCount; i++) {
			const index = chunk.tileStart + i;
			const photo = timelineStore.tilesV3[index];
			const box = timelinePixelLayout.value.boxes[index];
			if (photo !== undefined && box !== undefined) {
				result.push({ photo, box, index });
			}
		}
		return result;
	}
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

function relativeBox(box: PhotoBox, chunk: PhotoChunk | TimelineChunk): PhotoBox {
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
// `layout.headerTops` this feature computes instead. Already source-generic
// (`layout` itself branches above), so no Timeline-specific change needed.
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

// --- Feature 066: Timeline-only scroll-proximity prefetch (T-066-28) + deep-link resolution (T-066-30/31) ---

/** How many buckets beyond the currently-visible range to eagerly fetch — a cheap, fixed overscan-like margin (mirrors the virtualizer's own `overscan: 2` chunk margin, one level up at bucket granularity). */
const BUCKET_PREFETCH_MARGIN = 1;

const visibleBucketIndices = computed<number[]>(() => {
	if (source.value !== "timeline") {
		return [];
	}
	const items = virtualizer.value.getVirtualItems();
	if (items.length === 0) {
		return [];
	}
	const buckets = timelinePixelLayout.value.buckets;
	const bucketIndexById = new Map(buckets.map((b, i) => [b.bucketId, i]));
	const indices = new Set<number>();
	for (const item of items) {
		const chunk = chunks.value[item.index] as TimelineChunk | undefined;
		if (chunk === undefined) {
			continue;
		}
		const idx = bucketIndexById.get(chunk.bucketId);
		if (idx !== undefined) {
			indices.add(idx);
		}
	}
	return [...indices];
});

/** Visible buckets ± `BUCKET_PREFETCH_MARGIN` — recomputed reactively as the user scrolls (S-066-14). */
const prefetchBucketIds = computed<string[]>(() => {
	if (source.value !== "timeline" || visibleBucketIndices.value.length === 0) {
		return [];
	}
	const buckets = timelinePixelLayout.value.buckets;
	const minIdx = Math.max(0, Math.min(...visibleBucketIndices.value) - BUCKET_PREFETCH_MARGIN);
	const maxIdx = Math.min(buckets.length - 1, Math.max(...visibleBucketIndices.value) + BUCKET_PREFETCH_MARGIN);
	const ids: string[] = [];
	for (let i = minIdx; i <= maxIdx; i++) {
		ids.push(buckets[i].bucketId);
	}
	return ids;
});

// A stable, primitive watch key (rather than watching the array itself) so
// this only fires when the actual SET of buckets-to-prefetch changes, not on
// every scroll-tick's new array reference. `requestBucketWindow()`'s own
// dedup (already-loaded/already-loading) makes every call here idempotent
// regardless, but this avoids redundant no-op calls on every scroll pixel.
const prefetchKey = computed(() => prefetchBucketIds.value.join(","));

watch(
	prefetchKey,
	() => {
		if (prefetchBucketIds.value.length > 0) {
			void timelineStore.requestBucketWindow(prefetchBucketIds.value);
		}
	},
	{ immediate: true },
);

async function waitForContainerWidth(): Promise<void> {
	if (containerWidth.value > 0) {
		return;
	}
	await new Promise<void>((resolve) => {
		const stop = watch(containerWidth, (w) => {
			if (w > 0) {
				stop();
				resolve();
			}
		});
	});
}

/**
 * Deep-link resolution (FR-066-13, T-066-30/31): resolves `/timeline/:date?/:photoId?`
 * to a target bucket, eagerly loads it (± neighbours, via `requestBucketWindow()`),
 * then jumps the virtualizer to that bucket's (possibly still-placeholder-sized,
 * self-correcting per the reflow watcher above) offset. A bare `/timeline/:date`
 * (no photo id) skips the photo→bucket resolution step entirely — `date` already
 * IS the bucket-id string. No date/photo id at all → load the first (most
 * recent) window, no scroll. Bidirectional prefetch as the user scrolls back
 * up from the landing point falls out of the scroll-proximity watcher above
 * for free — no separate mechanism needed.
 *
 * UNVERIFIED — no browser/dev environment available this authoring session;
 * landing-offset accuracy (S-066-16) needs manual verification (T-066-31).
 */
async function resolveTimelineDeepLink(): Promise<void> {
	await timelineStore.ensureBucketsV3();

	const date = typeof route.params.date === "string" && route.params.date !== "" ? route.params.date : undefined;
	const photoId = typeof route.params.photoId === "string" && route.params.photoId !== "" ? route.params.photoId : undefined;

	let targetBucketId: string | undefined = date;
	if (photoId !== undefined) {
		const resolved = await timelineStore.resolveBucketForPhotoV3(photoId);
		if (resolved !== null) {
			targetBucketId = resolved;
		}
	}

	if (targetBucketId === undefined) {
		const firstIds = timelineStore.boundariesV3.slice(0, 2).map((b) => b.bucketId);
		await timelineStore.requestBucketWindow(firstIds);
		return;
	}

	await timelineStore.requestBucketWindow(timelineStore.neighborBucketIdsV3(targetBucketId));

	await waitForContainerWidth();
	await nextTick();

	const targetLayout = timelinePixelLayout.value.buckets.find((b) => b.bucketId === targetBucketId);
	if (targetLayout !== undefined) {
		virtualizer.value.scrollToOffset(targetLayout.top + scrollMargin.value);
	}
}

onMounted(async () => {
	await initLayouts();
	ready.value = true;

	if (source.value === "timeline") {
		await resolveTimelineDeepLink();
	}
});

// Re-resolves on every `date` (bucket id) route-param change, not just on
// mount — this is what makes `TimelineDatesV3.vue`'s side-rail clicks (which
// only push a new `date` param, the component itself never unmounts) load
// and scroll to the clicked bucket (S-066-18), reusing the exact same
// resolution path a real deep-link navigation uses. Deliberately NOT watching
// `route.params.photoId` too — opening/closing the lightbox (`photoClick()`/
// `goBack()` in `Timeline.vue`) pushes a `photoId` change while keeping
// `date` the same, which must NOT re-trigger a scroll jump.
watch(
	() => route.params.date,
	() => {
		if (source.value === "timeline" && ready.value) {
			void resolveTimelineDeepLink();
		}
	},
);
</script>
