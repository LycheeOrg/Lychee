/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { ref, type Ref } from "vue";
import type { MotionUrlLoader } from "@/v8/composables/photo/motionUrlLoader";

/** How long the pointer must rest on a thumbnail before its motion plays (FR-100-03). */
export const MOTION_HOVER_DELAY_MS = 250;

/** The fields of a photo tile that hover playback reads. */
export type MotionPhoto = {
	id: string;
	live_photo_url: string | null;
	precomputed: { is_livephoto: boolean };
};

/**
 * Whether hover playback suits this device: a fine pointer that can hover,
 * and no preference for reduced motion (NFR-100-04). Touch devices and
 * reduced-motion users get the badge only.
 */
export function canPlayMotionOnHover(): boolean {
	if (typeof window.matchMedia !== "function") {
		return false;
	}
	return window.matchMedia("(hover: hover) and (pointer: fine)").matches && !window.matchMedia("(prefers-reduced-motion: reduce)").matches;
}

/**
 * Plays the motion of a live photo thumbnail on hover (Feature 100).
 *
 * `videoSrc` stays `null` until the pointer has rested for
 * {@link MOTION_HOVER_DELAY_MS} and the photo's video URL is known; until
 * then no video element exists, so nothing is downloaded (NFR-100-01). A
 * missing URL is requested through `loader` as soon as the pointer enters,
 * so the request overlaps the delay.
 *
 * @param photo    getter of the tile's photo, read on every hover
 * @param loader   fills `live_photo_url` (FR-100-05); without it only already-known URLs play
 * @param canPlay  device check, {@link canPlayMotionOnHover} by default
 */
export function useMotionHover(
	photo: () => MotionPhoto,
	loader?: MotionUrlLoader,
	canPlay: () => boolean = canPlayMotionOnHover,
): {
	videoSrc: Ref<string | null>;
	isPlaying: Ref<boolean>;
	onEnter: () => void;
	onLeave: () => void;
	onPlaying: () => void;
	onError: () => void;
} {
	const videoSrc = ref<string | null>(null);
	// True from the first rendered frame, so the video can fade in over the
	// still instead of flashing an empty element.
	const isPlaying = ref(false);
	let timer: ReturnType<typeof setTimeout> | undefined;
	// Bumped on every enter and leave, so a late timer or URL from an
	// earlier hover cannot start the video.
	let hoverId = 0;
	// The details request in flight, reused when the pointer leaves and comes
	// back before it settles: the stores mark an id resolved only once its
	// response arrives, so asking again would send a duplicate request.
	let pending: { photoId: string; request: Promise<void> } | null = null;

	function urlReady(): Promise<void> {
		if (photo().live_photo_url !== null || loader === undefined) {
			return Promise.resolve();
		}

		const photoId = photo().id;
		if (pending?.photoId !== photoId) {
			const request: Promise<void> = loader([photoId])
				.catch(() => undefined)
				.finally(() => {
					// Settled (failed requests included): the next hover asks again.
					pending = pending?.request === request ? null : pending;
				});
			pending = { photoId, request };
		}
		return pending.request;
	}

	function onEnter(): void {
		if (!photo().precomputed.is_livephoto || !canPlay()) {
			return;
		}

		const current = ++hoverId;
		const delay = new Promise<void>((resolve) => {
			timer = setTimeout(resolve, MOTION_HOVER_DELAY_MS);
		});
		void Promise.all([urlReady(), delay]).then(() => {
			if (current === hoverId) {
				videoSrc.value = photo().live_photo_url;
			}
		});
	}

	function onLeave(): void {
		hoverId++;
		clearTimeout(timer);
		videoSrc.value = null;
		isPlaying.value = false;
	}

	function onPlaying(): void {
		isPlaying.value = true;
	}

	/** The browser cannot play the clip (e.g. HEVC in Firefox): keep the still. */
	function onError(): void {
		videoSrc.value = null;
		isPlaying.value = false;
	}

	return { videoSrc, isPlaying, onEnter, onLeave, onPlaying, onError };
}
