/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import type { InjectionKey } from "vue";

/**
 * Loads the tier-3 details (Feature 064) of the given photos, which fills
 * their `live_photo_url` in place through `mergePhotoDetail()`.
 *
 * Grid tiles built from the v3 struct-of-arrays payloads only know
 * `is_livephoto`; each view that renders photo thumbnails provides its
 * store's deduplicated details loader under {@link MOTION_URL_LOADER}, so a
 * thumbnail can fetch its video URL on hover (Feature 100, FR-100-05).
 */
export type MotionUrlLoader = (photoIds: string[]) => Promise<void>;

export const MOTION_URL_LOADER: InjectionKey<MotionUrlLoader> = Symbol("motionUrlLoader");
