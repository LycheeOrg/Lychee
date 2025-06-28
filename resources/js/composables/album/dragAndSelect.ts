import { TogglablesStateStore } from "@/stores/ModalsState";
import { ref } from "vue";
import { useThrottleFn } from "@vueuse/core";
import { modKey, shiftKeyState } from "@/utils/keybindings-utils";

type InitialPosition = {
	top: number;
	left: number;
	right: number;
	bottom: number;
};

type Bounding = { id: string } & InitialPosition;

type Position = {
	top: number | string | undefined;
	left: number | string | undefined;
	right: number | string | undefined;
	bottom: number | string | undefined;
	width?: number | string | undefined;
	height?: number | string | undefined;
};

export function useDragAndSelect(
	togglableStore: TogglablesStateStore,
	photos: { id: string }[],
	albums: { id: string }[],
	withScroll: boolean = true,
) {
	// const position = ref<Position | undefined>(undefined);
	const position = ref<Position | undefined>({
		top: "50px",
		left: "50px",
		right: "inherit",
		bottom: "inherit",
		width: "200px",
		height: "300px",
	});
	const initialPosition = ref<InitialPosition | undefined>(undefined);

	const cache = {
		max_height: 0,
		max_width: 0,
		photo_boxes: [] as Bounding[],
		album_boxes: [] as Bounding[],
	};

	function get_max_width() {
		return document.getElementById("galleryView")?.clientWidth ?? 0;
	}
	function get_max_height() {
		return document.getElementById("galleryView")?.getBoundingClientRect().height ?? 0;
	}

	// We use a function here to get the padding top depending of whether we toggled fullscreen or not.
	function paddingTop() {
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

		const pageY = y(e.pageY);
		position.value = {
			top: undefined,
			left: undefined,
			right: undefined,
			bottom: undefined,
			width: undefined,
			height: undefined,
		};

		if (pageY >= initialPosition.value.top) {
			position.value.top = initialPosition.value.top + "px";
			position.value.bottom = "inherit";
			position.value.height = pageY - initialPosition.value.top + "px";
		} else {
			position.value.top = "inherit";
			position.value.bottom = initialPosition.value.bottom + "px";
			position.value.height = initialPosition.value.top - Math.max(pageY, 2) + "px";
		}

		if (e.pageX >= initialPosition.value.left) {
			position.value.right = "inherit";
			position.value.left = initialPosition.value.left + "px";
			position.value.width = Math.min(e.pageX, cache.max_width - 3) - initialPosition.value.left + "px";
		} else {
			position.value.right = initialPosition.value.right + "px";
			position.value.left = "inherit";
			position.value.width = initialPosition.value.left - Math.max(e.pageX, 2) + "px";
		}
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
			togglableStore.is_import_from_link_open ||
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

		const pageY = y(e.pageY);
		initialPosition.value = {
			top: pageY,
			right: cache.max_width - e.pageX,
			bottom: cache.max_height - pageY,
			left: e.pageX,
		};
		position.value = {
			top: pageY,
			right: cache.max_width - e.pageX,
			bottom: cache.max_height - pageY,
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
		const photos = document.querySelectorAll(`[${type}]`) as NodeListOf<HTMLElement>;
		const ret = [] as Bounding[];
		photos.forEach((el: HTMLElement) => {
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

		const photos_slected = cache.photo_boxes.filter((b) => isIntersecting(b, selector)).map((b) => b.id);
		if (photos_slected.length > 0) {
			togglableStore.selectedPhotosIdx = photos_slected.map((id) => photos?.findIndex((p) => p.id === id) ?? -1);
			togglableStore.selectedAlbumsIdx = [];
			return;
		}

		const albums_selected = cache.album_boxes.filter((b) => isIntersecting(b, selector)).map((b) => b.id);
		if (albums_selected.length > 0) {
			togglableStore.selectedAlbumsIdx = albums_selected.map((id) => albums?.findIndex((p) => p.id === id) ?? -1);
			togglableStore.selectedPhotosIdx = [];
			return;
		}

		togglableStore.selectedPhotosIdx = [];
		togglableStore.selectedAlbumsIdx = [];
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
