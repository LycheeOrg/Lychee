<template>
	<div class="w-full px-4 sm:px-6">
		<div ref="containerRef" data-album-grid-root role="list" class="relative w-full" :style="{ height: `${totalSize}px` }">
			<!-- Sticky pinned header — see AlbumThumbGridVirtual.vue for the identical mechanism. -->
			<div
				v-if="activeHeaderLabel !== null"
				class="sticky top-(--ui-header-height) z-10 pointer-events-none"
				:style="{ height: `${HEADER_ROW_HEIGHT}px`, marginBottom: `-${HEADER_ROW_HEIGHT}px` }"
			>
				<div class="w-full h-full flex items-center font-semibold text-toned text-lg bg-default/95 backdrop-blur">
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
				<div v-if="item.row?.type === 'header'" class="w-full h-full flex items-center font-semibold text-toned text-lg">
					{{ item.row.label }}
				</div>
				<template v-else-if="item.row?.type === 'tiles'">
					<AlbumListItemVirtual
						v-for="(tile, idx) in tilesForRow(item.row)"
						:key="tile.id"
						role="listitem"
						:aria-posinset="item.row.startIndex + idx + 1"
						:aria-setsize="visibleAlbums.tiles.length"
						:album="tile"
						:is-selected="props.selectedAlbums.includes(tile.id)"
						@clicked="propagateClicked"
						@selected="(e: MouseEvent, id: string) => emits('selected', e, id)"
						@contexted="propagateContexted"
					/>
				</template>
			</div>
		</div>
	</div>
</template>
<script setup lang="ts">
/**
 * List-view virtualizer for the flag-on subalbum-children path — same
 * window-scroll virtualizer wiring as
 * AlbumThumbGridVirtual.vue (see that file's own comment for why
 * `useWindowVirtualizer`/`scrollMargin` rather than a nested scroll
 * container), but a single item per row (itemsPerRow=1) and a fixed row
 * height instead of the aspect-ratio-derived tile height:
 * buildVirtualAlbumRows() is reused unchanged by passing
 * tileWidth=LIST_ROW_HEIGHT, aspectRatioNumber=1 (so its
 * tileHeight = tileWidth/aspectRatioNumber collapses to the constant),
 * gap=0 (rows stack flush, matching AlbumListView.vue's own gap-0 wrapper).
 */
import { computed, ref } from "vue";
import { storeToRefs } from "pinia";
import { useElementBounding } from "@vueuse/core";
import { useWindowVirtualizer } from "@tanstack/vue-virtual";
import { useAlbumStore } from "@/stores/AlbumState";
import { useAlbumsStore } from "@/stores/AlbumsState";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { usePropagateAlbumEvents } from "@/composables/album/propagateEvents";
import { buildVirtualAlbumRows, HEADER_ROW_HEIGHT, LIST_ROW_HEIGHT, type VirtualTileRow } from "@/v8/composables/album/virtualAlbumRows";
import { filterBucketedTiles } from "@/v8/utils/albumBucketBoundaries";
import { resolveCssLengthPx } from "@/v8/utils/resolveCssLengthPx";
import AlbumListItemVirtual from "@/v8/components/gallery/albumModule/Virtualized/AlbumListItemVirtual.vue";
import type { AdaptedAlbumTile } from "@/v8/utils/adaptAlbumChildTile";

const props = defineProps<{
	selectedAlbums: string[];
}>();

const emits = defineEmits<{
	clicked: [event: MouseEvent, id: string];
	selected: [event: MouseEvent, id: string];
	contexted: [event: MouseEvent, id: string];
}>();

const { propagateClicked, propagateContexted } = usePropagateAlbumEvents(emits);

const albumStore = useAlbumStore();
const albumsStore = useAlbumsStore();
const lycheeStore = useLycheeStateStore();
const { are_nsfw_visible } = storeToRefs(lycheeStore);

const containerRef = ref<HTMLElement>();

const { top: viewportTop } = useElementBounding(containerRef);
const scrollMargin = computed(() => viewportTop.value + window.scrollY);

// See AlbumThumbGridVirtual.vue's identical constant for why this is needed:
// the real header row is hidden behind the app's own sticky top bar, not
// behind the true viewport top (y=0).
const uiHeaderHeightPx = resolveCssLengthPx("var(--ui-header-height)");

const showHeaders = computed(() => albumStore.bucketableV3);
const boundaries = computed(() =>
	albumStore.boundariesV3 !== null ? albumStore.boundariesV3 : [{ bucketId: "all", label: "", startIndex: 0, count: albumsStore.albums.length }],
);

// NSFW-hidden tiles are dropped before row-chunking (not per-tile in the
// template) — filtering afterwards would leave a blank row where a hidden
// tile's own row used to be, since row boundaries are fixed against the
// original counts.
const visibleAlbums = computed(() => filterBucketedTiles(albumsStore.albums, boundaries.value, (tile) => !tile.is_nsfw || are_nsfw_visible.value));

const rowsResult = computed(() =>
	buildVirtualAlbumRows(
		visibleAlbums.value.tiles.map((a) => a.id),
		visibleAlbums.value.boundaries,
		showHeaders.value,
		1,
		LIST_ROW_HEIGHT,
		1,
		0,
	),
);

const virtualizer = useWindowVirtualizer(
	computed(() => ({
		count: rowsResult.value.rows.length,
		estimateSize: (index: number) => rowsResult.value.rowHeights[index] ?? 0,
		overscan: 8,
		getItemKey: (index: number) => rowsResult.value.rows[index]?.key ?? index,
		scrollMargin: scrollMargin.value,
	})),
);

const totalSize = computed(() => virtualizer.value.getTotalSize());
const virtualRows = computed(() => virtualizer.value.getVirtualItems().map((item) => ({ ...item, row: rowsResult.value.rows[item.index] })));

function tilesForRow(row: VirtualTileRow): AdaptedAlbumTile[] {
	return visibleAlbums.value.tiles.slice(row.startIndex, row.startIndex + row.count) as AdaptedAlbumTile[];
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
	// See AlbumThumbGridVirtual.vue's identical comment: a header becomes
	// "current" once it reaches the bottom edge of the sticky top bar
	// (uiHeaderHeightPx), not the true viewport top (0).
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
