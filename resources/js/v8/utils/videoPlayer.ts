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
 * Keys of the default skin's toggle hotkeys (pause, mute, fullscreen, subtitles,
 * picture-in-picture). Video.js ignores their auto-repeat without preventing it.
 */
const PLAYER_TOGGLE_KEYS = new Set([" ", "k", "m", "f", "c", "i"]);

/**
 * Whether a keydown from inside the player belongs to it: one of its hotkeys
 * handled it (they call `preventDefault`), Space / Enter activate the focused
 * control, or a held toggle key repeats. Those must not also reach the gallery
 * shortcuts (arrows would change photo, Space would start the slideshow, a held
 * `m` would open Move).
 */
export function isPlayerKey(event: Pick<KeyboardEvent, "defaultPrevented" | "key" | "repeat">): boolean {
	return event.defaultPrevented || event.key === " " || event.key === "Enter" || (event.repeat && PLAYER_TOGGLE_KEYS.has(event.key.toLowerCase()));
}

/**
 * Whether an event started on the player's controls (time and volume sliders,
 * buttons, menus). The skin's shadow root is open, so its composed path holds
 * them. A drag there belongs to the control, not to the swipe navigation.
 */
export function isOnPlayerControls(path: readonly EventTarget[]): boolean {
	return path.some((target) => target instanceof Element && target.localName === "media-controls");
}
