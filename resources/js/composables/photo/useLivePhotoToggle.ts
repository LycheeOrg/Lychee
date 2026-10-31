/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { markRaw, onScopeDispose, ref, watch } from "vue";
import { usePhotoStore, type LivePhotoControls } from "@/stores/PhotoState";

/**
 * Play/stop of the lightbox Live Photo from the dock (FR-100-06), for one `PhotoBox`.
 *
 * While `isActive()` (main lightbox, live photo with a video URL) the box registers its
 * controls in the photo store, as it does for the zoom controls, and mirrors `isPlaying`
 * into `is_live_playing`. The box shows a looping `<video>` over the still while
 * `isPlaying` is true. The next photo's box mounts before this one leaves (slide
 * transition), so only our own registration is ever cleared.
 */
export function useLivePhotoToggle(isActive: () => boolean) {
	const photoStore = usePhotoStore();
	const isPlaying = ref(false);
	const controls: LivePhotoControls = markRaw({ toggle: () => (isPlaying.value = !isPlaying.value) });

	function stop() {
		isPlaying.value = false;
	}

	function release() {
		stop();
		if (photoStore.live_photo_controls === controls) {
			photoStore.live_photo_controls = undefined;
			photoStore.is_live_playing = false;
		}
	}

	watch(
		isActive,
		(active) => {
			if (!active) {
				release();
				return;
			}
			stop();
			photoStore.live_photo_controls = controls;
			photoStore.is_live_playing = false;
		},
		{ immediate: true },
	);

	watch(isPlaying, (playing) => {
		if (photoStore.live_photo_controls === controls) {
			photoStore.is_live_playing = playing;
		}
	});

	onScopeDispose(release);

	return { isPlaying, stop };
}
