/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * Pure helpers of the v8 lightbox video player (`VideoView.vue`, Video.js 10).
 */

/** Ratio used until the video's own dimensions are known. */
export const DEFAULT_VIDEO_ASPECT = 16 / 9;

/** Width over height, `undefined` when either dimension is missing (a video stored without metadata has 0). */
export function videoAspect(width: number | null | undefined, height: number | null | undefined): number | undefined {
	if (width === null || width === undefined || height === null || height === undefined || width <= 0 || height <= 0) {
		return undefined;
	}
	return width / height;
}

/**
 * Size of the player: the largest box of the video's ratio that fits its size
 * container (`container-type: size`), so the controls span the picture and not
 * the letterbox around it.
 */
export function videoBoxStyle(aspect: number | undefined): { aspectRatio: string; width: string } {
	const ratio = aspect ?? DEFAULT_VIDEO_ASPECT;
	return { aspectRatio: `${ratio}`, width: `min(100cqw, 100cqh * ${ratio})` };
}

/**
 * Whether a keydown from inside the player belongs to it: one of its hotkeys
 * handled it (they call `preventDefault`), or Space / Enter activate the focused
 * control. Those must not also reach the gallery shortcuts (arrows would change
 * photo, Space would start the slideshow).
 */
export function isPlayerKey(event: Pick<KeyboardEvent, "defaultPrevented" | "key">): boolean {
	return event.defaultPrevented || event.key === " " || event.key === "Enter";
}
