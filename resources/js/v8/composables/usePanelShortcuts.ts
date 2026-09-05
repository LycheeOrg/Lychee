import { useTogglablesStateStore } from "@/stores/ModalsState";
import { usePhotoStore } from "@/stores/PhotoState";
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

		const shortcuts = toValue(config);
		if (!hasSelection.value || photoStore.isLoaded) {
			return shortcuts;
		}

		return { ...shortcuts, escape: { usingInput: true, handler: clearSelection } };
	});

	return defineShortcuts(panelConfig, shortcutsOptions);
}
