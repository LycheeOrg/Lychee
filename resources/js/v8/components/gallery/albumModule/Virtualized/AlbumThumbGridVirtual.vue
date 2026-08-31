<template>
	<div ref="containerRef" data-album-grid-root role="list" class="relative w-full" :style="{ height: `${totalSize - scrollMargin}px` }">
		<!-- Sticky pinned header (FR-062-08): only rendered once the active bucket's own
		     real header row has scrolled past the top — see activeHeaderLabel below. The
		     negative bottom margin keeps it from adding extra scroll height of its own.
		     Docks below the page's own sticky top bar (top-(--ui-header-height), same
		     variable AlbumNavPanel.vue's sticky sidebar uses) since scrolling here is the
		     page/window's own scroll, not a nested scroll container. -->
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
				contain: 'layout size paint',
			}"
		>
			<div v-if="item.row?.type === 'header'" class="w-full h-full flex items-center font-semibold text-toned text-lg">
				{{ item.row.label }}
			</div>
			<div v-else-if="item.row?.type === 'tiles'" class="w-full h-full flex flex-row" :style="{ gap: `${gap}px` }">
				<div
					v-for="(tile, idx) in tilesForRow(item.row)"
					:key="tile.id"
					role="listitem"
					:aria-posinset="item.row.startIndex + idx + 1"
					:aria-setsize="albumsStore.albums.length"
					:style="{ width: `${tileWidth}px`, height: '100%', flexShrink: 0 }"
				>
					<AlbumThumbVirtual
						v-if="!tile.is_nsfw || are_nsfw_visible"
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
</template>
<script setup lang="ts">
/**
 * Grid virtualizer for the flag-on subalbum-children path (FR-062-07,
 * Increment I5) — the `useVirtualizer` wiring pattern (reactive options via
 * `computed`, single relative spacer + absolutely-positioned translate3d
 * rows) is lifted from `AlbumNavTree.vue`'s existing tree virtualizer, the
 * only precedent for this library already in the codebase — but that
 * precedent scrolls inside its own fixed-height sidebar box, whereas
 * AlbumPanel.vue's content (AlbumHero, this grid, PhotoThumbPanel, ...) all
 * shares one ordinary page/window scroll. So this uses `useWindowVirtualizer`
 * instead of `useVirtualizer`, with `scrollMargin` (this component's own
 * document-relative top offset, kept reactive via `useElementBounding` so it
 * tracks layout shifts from content above it) telling the virtualizer where
 * in the page this grid's own row 0 starts.
 *
 * Row heights are not uniform (header rows vs. tile rows, and tile row
 * height itself varies with the analytically-computed `tileWidth`), so
 * `estimateSize` reads per-index from `rowsResult.rowHeights` rather than
 * returning a constant — since those heights are exact (not actually
 * estimates), no `measureElement` DOM remeasurement is needed.
 */
import { computed, ref } from "vue";
import { storeToRefs } from "pinia";
import { useElementBounding } from "@vueuse/core";
import { useWindowVirtualizer } from "@tanstack/vue-virtual";
import { useAlbumStore } from "@/stores/AlbumState";
import { useAlbumsStore } from "@/stores/AlbumsState";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { usePropagateAlbumEvents } from "@/composables/album/propagateEvents";
import { useAlbumTileWidth } from "@/v8/composables/album/albumTileWidth";
import { buildVirtualAlbumRows, HEADER_ROW_HEIGHT, type VirtualTileRow } from "@/v8/composables/album/virtualAlbumRows";
import { aspectRatioCssToNumber } from "@/v8/utils/aspectRatioNumber";
import AlbumThumbVirtual from "@/v8/components/gallery/albumModule/Virtualized/AlbumThumbVirtual.vue";
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
const { tileWidth, itemsPerRow, gap } = useAlbumTileWidth(containerRef);

// Document-relative top of this component's own root element — reactive to
// both scroll (viewportTop and window.scrollY move in lockstep, so their
// sum is scroll-position-independent) and to layout shifts from content
// mounted above it (AlbumHero, statistics panel, ...).
const { top: viewportTop } = useElementBounding(containerRef);
const scrollMargin = computed(() => viewportTop.value + window.scrollY);

const aspectRatioNumber = computed(() => aspectRatioCssToNumber(albumStore.config?.album_thumb_css_aspect_ratio));

// Single unbucketed section (FR-062-09) when tier 1 isn't bucketable or the
// count-mismatch fallback fires (boundariesV3 === null) — same flat
// rendering path buildVirtualAlbumRows() already documents for both cases.
const showHeaders = computed(() => albumStore.bucketableV3);
const boundaries = computed(() =>
	albumStore.boundariesV3 !== null ? albumStore.boundariesV3 : [{ bucketId: "all", label: "", startIndex: 0, count: albumsStore.albums.length }],
);

const rowsResult = computed(() =>
	buildVirtualAlbumRows(
		albumsStore.albums.map((a) => a.id),
		boundaries.value,
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

function tilesForRow(row: VirtualTileRow): AdaptedAlbumTile[] {
	return albumsStore.albums.slice(row.startIndex, row.startIndex + row.count) as AdaptedAlbumTile[];
}

// Sticky pinned bucket label (FR-062-08): the currently-active bucket's
// header, shown only once its own real (virtualized) header row has
// scrolled past the top of the viewport — see the template comment above.
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
	// scrollOffset is the raw window.scrollY (unlike item.start, it is not
	// scrollMargin-shifted) — subtract scrollMargin to get back into the same
	// local, container-relative coordinates headerTops is computed in.
	const offset = (virtualizer.value.scrollOffset ?? 0) - scrollMargin.value;
	const tops = headerTops.value;
	let current: { top: number; label: string } | null = null;
	for (const h of tops) {
		if (h.top <= offset) {
			current = h;
		} else {
			break;
		}
	}
	if (current === null || offset < current.top + HEADER_ROW_HEIGHT) {
		return null;
	}
	return current.label;
});
</script>
