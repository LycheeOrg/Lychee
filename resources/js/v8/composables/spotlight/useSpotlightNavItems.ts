import { computed, type ComputedRef } from "vue";
import { Router } from "vue-router";
import type { LeftMenuItem } from "@/v8/composables/contextMenus/leftMenu";
import type { SpotlightItem } from "./types";

/**
 * Flattens the left-nav's own `menuItems`/`profileItems` into Spotlight rows. The "Admin
 * panel" link is dropped: it only leads to a page of tiles, and Spotlight lists those
 * tiles directly (see useSpotlightSystemActions), so the link itself would be redundant.
 */
export function useSpotlightNavItems(
	menuItems: ComputedRef<LeftMenuItem[]>,
	profileItems: ComputedRef<LeftMenuItem[]>,
	router: Router,
	close: () => void,
): ComputedRef<SpotlightItem[]> {
	return computed(() =>
		[...menuItems.value, ...profileItems.value]
			.filter((item) => item.label !== undefined && item.to !== "/admin")
			.map((item) => ({
				label: item.label as string,
				icon: item.icon,
				kind: "nav" as const,
				onSelect: () => {
					close();
					if (item.onSelect) {
						item.onSelect(new Event("select"));
					} else if (item.to !== undefined) {
						router.push(item.to);
					}
				},
			})),
	);
}
