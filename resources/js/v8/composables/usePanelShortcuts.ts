import { useTogglablesStateStore } from "@/stores/ModalsState";
import { usePhotoStore } from "@/stores/PhotoState";
import { defineShortcuts, type ShortcutsConfig, type ShortcutsOptions } from "@nuxt/ui/composables/defineShortcuts";
import { computed, toValue, type MaybeRefOrGetter } from "vue";

export function definePanelShortcuts(config: MaybeRefOrGetter<ShortcutsConfig>, options?: ShortcutsOptions) {
	const togglableStore = useTogglablesStateStore();
	const photoStore = usePhotoStore();

	const hasSelection = computed(() => togglableStore.selectedPhotosIds.length > 0 || togglableStore.selectedAlbumsIds.length > 0);

	function clearSelection() {
		togglableStore.selectedPhotosIds = [];
		togglableStore.selectedAlbumsIds = [];
	}

	const panelConfig = computed<ShortcutsConfig>(() => {
		if (togglableStore.is_modal_open) {
			return {};
		}

		const shortcuts = toValue(config);
		if (!hasSelection.value || photoStore.isLoaded) {
			return shortcuts;
		}

		return { ...shortcuts, escape: { usingInput: true, handler: clearSelection } };
	});

	return defineShortcuts(panelConfig, options);
}
