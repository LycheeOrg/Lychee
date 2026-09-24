import { computed, watch, type ComputedRef, type Ref } from "vue";
import type { Virtualizer } from "@tanstack/vue-virtual";
import { useAlbumStore } from "@/stores/AlbumState";
import type { VirtualAlbumRowsResult } from "@/v8/composables/album/virtualAlbumRows";
import type { AdaptedAlbumTile } from "@/v8/utils/adaptAlbumChildTile";
import { albumRowScrubItems, deriveDayScrubEntries, formatDayLabel, type DateScrubberField, type DateScrubLayout } from "@/v8/utils/dateScrubber";

/**
 * Feature 071 — date scrubber wiring shared by the two sub-album views
 * (`AlbumThumbGridVirtual.vue`, `AlbumListViewVirtual.vue`), which lay out
 * the same `buildVirtualAlbumRows()` rows. Emits the rail's day entries
 * whenever the rows change, emits the scroll offset only while there are
 * entries to follow, and returns the rail's scroll target.
 */
export function useAlbumRowsDateScrubber(
	rowsResult: ComputedRef<VirtualAlbumRowsResult>,
	tiles: ComputedRef<AdaptedAlbumTile[]>,
	virtualizer: Ref<Virtualizer<Window, Element>>,
	scrollMargin: ComputedRef<number>,
	emitLayout: (layout: DateScrubLayout) => void,
	emitScrollOffset: (offset: number) => void,
) {
	const albumStore = useAlbumStore();

	const scrubberLayout = computed<DateScrubLayout | null>(() => {
		const field = (albumStore.config?.album_date_scrubber_field ?? null) as DateScrubberField | null;
		if (field === null) {
			return null;
		}
		const format = albumStore.config?.date_scrubber_label_format ?? "j M Y";
		const { items, totalHeight } = albumRowScrubItems(rowsResult.value.rows, rowsResult.value.rowHeights, tiles.value, field);
		return deriveDayScrubEntries(items, totalHeight, (day) => formatDayLabel(day, format));
	});

	watch(
		scrubberLayout,
		(layout) => {
			if (layout !== null) {
				emitLayout(layout);
			}
		},
		{ immediate: true },
	);

	const contentScrollOffset = computed(() => (virtualizer.value.scrollOffset ?? 0) - scrollMargin.value);

	watch(
		contentScrollOffset,
		(offset) => {
			if ((scrubberLayout.value?.entries.length ?? 0) > 0) {
				emitScrollOffset(offset);
			}
		},
		{ immediate: true },
	);

	function scrollToPixelOffset(px: number): void {
		virtualizer.value.scrollToOffset(px + scrollMargin.value);
	}

	return { scrollToPixelOffset };
}
