/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

export type ScreenDensity = 1 | 2;

/**
 * Density grid tiles are fetched for: `small` at 1, `small2x` at 2
 * (Feature 102, FR-102-06). `devicePixelRatio` doesn't change during a
 * session, so callers read it once.
 */
export function screenDensity(): ScreenDensity {
	return window.devicePixelRatio > 1 ? 2 : 1;
}
