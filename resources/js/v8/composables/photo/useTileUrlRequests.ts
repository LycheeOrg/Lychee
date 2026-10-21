/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { watch } from "vue";
import type { TileUrlLoader } from "@/v8/composables/photo/tileUrlLoader";

/**
 * Asks `loader` for the direct URLs of the tiles a virtual grid renders
 * (visible plus overscan, Feature 102, FR-102-06): whenever that id set
 * changes, and again after the loader is reset. A grid without a loader
 * (search) does nothing.
 */
export function useTileUrlRequests(loader: TileUrlLoader | undefined, renderedIds: () => string[]): void {
	if (loader === undefined) {
		return;
	}

	watch(
		[() => renderedIds().join(","), loader.version],
		([key]) => {
			if (key !== "") {
				loader.request(key.split(","));
			}
		},
		{ immediate: true },
	);
}
