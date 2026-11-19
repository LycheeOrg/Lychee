/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { computed, toRaw, type ComputedRef } from "vue";
import { tileKey, type DateScrubberField } from "@/v8/utils/dateScrubber";

/**
 * The date scrubber key (day, or hour when `hourly`) of every photo, index-aligned with the
 * array, computed once per array, field and granularity: a new layout of the same photos
 * re-pairs these keys with its tops instead of recomputing them (FR-103-06). Reads the photos
 * raw, so it depends on the array, not on each photo.
 */
export function useTileKeys(
	photos: () => App.Http.Resources.Models.PhotoResource[],
	field: () => DateScrubberField | null,
	hourly: () => boolean,
): ComputedRef<(string | null)[]> {
	return computed(() => {
		const scrubberField = field();
		if (scrubberField === null) {
			return [];
		}
		const isHourly = hourly();
		return toRaw(photos()).map((photo) => tileKey(photo, scrubberField, isHourly));
	});
}
