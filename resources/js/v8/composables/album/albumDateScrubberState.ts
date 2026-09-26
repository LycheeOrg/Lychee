import { computed, ref } from "vue";
import { useAlbumStore } from "@/stores/AlbumState";
import { useAlbumsStore } from "@/stores/AlbumsState";
import { usePhotosStore } from "@/stores/PhotosState";
import { resolveDateScrubberSource, type DateScrubLayout } from "@/v8/utils/dateScrubber";

/**
 * Feature 071 — album date scrubber state shared by `AlbumPanel.vue` (which
 * mounts the rail and receives the grids' layouts) and `AlbumHeader.vue`
 * (the viewer toggle, FR-071-07). Module-level refs: there is only ever one
 * album view mounted, and `AlbumPanel.vue` (keyed by album id) calls
 * `resetLayouts()` whenever a new one is set up.
 */

const HIDDEN_STORAGE_KEY = "lychee.album_date_scrubber_hidden";

function readHidden(): boolean {
	try {
		return localStorage.getItem(HIDDEN_STORAGE_KEY) === "1";
	} catch {
		return false;
	}
}

function writeHidden(hidden: boolean): void {
	try {
		localStorage.setItem(HIDDEN_STORAGE_KEY, hidden ? "1" : "0");
	} catch {
		// Storage unavailable: the preference lasts for this session only.
	}
}

const isHidden = ref(readHidden());
const photoLayout = ref<DateScrubLayout | null>(null);
const albumLayout = ref<DateScrubLayout | null>(null);
const photoScrollOffset = ref(0);
const albumScrollOffset = ref(0);

export function useAlbumDateScrubberState() {
	const albumStore = useAlbumStore();
	const albumsStore = useAlbumsStore();
	const photosStore = usePhotosStore();

	/** Smart albums never load a children listing, so their album count is 0; otherwise the count is known once the children tiers resolve. */
	const albumCount = computed<number | undefined>(() => {
		if (albumStore.config?.is_base_album === false) {
			return 0;
		}
		return albumStore.bucketsV3 !== undefined ? albumsStore.albums.length : undefined;
	});

	const photoCount = computed<number | undefined>(() => (albumStore.photoBucketsV3 !== undefined ? photosStore.photos.length : undefined));

	const source = computed(() =>
		resolveDateScrubberSource({
			soaActive: albumStore.isPhotoSoaActive,
			enabled: albumStore.config?.is_date_scrubber_enabled ?? false,
			albumCount: albumCount.value,
			photoCount: photoCount.value,
			photoField: albumStore.config?.photo_date_scrubber_field ?? null,
			albumField: albumStore.config?.album_date_scrubber_field ?? null,
		}),
	);

	const activeLayout = computed(() => (source.value === "photos" ? photoLayout.value : source.value === "albums" ? albumLayout.value : null));

	const activeScrollOffset = computed(() => (source.value === "albums" ? albumScrollOffset.value : photoScrollOffset.value));

	/** The rail can be shown: an eligible source with at least one dated tile (FR-071-08's failure path). Drives the header toggle. */
	const isAvailable = computed(() => (activeLayout.value?.entries.length ?? 0) > 0);

	function toggleHidden(): void {
		isHidden.value = !isHidden.value;
		writeHidden(isHidden.value);
	}

	function resetLayouts(): void {
		photoLayout.value = null;
		albumLayout.value = null;
		photoScrollOffset.value = 0;
		albumScrollOffset.value = 0;
	}

	return {
		source,
		activeLayout,
		activeScrollOffset,
		isAvailable,
		isHidden,
		toggleHidden,
		resetLayouts,
		photoLayout,
		albumLayout,
		photoScrollOffset,
		albumScrollOffset,
	};
}
