import { useTogglablesStateStore } from "@/stores/ModalsState";
import { usePhotoStore, type ZoomControls } from "@/stores/PhotoState";
import { defineShortcuts, type ShortcutsConfig, type ShortcutsOptions } from "@nuxt/ui/composables/defineShortcuts";
import { computed, toValue, type MaybeRefOrGetter } from "vue";

export type LocalSelection = {
	has: MaybeRefOrGetter<boolean>;
	clear: () => void;
};

export type PanelShortcutsOptions = ShortcutsOptions & {
	localSelection?: LocalSelection;
	// Gate for modals that live outside the Pinia-backed togglables store
	// (e.g. module-level singleton state shared with v7), so shortcuts still
	// pause while they're open.
	extraModalOpen?: MaybeRefOrGetter<boolean>;
};

const RATING_KEYS = ["1", "2", "3", "4", "5"];

/**
 * Feature 078 (FR-078-11, FR-078-21): zoom keys for the open photo. `z`, `+`/`=`
 * and `-` while it is zoomable; while it is zoomed, `escape` and `0` reset the
 * zoom instead of the view's binding and the rating keys `1`–`5` are inactive.
 */
function withZoomShortcuts(shortcuts: ShortcutsConfig, controls: ZoomControls | undefined, isZoomed: boolean): ShortcutsConfig {
	if (controls === undefined) {
		return shortcuts;
	}

	const zoomable: ShortcutsConfig = { ...shortcuts, z: controls.toggle, "+": controls.zoomIn, "=": controls.zoomIn, "-": controls.zoomOut };
	if (!isZoomed) {
		return zoomable;
	}

	const withoutRatings = Object.fromEntries(Object.entries(zoomable).filter(([key]) => !RATING_KEYS.includes(key)));
	return { ...withoutRatings, escape: controls.reset, "0": controls.reset };
}

export function definePanelShortcuts(config: MaybeRefOrGetter<ShortcutsConfig>, options?: PanelShortcutsOptions) {
	const togglableStore = useTogglablesStateStore();
	const photoStore = usePhotoStore();

	const { localSelection, extraModalOpen, ...shortcutsOptions } = options ?? {};

	const hasSelection = computed(
		() =>
			togglableStore.selectedPhotosIds.length > 0 ||
			togglableStore.selectedAlbumsIds.length > 0 ||
			(localSelection !== undefined && toValue(localSelection.has)),
	);

	function clearSelection() {
		togglableStore.selectedPhotosIds = [];
		togglableStore.selectedAlbumsIds = [];
		localSelection?.clear();
	}

	const panelConfig = computed<ShortcutsConfig>(() => {
		if (togglableStore.is_modal_open || toValue(extraModalOpen)) {
			return {};
		}

		const shortcuts = photoStore.isLoaded ? withZoomShortcuts(toValue(config), photoStore.zoom_controls, photoStore.is_zoomed) : toValue(config);
		if (!hasSelection.value || photoStore.isLoaded) {
			return shortcuts;
		}

		return { ...shortcuts, escape: { usingInput: true, handler: clearSelection } };
	});

	return defineShortcuts(panelConfig, shortcutsOptions);
}
