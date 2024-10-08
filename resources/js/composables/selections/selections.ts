import { useKeyModifier } from "@vueuse/core";
import { computed, ComputedRef, Ref, ref } from "vue";

export function useSelection(
	photos: Ref<{ [key: number]: App.Http.Resources.Models.PhotoResource }>,
	albums:
		| ComputedRef<{ [key: number]: App.Http.Resources.Models.ThumbAlbumResource }>
		| Ref<{ [key: number]: App.Http.Resources.Models.ThumbAlbumResource }>,
) {
	function get_platform() {
		// 2022 way of detecting. Note : this userAgentData feature is available only in secure contexts (HTTPS)
		// @ts-expect-error Legacy stuff
		if (typeof navigator.userAgentData !== "undefined" && navigator.userAgentData != null) {
			// @ts-expect-error Legacy stuff
			return navigator.userAgentData.platform;
		}
		// Deprecated but still works for most of the browser
		if (typeof navigator.platform !== "undefined") {
			if (typeof navigator.userAgent !== "undefined" && /android/.test(navigator.userAgent.toLowerCase())) {
				// android device’s navigator.platform is often set as 'linux', so let’s use userAgent for them
				return "android";
			}
			return navigator.platform;
		}
		return "unknown";
	}

	const platform = get_platform().toLowerCase();

	const isOSX = /mac/.test(platform); // Mac desktop
	const isIOS = ["iphone", "ipad", "ipod"].indexOf(platform) >= 0; // Mac iOs
	const isApple = isOSX || isIOS; // Apple device (desktop or iOS)

	const selectedPhotosIdx = ref([] as number[]);
	const selectedAlbumsIdx = ref([] as number[]);
	const selectedPhoto = computed(() => (selectedPhotosIdx.value.length === 1 ? photos.value[selectedPhotosIdx.value[0]] : undefined));
	const selectedAlbum = computed(() => (selectedAlbumsIdx.value.length === 1 ? albums.value[selectedAlbumsIdx.value[0]] : undefined));
	const selectedPhotos = computed(() =>
		(photos.value as App.Http.Resources.Models.PhotoResource[]).filter((_, idx) => selectedPhotosIdx.value.includes(idx)),
	);
	const selectedAlbums = computed(() =>
		(albums.value as App.Http.Resources.Models.ThumbAlbumResource[]).filter((_, idx) => selectedAlbumsIdx.value.includes(idx)),
	);
	const selectedPhotosIds = computed(() => selectedPhotos.value.map((p) => p.id));
	const selectedAlbumsIds = computed(() => selectedAlbums.value.map((a) => a.id));

	const ctrlKeyState = useKeyModifier("Control");
	const metaKeyState = useKeyModifier("Meta");
	const shiftKeyState = useKeyModifier("Shift");

	function modKey() {
		if (isApple) {
			return metaKeyState;
		}
		return ctrlKeyState;
	}

	// We save the last clicked index so we can do selections with shift.
	const lastPhotoClicked = ref(undefined as number | undefined);
	const lastAlbumClicked = ref(undefined as number | undefined);

	const isPhotoSelected = (idx: number) => selectedPhotosIdx.value.includes(idx);
	const isAlbumSelected = (idx: number) => selectedAlbumsIdx.value.includes(idx);

	function addToPhotoSelection(idx: number) {
		selectedPhotosIdx.value.push(idx);
	}
	function removeFromPhotoSelection(idx: number) {
		selectedPhotosIdx.value = selectedPhotosIdx.value.filter((i) => i !== idx);
	}

	function addToAlbumSelection(idx: number) {
		selectedAlbumsIdx.value.push(idx);
	}
	function removeFromAlbumSelection(idx: number) {
		selectedAlbumsIdx.value = selectedAlbumsIdx.value.filter((i) => i !== idx);
	}

	function photoClick(idx: number, e: Event) {
		// clear the Album selection.
		selectedAlbumsIdx.value = [];

		// we do not support CTRL + SHIFT
		if (!modKey().value && !shiftKeyState.value) {
			return;
		}

		// We are able to edit.
		e.preventDefault();
		e.stopPropagation();

		if (modKey().value) {
			handlePhotoCtrl(idx, e);
			return;
		}

		if (shiftKeyState.value) {
			handlePhotoShift(idx, e);
			return;
		}
	}

	function handlePhotoCtrl(idx: number, e: Event) {
		if (isPhotoSelected(idx)) {
			removeFromPhotoSelection(idx);
		} else {
			addToPhotoSelection(idx);
		}
		lastPhotoClicked.value = idx;
	}

	function handlePhotoShift(idx: number, e: Event) {
		if (selectedPhotos.value.length === 0) {
			addToPhotoSelection(idx);
			return;
		}

		// Picture is selected.
		// We remove all pictures from latest click till current idx
		if (isPhotoSelected(idx)) {
			// @ts-expect-error lastPhotoClicked is always defined here
			const idx_min = Math.min(lastPhotoClicked.value, idx);
			// @ts-expect-error lastPhotoClicked is always defined here
			const idx_max = Math.max(lastPhotoClicked.value, idx);
			for (let i = idx_min; i <= idx_max; i++) {
				removeFromPhotoSelection(i);
			}
		} else if (lastPhotoClicked.value === undefined) {
			addToPhotoSelection(idx);
		} else {
			const idx_min = Math.min(lastPhotoClicked.value, idx);
			const idx_max = Math.max(lastPhotoClicked.value, idx);
			for (let i = idx_min; i <= idx_max; i++) {
				addToPhotoSelection(i);
			}
		}
		lastPhotoClicked.value = idx;
	}

	function albumClick(idx: number, e: Event) {
		// clear the Photo selection.
		selectedPhotosIdx.value = [];

		// we do not support CTRL + SHIFT
		if (!ctrlKeyState.value && !shiftKeyState.value) {
			return;
		}

		// We are able to edit.
		e.preventDefault();
		e.stopPropagation();

		if (ctrlKeyState.value) {
			handleAlbumCtrl(idx, e);
			return;
		}

		if (shiftKeyState.value) {
			handleAlbumShift(idx, e);
			return;
		}
	}

	function handleAlbumCtrl(idx: number, e: Event) {
		if (isAlbumSelected(idx)) {
			removeFromAlbumSelection(idx);
		} else {
			addToAlbumSelection(idx);
		}
		lastAlbumClicked.value = idx;
	}

	function handleAlbumShift(idx: number, e: Event) {
		if (selectedAlbums.value.length === 0) {
			addToAlbumSelection(idx);
			return;
		}

		// Picture is selected.
		// We remove all pictures from latest click till current idx
		if (isAlbumSelected(idx)) {
			// @ts-expect-error lastAlbumClicked is always defined here
			const idx_min = Math.min(lastAlbumClicked.value, idx);
			// @ts-expect-error lastAlbumClicked is always defined here
			const idx_max = Math.max(lastAlbumClicked.value, idx);
			for (let i = idx_min; i <= idx_max; i++) {
				removeFromAlbumSelection(i);
			}
		} else if (lastAlbumClicked.value === undefined) {
			addToAlbumSelection(idx);
		} else {
			const idx_min = Math.min(lastAlbumClicked.value, idx);
			const idx_max = Math.max(lastAlbumClicked.value, idx);
			for (let i = idx_min; i <= idx_max; i++) {
				addToAlbumSelection(i);
			}
		}
		lastAlbumClicked.value = idx;
	}

	return {
		selectedPhoto,
		selectedAlbum,
		selectedPhotosIdx,
		selectedAlbumsIdx,
		selectedPhotos,
		selectedAlbums,
		selectedPhotosIds,
		selectedAlbumsIds,
		photoClick,
		albumClick,
	};
}
