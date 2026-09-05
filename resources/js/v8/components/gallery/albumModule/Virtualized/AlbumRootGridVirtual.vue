<template>
	<div class="w-full px-4 sm:px-6">
		<div
			ref="containerRef"
			data-album-grid-root
			:data-album-grid-scope="props.scope"
			role="list"
			class="relative w-full"
			:style="{ height: `${totalSize}px` }"
		>
			<!-- Sticky pinned header — see AlbumThumbGridVirtual.vue for the identical mechanism (this
			     component is its root-scope fork, 2026-09-02 addendum). -->
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
				v-for="item in virtualRows"
				:key="String(item.key)"
				class="absolute top-0 left-0 w-full"
				:style="{
					height: `${item.size}px`,
					transform: `translate3d(0, ${item.start - scrollMargin}px, 0)`,
					contain: 'layout size',
				}"
			>
				<div
					v-if="item.row?.type === 'header'"
					class="w-full flex items-center font-semibold text-toned text-lg"
					:style="{ height: `${rowsResult.rowContentHeights[item.index]}px` }"
				>
					{{ item.row.label }}
				</div>
				<div
					v-else-if="item.row?.type === 'tiles'"
					class="w-full flex flex-row"
					:style="{ height: `${rowsResult.rowContentHeights[item.index]}px`, gap: `${gap}px` }"
				>
					<div
						v-for="(tile, idx) in tilesForRow(item.row)"
						:key="tile.id"
						role="listitem"
						:aria-posinset="item.row.startIndex + idx + 1"
						:aria-setsize="visibleTiles.tiles.length"
						:style="{ width: `${tileWidth}px`, height: '100%', flexShrink: 0 }"
					>
						<AlbumThumbVirtual
							:album="tile"
							:cover_id="null"
							:is-selected="props.selectedAlbums.includes(tile.id)"
							@click="propagateClicked($event, tile.id)"
							@touch-select="(e: MouseEvent) => emits('selected', e, tile.id)"
							@contextmenu="propagateContexted($event, tile.id)"
						/>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
<script setup lang="ts">
/**
 * Grid virtualizer for the flag-on root-gallery own/shared albums path
 * (2026-09-02 root-SoA addendum — "the point of the extension is to support
 * Struct-of-Array on the root gallery page"). Forked from
 * `AlbumThumbGridVirtual.vue` rather than making that component
 * take its data via props: this repo's established convention is to fork a
 * shared component rather than branch a flag/mode into it, and the two
 * components differ in exactly which store fields they read (root's own vs.
 * shared scope here, vs. the currently-browsed album's children there) —
 * genuinely different data sources, not a rendering-strategy difference.
 * All the *pure* pieces are reused unchanged: `buildVirtualAlbumRows()`,
 * `computeBucketBoundaries()` (called by the store, not here),
 * `useAlbumTileWidth()`, `AlbumThumbVirtual.vue` itself.
 *
 * `scope` selects which of `AlbumsState.ts`'s own/shared bucket-tier state
 * to read — for `shared` scope, tier 1's `labels[]` are already
 * server-resolved owner display names (`AlbumRootController::
 * querySharedBuckets()`), so the exact same sticky-header mechanism that
 * shows a date/title-prefix label for `own` scope shows "owner name" for
 * `shared` scope with zero special-casing needed here.
 */
import { computed, ref, watch } from "vue";
import { storeToRefs } from "pinia";
import { useElementBounding } from "@vueuse/core";
import { useWindowVirtualizer } from "@tanstack/vue-virtual";
import { useAlbumsStore } from "@/stores/AlbumsState";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { usePropagateAlbumEvents } from "@/composables/album/propagateEvents";
import { useAlbumTileWidth } from "@/v8/composables/album/albumTileWidth";
import { buildVirtualAlbumRows, HEADER_ROW_HEIGHT, type VirtualTileRow } from "@/v8/composables/album/virtualAlbumRows";
import { filterBucketedTiles } from "@/v8/utils/albumBucketBoundaries";
import { aspectRatioCssToNumber } from "@/v8/utils/aspectRatioNumber";
import { resolveCssLengthPx } from "@/v8/utils/resolveCssLengthPx";
import AlbumThumbVirtual from "@/v8/components/gallery/albumModule/Virtualized/AlbumThumbVirtual.vue";
import type { AdaptedAlbumTile } from "@/v8/utils/adaptAlbumChildTile";

const props = defineProps<{
	scope: App.Enum.AlbumListingScope;
	selectedAlbums: string[];
}>();

const emits = defineEmits<{
	clicked: [event: MouseEvent, id: string];
	selected: [event: MouseEvent, id: string];
	contexted: [event: MouseEvent, id: string];
}>();

const { propagateClicked, propagateContexted } = usePropagateAlbumEvents(emits);

const albumsStore = useAlbumsStore();
const lycheeStore = useLycheeStateStore();
const { are_nsfw_visible } = storeToRefs(lycheeStore);

const containerRef = ref<HTMLElement>();
const { tileWidth, itemsPerRow, gap } = useAlbumTileWidth();

const { top: viewportTop } = useElementBounding(containerRef);
const scrollMargin = computed(() => viewportTop.value + window.scrollY);

const uiHeaderHeightPx = resolveCssLengthPx("var(--ui-header-height)");

const aspectRatioNumber = computed(() => aspectRatioCssToNumber(albumsStore.rootConfig?.album_thumb_css_aspect_ratio));

const tiles = computed<AdaptedAlbumTile[]>(() => (props.scope === "own" ? (albumsStore.albums as AdaptedAlbumTile[]) : albumsStore.sharedAlbumsV3));
const showHeaders = computed(() => (props.scope === "own" ? albumsStore.ownBucketableV3 : albumsStore.sharedBucketableV3));
const boundaries = computed(() => {
	const b = props.scope === "own" ? albumsStore.ownBoundariesV3 : albumsStore.sharedBoundariesV3;
	return b !== null ? b : [{ bucketId: "all", label: "", startIndex: 0, count: tiles.value.length }];
});

// NSFW-hidden tiles are dropped before row-chunking (not per-tile in the
// template) — filtering afterwards would leave a gap at the end of whichever
// row lost a tile, since row boundaries are fixed against the original counts.
const visibleTiles = computed(() => filterBucketedTiles(tiles.value, boundaries.value, (tile) => !tile.is_nsfw || are_nsfw_visible.value));

const rowsResult = computed(() =>
	buildVirtualAlbumRows(
		visibleTiles.value.tiles.map((a) => a.id),
		visibleTiles.value.boundaries,
		showHeaders.value,
		itemsPerRow.value,
		tileWidth.value,
		aspectRatioNumber.value,
		gap.value,
	),
);

const virtualizer = useWindowVirtualizer(
	computed(() => ({
		count: rowsResult.value.rows.length,
		estimateSize: (index: number) => rowsResult.value.rowHeights[index] ?? 0,
		overscan: 4,
		getItemKey: (index: number) => rowsResult.value.rows[index]?.key ?? index,
		scrollMargin: scrollMargin.value,
	})),
);

const totalSize = computed(() => virtualizer.value.getTotalSize());
const virtualRows = computed(() => virtualizer.value.getVirtualItems().map((item) => ({ ...item, row: rowsResult.value.rows[item.index] })));

// See AlbumThumbGridVirtual.vue's identical comment for why rowsResult
// itself (not just its geometry inputs) is watched.
watch([rowsResult, scrollMargin], () => virtualizer.value.measure());

function tilesForRow(row: VirtualTileRow): AdaptedAlbumTile[] {
	return visibleTiles.value.tiles.slice(row.startIndex, row.startIndex + row.count);
}

const headerTops = computed(() => {
	const tops: { top: number; label: string }[] = [];
	let top = 0;
	const rows = rowsResult.value.rows;
	const heights = rowsResult.value.rowHeights;
	for (let i = 0; i < rows.length; i++) {
		const row = rows[i];
		if (row.type === "header") {
			tops.push({ top, label: row.label });
		}
		top += heights[i];
	}
	return tops;
});

const activeHeaderLabel = computed<string | null>(() => {
	const offset = (virtualizer.value.scrollOffset ?? 0) - scrollMargin.value;
	const tops = headerTops.value;
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
