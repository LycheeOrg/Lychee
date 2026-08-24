import { computed, type ComputedRef, type Ref } from "vue";
import { trans } from "laravel-vue-i18n";
import { Router } from "vue-router";
import type { AdminTile } from "@/v8/composables/useAdminTiles";
import type { SpotlightItem } from "./types";

/**
 * Admin tiles (filtered to the ones the current user can see) plus the dark/light mode
 * toggle - the parts of the "actions" group that aren't specific to the currently
 * open/browsed album (see useSpotlightGalleryActions for those).
 */
export function useSpotlightSystemActions(
	adminTiles: AdminTile[],
	router: Router,
	isDark: Ref<boolean>,
	initData: Ref<App.Http.Resources.Rights.GlobalRightsResource | undefined>,
	toggleDarkMode: () => void,
	toggleDarkModeGlobal: () => void,
	close: () => void,
): ComputedRef<SpotlightItem[]> {
	return computed(() => {
		const fromAdmin: SpotlightItem[] = adminTiles
			.filter((tile) => tile.visible.value)
			.map((tile) => ({
				label: trans(tile.label),
				icon: tile.icon,
				kind: "nav" as const,
				onSelect: () => {
					close();
					if (tile.isExternal) {
						window.open(tile.to, "_blank");
					} else {
						router.push(tile.to);
					}
				},
			}));

		// Admins persist the choice as the instance-wide default (see General.vue's own dark
		// mode setting); everyone else only gets a browser-local override.
		const themeItem: SpotlightItem = {
			label: isDark.value ? trans("search-palette.light_mode") : trans("search-palette.dark_mode"),
			icon: isDark.value ? "lucide:sun" : "lucide:moon",
			kind: "nav",
			onSelect: () => {
				close();
				if (initData.value?.settings.can_edit) {
					toggleDarkModeGlobal();
				} else {
					toggleDarkMode();
				}
			},
		};

		return [themeItem, ...fromAdmin];
	});
}
