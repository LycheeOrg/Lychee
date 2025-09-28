import { TogglablesStateStore } from "@/stores/ModalsState";
import { ref } from "vue";
import { useThrottleFn } from "@vueuse/core";
import { modKey, shiftKeyState } from "@/utils/keybindings-utils";
import { useAlbumActions } from "./albumActions";
import { AlbumsStore } from "@/stores/AlbumsState";
import { PhotosStore } from "@/stores/PhotosState";

const { canInteractAlbum, canInteractPhoto } = useAlbumActions();

type InitialPosition = {
	top: number;
	left: number;
};

type Bounding = {
	id: string;
	top: number;
	left: number;
	right: number;
	bottom: number;
};

type Position = {
	top: number | string | undefined;
	left: number | string | undefined;
	width?: number | string | undefined;
	height?: number | string | undefined;
};

export function useDragAndSelect(
	togglableStore: TogglablesStateStore,
	albumsStore: AlbumsStore,
	photosStore: PhotosStore,
	withScroll: boolean = true,
) {
	const initialPosition = ref<InitialPosition | undefined>(undefined);
	const position = ref<Position | undefined>(undefined);

	const cache = {
		max_height: 0,
		max_width: 0,
		photo_boxes: [] as Bounding[],
		album_boxes: [] as Bounding[],

		// We store the current selection indices to restore them after the selection is done.
		currentPhotoSelectionIdx: [] as number[],
		currentAlbumSelectionIdx: [] as number[],
	};

	function get_max_width() {
		return document.getElementById("galleryView")?.clientWidth ?? 0;
	}
	function get_max_height() {
		return document.getElementById("galleryView")?.getBoundingClientRect().height ?? 0;
	}

	// We use a function here to get the padding top depending of whether we toggled fullscreen or not.
	function paddingTop() {
		if (withScroll === false) return 0; // If we don't want to use scroll, we return 0.
		return document.getElementById("galleryView")?.getClientRects()[0].y ?? 0;
	}

	// Similarly we use this to get the scroll position.
	function scrollFromTop() {
		if (withScroll === false) return 0; // If we don't want to use scroll, we return 0.
		return document.getElementById("galleryView")?.scrollTop ?? 0;
	}

	// Convert the pageY coordinate (from e.g. mouse event) to the "real"
	// y coordinate in the gallery view.
	// y = 0 is the top of the gallery view, not the top of the page.
	function y(pageY: number): number {
		return pageY + scrollFromTop() - paddingTop();
	}

	// Resize the selection box based on mouse position.
	function resize(e: MouseEvent) {
		if (initialPosition.value === undefined) return false;
		togglableStore.isDragging = true;

		const diffX = e.pageX - initialPosition.value.left;
		const diffY = y(e.pageY) - initialPosition.value.top;

		position.value = {
			left: diffX < 0 ? initialPosition.value.left + diffX + "px" : initialPosition.value.left + "px",
			top: diffY < 0 ? initialPosition.value.top + diffY + "px" : initialPosition.value.top + "px",
			width: Math.abs(diffX) + "px",
			height: Math.abs(diffY) + "px",
		};

		throttledApplySelection();
	}

	function canStart(e: MouseEvent): boolean {
		// We use short circuit evaluation.
		if (
			e.button !== 0 || // button is pressed not left
			togglableStore.is_login_open ||
			togglableStore.is_webauthn_open ||
			togglableStore.is_metrics_open ||
			togglableStore.is_upload_visible ||
			togglableStore.is_create_album_visible ||
			togglableStore.is_create_tag_album_visible ||
			togglableStore.is_album_edit_open ||
			togglableStore.is_slideshow_active ||
			togglableStore.is_import_from_link_open ||
			togglableStore.is_rename_visible ||
			togglableStore.is_move_visible ||
			togglableStore.is_delete_visible ||
			togglableStore.is_merge_album_visible ||
			togglableStore.is_share_album_visible ||
			togglableStore.is_tag_visible ||
			togglableStore.is_copy_visible
		) {
			return false;
		}

		return true;
	}

	function show(e: MouseEvent) {
		if (!canStart(e)) {
			return;
		}

		// If we do not have the shift or control key pressed, erase the selection immediately.
		if (!modKey().value && !shiftKeyState.value) {
			togglableStore.selectedPhotosIdx = [];
			togglableStore.selectedAlbumsIdx = [];
		}

		cache.max_height = get_max_height();
		cache.max_width = get_max_width();
		cache.photo_boxes = getBoxes("data-photo-id");
		cache.album_boxes = getBoxes("data-album-id");
		// We use slide to Copy the array: https://stackoverflow.com/questions/7486085/copy-array-by-value
		// Otherwise that would be a reference to the original array and we would modify it.
		cache.currentPhotoSelectionIdx = togglableStore.selectedPhotosIdx.slice();
		cache.currentAlbumSelectionIdx = togglableStore.selectedAlbumsIdx.slice();

		initialPosition.value = {
			top: y(e.pageY),
			left: e.pageX,
		};
		position.value = {
			top: y(e.pageY),
			left: e.pageX,
		};
		document.addEventListener("mousemove", resize);
		document.addEventListener("mouseup", stopResize);
	}

	function stopResize() {
		document.removeEventListener("mousemove", resize);
		document.removeEventListener("mouseup", stopResize);
		initialPosition.value = undefined;
		position.value = undefined;
		togglableStore.isDragging = false;
	}

	function getBounding(e: HTMLElement, id: string): Bounding {
		const rect = e.getBoundingClientRect();
		const top = y(rect.top);
		return {
			id: id,
			top: top,
			left: rect.left,
			right: rect.left + rect.width,
			bottom: top + rect.height,
		};
	}

	function isIntersecting(a: Bounding, b: Bounding): boolean {
		return !(a.left > b.right || a.right < b.left || a.top > b.bottom || a.bottom < b.top);
	}

	function getBoxes(type: string): Bounding[] {
		const root = document.getElementById("galleryView");
		const nodes = root
			? (root.querySelectorAll(`[${type}]`) as NodeListOf<HTMLElement>)
			: (document.querySelectorAll(`[${type}]`) as NodeListOf<HTMLElement>);
		const ret = [] as Bounding[];
		nodes.forEach((el: HTMLElement) => {
			const id = el.getAttribute(type);
			if (id === null) return;

			const box = getBounding(el, id);
			ret.push(box);
		});

		return ret;
	}

	function applySelection() {
		// We do nothing if the position is not set
		if (position.value === undefined) return;

		const selector = getBounding(document.getElementById("selector") as HTMLElement, "selector");

		const photos_intersected = cache.photo_boxes.filter((b) => isIntersecting(b, selector)).map((b) => b.id);
		if (photos_intersected.length > 0) {
			const selectedPhotos = reduceIntersection(photos_intersected, photosStore.photos, canInteractPhoto);

			togglableStore.selectedPhotosIdx = cache.currentPhotoSelectionIdx.concat(selectedPhotos);
			togglableStore.selectedAlbumsIdx = [];
			return;
		}

		const albums_intersected = cache.album_boxes.filter((b) => isIntersecting(b, selector)).map((b) => b.id);
		if (albums_intersected.length > 0) {
			const selectedAlbums = reduceIntersection(albums_intersected, albumsStore.selectableAlbums, canInteractAlbum);
			togglableStore.selectedAlbumsIdx = cache.currentAlbumSelectionIdx.concat(selectedAlbums);
			togglableStore.selectedPhotosIdx = [];
			return;
		}

		togglableStore.selectedPhotosIdx = cache.currentPhotoSelectionIdx;
		togglableStore.selectedAlbumsIdx = cache.currentAlbumSelectionIdx;
	}

	function reduceIntersection<Model>(intersection: string[], selectables: ({ id: string } & Model)[], validator: (i: Model) => boolean): number[] {
		return intersection.reduce((result: number[], id) => {
			const idx = selectables.findIndex((p) => p.id === id && validator(p));
			if (idx !== -1 && idx !== undefined) {
				result.push(idx);
			}
			return result;
		}, []);
	}

	// We throttle the applySelection function to avoid performance issues
	// when the user is dragging the selection box.
	// This will ensure that the function is not called too frequently.
	// The delay is set to 100ms, which should still make it fluid "enough".
	const throttledApplySelection = useThrottleFn(applySelection, 100);

	return {
		initialPosition,
		position,
		show,
	};
}
