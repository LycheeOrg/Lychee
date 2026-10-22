/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { watch } from "vue";
import { useRoute } from "vue-router";
import { useTileUrlsStore } from "@/stores/TileUrlsState";
import { useUserStore } from "@/stores/UserState";
import { tileUrlSource } from "@/v8/composables/photo/tileUrlLoader";

/**
 * Asks the tile URL store for the direct URLs of the tiles a virtual grid
 * renders (visible plus overscan, Feature 102, FR-102-06): whenever that id
 * set changes, and again after a source or viewer change. The route names
 * the source; a route without one (search) asks nothing.
 */
export function useTileUrlRequests(renderedIds: () => string[]): void {
	const store = useTileUrlsStore();
	const userStore = useUserStore();
	const route = useRoute();

	watch(
		() => userStore.user?.id ?? null,
		(viewer) => store.setViewer(viewer),
		{ immediate: true },
	);
	watch(
		() => tileUrlSource(route),
		(source) => store.setSource(source),
		{ immediate: true },
	);
	watch(
		[() => renderedIds().join(","), () => store.generation],
		([key]) => {
			if (key !== "") {
				store.request(key.split(","));
			}
		},
		{ immediate: true },
	);
}
