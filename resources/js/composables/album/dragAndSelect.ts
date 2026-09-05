import { TogglablesStateStore } from "@/stores/ModalsState";
import { ref } from "vue";
import { useThrottleFn } from "@vueuse/core";
import { modKey, shiftKeyState } from "@/utils/keybindings-utils";
import { useAlbumActions } from "./albumActions";
import { AlbumsStore } from "@/stores/AlbumsState";
import { PhotosStore } from "@/stores/PhotosState";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useAlbumStore } from "@/stores/AlbumState";
import { computeAlbumTileGeometry, resolveBreakpoint } from "@/v8/composables/album/albumTileWidth";
import { buildVirtualAlbumRows, LIST_ROW_HEIGHT } from "@/v8/composables/album/virtualAlbumRows";
import { aspectRatioCssToNumber } from "@/v8/utils/aspectRatioNumber";
import { filterBucketedTiles } from "@/v8/utils/albumBucketBoundaries";

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

	const lycheeStore = useLycheeStateStore();
	const albumStore = useAlbumStore();

	const cache = {
		max_height: 0,
		max_width: 0,
		photo_boxes: [] as Bounding[],
		album_boxes: [] as Bounding[],

		// We store the current selection IDs to restore them after the selection is done.
		currentPhotoSelectionIds: [] as string[],
		currentAlbumSelectionIds: [] as string[],
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

	function isInteractiveTarget(target: EventTarget | null): boolean {
		if (!(target instanceof HTMLElement)) return false;
		if (target.closest("[data-stop-drag-select='true']")) return true;
		const interactiveSelectors =
			"a,button,input,textarea,select,summary,[role='button'],[role='menuitem'],[role='link'],.p-drawer-mask,.p-speeddial,.p-contextmenu,.p-dialog";
		return target.closest(interactiveSelectors) !== null;
	}

	function show(e: MouseEvent) {
		if (!canStart(e) || isInteractiveTarget(e.target)) {
			return;
		}

		// If we do not have the shift or control key pressed, erase the selection immediately.
		if (!modKey().value && !shiftKeyState.value) {
			togglableStore.selectedPhotosIds = [];
			togglableStore.selectedAlbumsIds = [];
		}

		cache.max_height = get_max_height();
		cache.max_width = get_max_width();
		cache.photo_boxes = getBoxes("data-photo-id");
		cache.album_boxes = lycheeStore.is_struct_of_array_enabled ? getAlbumBoxesV3() : getBoxes("data-album-id");
		// We use slice to Copy the array: https://stackoverflow.com/questions/7486085/copy-array-by-value
		// Otherwise that would be a reference to the original array and we would modify it.
		cache.currentPhotoSelectionIds = togglableStore.selectedPhotosIds.slice();
		cache.currentAlbumSelectionIds = togglableStore.selectedAlbumsIds.slice();

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

	/**
	 * Flag-on replacement for `getBoxes("data-album-id")` —
	 * virtualization only ever mounts the tiles near the viewport, so a DOM
	 * query would miss every off-screen album a drag rectangle could still
	 * cover. Instead this reproduces the exact geometry
	 * AlbumThumbGridVirtual.vue/AlbumListViewVirtual.vue (sub-album children)
	 * or AlbumRootGridVirtual.vue/AlbumRootListViewVirtual.vue (root gallery
	 * own/shared, 2026-09-02 root-SoA addendum) lay their tiles out with —
	 * same `computeAlbumTileGeometry`/`buildVirtualAlbumRows` pure functions,
	 * same inputs — and anchors it via `getBounding()` on each mounted grid
	 * root (`[data-album-grid-root]`, always in the DOM even when its tiles
	 * aren't), so the result lands in the exact same coordinate system
	 * `getBoxes()` itself uses, whatever `#galleryView`'s own scroll model
	 * turns out to be — no need to duplicate that math here.
	 *
	 * Queries *every* `[data-album-grid-root]`, not just the first: the root
	 * gallery's non-tabbed SHOW mode can mount two independent grids
	 * simultaneously (own + shared, `[data-album-grid-scope="own"|"shared"]`)
	 * — a drag spanning both must select tiles from both. The sub-album path
	 * never sets `data-album-grid-scope` at all, so it's distinguished from
	 * either root scope by that attribute's absence.
	 */
	function getAlbumBoxesV3(): Bounding[] {
		const gridRootEls = document.querySelectorAll<HTMLElement>("[data-album-grid-root]");
		const boxes: Bounding[] = [];

		gridRootEls.forEach((gridRootEl) => {
			const scope = gridRootEl.getAttribute("data-album-grid-scope");
			const tiles = scope === "own" ? albumsStore.albums : scope === "shared" ? albumsStore.sharedAlbumsV3 : albumsStore.albums;
			const boundariesV3 =
				scope === "own" ? albumsStore.ownBoundariesV3 : scope === "shared" ? albumsStore.sharedBoundariesV3 : albumStore.boundariesV3;
			const bucketableV3 =
				scope === "own" ? albumsStore.ownBucketableV3 : scope === "shared" ? albumsStore.sharedBucketableV3 : albumStore.bucketableV3;

			const isListMode = lycheeStore.album_view_mode === "list";
			const viewportWidth = window.innerWidth;
			const containerWidth = gridRootEl.getBoundingClientRect().width;

			let itemsPerRow: number;
			let tileWidth: number;
			let gap: number;
			let aspectRatioNumber: number;
			if (isListMode) {
				itemsPerRow = 1;
				tileWidth = LIST_ROW_HEIGHT;
				gap = 0;
				aspectRatioNumber = 1;
			} else {
				const breakpoint = resolveBreakpoint(viewportWidth);
				const geometry = computeAlbumTileGeometry(viewportWidth, containerWidth, breakpoint, lycheeStore.number_albums_per_row_mobile);
				itemsPerRow = geometry.itemsPerRow;
				tileWidth = geometry.tileWidth;
				gap = geometry.gap;
				aspectRatioNumber = aspectRatioCssToNumber(
					scope === null ? albumStore.config?.album_thumb_css_aspect_ratio : albumsStore.rootConfig?.album_thumb_css_aspect_ratio,
				);
			}

			const boundaries = boundariesV3 ?? [{ bucketId: "all", label: "", startIndex: 0, count: tiles.length }];
			// Filter NSFW-hidden tiles out *before* row-chunking, same as
			// AlbumThumbGridVirtual.vue/AlbumRootGridVirtual.vue/the list forks —
			// buildVirtualAlbumRows() bakes bucket counts into fixed-size rows, so
			// skipping a hidden tile's box afterwards (at emit time) leaves every
			// later tile's real DOM row one slot ahead of what getTileBox(i) still
			// reports, drifting every following hit-box.
			const { tiles: visibleTiles, boundaries: visibleBoundaries } = filterBucketedTiles(
				tiles,
				boundaries,
				(album) => !album.is_nsfw || lycheeStore.are_nsfw_visible,
			);

			const { getTileBox } = buildVirtualAlbumRows(
				visibleTiles.map((a) => a.id),
				visibleBoundaries,
				bucketableV3,
				itemsPerRow,
				tileWidth,
				aspectRatioNumber,
				gap,
			);

			const gridBox = getBounding(gridRootEl, "root");

			visibleTiles.forEach((album, i) => {
				const box = getTileBox(i);
				const top = gridBox.top + box.top;
				const left = gridBox.left + box.left;
				// List-mode rows render full-width (title/metadata extend past the
				// 40px thumbnail, see AlbumListItemVirtual.vue) — `box.width` here is
				// only `tileWidth` (=LIST_ROW_HEIGHT) reused to derive row height, not
				// the row's actual rendered width, so the hit-box must reach
				// `gridBox.right` or drags hovering the title/metadata miss the row.
				const right = isListMode ? gridBox.right : left + box.width;
				boxes.push({
					id: album.id,
					top: top,
					left: left,
					right: right,
					bottom: top + box.height,
				});
			});
		});

		return boxes;
	}

	function applySelection() {
		// We do nothing if the position is not set
		if (position.value === undefined) return;

		const selector = getBounding(document.getElementById("selector") as HTMLElement, "selector");

		const photos_intersected = cache.photo_boxes.filter((b) => isIntersecting(b, selector)).map((b) => b.id);
		if (photos_intersected.length > 0) {
			const selectedPhotoIds = reduceIntersection(photos_intersected, photosStore.photos, canInteractPhoto);

			togglableStore.selectedPhotosIds = cache.currentPhotoSelectionIds.concat(selectedPhotoIds);
			togglableStore.selectedAlbumsIds = [];
			return;
		}

		const albums_intersected = cache.album_boxes.filter((b) => isIntersecting(b, selector)).map((b) => b.id);
		if (albums_intersected.length > 0) {
			const selectedAlbumIds = reduceIntersection(albums_intersected, albumsStore.selectableAlbums, canInteractAlbum);
			togglableStore.selectedAlbumsIds = cache.currentAlbumSelectionIds.concat(selectedAlbumIds);
			togglableStore.selectedPhotosIds = [];
			return;
		}

		togglableStore.selectedPhotosIds = cache.currentPhotoSelectionIds;
		togglableStore.selectedAlbumsIds = cache.currentAlbumSelectionIds;
	}

	// Returns IDs (string[]) of items that are in the intersection and pass the validator
	function reduceIntersection<Model extends { id: string }>(
		intersection: string[],
		selectables: Model[],
		validator: (i: Model) => boolean,
	): string[] {
		return intersection.reduce((result: string[], id) => {
			const item = selectables.find((p) => p.id === id && validator(p));
			if (item) {
				result.push(item.id);
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
