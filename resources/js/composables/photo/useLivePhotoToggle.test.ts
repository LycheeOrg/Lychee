/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { beforeEach, describe, expect, it } from "vite-plus/test";
import { createPinia, setActivePinia } from "pinia";
import { effectScope, nextTick, ref } from "vue";
import { usePhotoStore } from "@/stores/PhotoState";
import { useLivePhotoToggle } from "@/composables/photo/useLivePhotoToggle";

/** One `PhotoBox`: its own scope (disposed on unmount) and its "is a dock-controlled live photo" flag. */
function mountBox(isActive = true) {
	const active = ref(isActive);
	const scope = effectScope();
	const live = scope.run(() => useLivePhotoToggle(() => active.value))!;
	return { live, active, unmount: () => scope.stop() };
}

beforeEach(() => {
	setActivePinia(createPinia());
});

describe("useLivePhotoToggle (FR-100-06)", () => {
	it("registers dock controls for a live photo and toggles play/stop (S-100-25)", async () => {
		const store = usePhotoStore();
		const { live } = mountBox();

		expect(store.live_photo_controls).toBeDefined();
		expect(live.isPlaying.value).toBe(false);

		store.live_photo_controls!.toggle();
		await nextTick();
		expect(live.isPlaying.value).toBe(true);
		expect(store.is_live_playing).toBe(true);

		store.live_photo_controls!.toggle();
		await nextTick();
		expect(live.isPlaying.value).toBe(false);
		expect(store.is_live_playing).toBe(false);
	});

	it("registers nothing when the box is not a dock-controlled live photo (S-100-26)", () => {
		const store = usePhotoStore();
		mountBox(false);

		expect(store.live_photo_controls).toBeUndefined();
	});

	it("stop() shows the still again, e.g. when the clip fails to load", async () => {
		const store = usePhotoStore();
		const { live } = mountBox();
		store.live_photo_controls!.toggle();

		live.stop();
		await nextTick();

		expect(live.isPlaying.value).toBe(false);
		expect(store.is_live_playing).toBe(false);
	});

	it("releases its controls when it stops being a live photo", async () => {
		const store = usePhotoStore();
		const { active } = mountBox();
		store.live_photo_controls!.toggle();

		active.value = false;
		await nextTick();

		expect(store.live_photo_controls).toBeUndefined();
		expect(store.is_live_playing).toBe(false);
	});

	it("the next photo starts stopped, and the leaving box does not clear its registration", async () => {
		const store = usePhotoStore();
		const leaving = mountBox();
		store.live_photo_controls!.toggle();
		await nextTick();

		// Slide transition: the next box mounts before the previous one unmounts.
		const next = mountBox();
		await nextTick();
		expect(store.is_live_playing).toBe(false);

		leaving.unmount();
		expect(store.live_photo_controls).toBeDefined();

		store.live_photo_controls!.toggle();
		await nextTick();
		expect(next.live.isPlaying.value).toBe(true);
		expect(leaving.live.isPlaying.value).toBe(false);

		next.unmount();
		expect(store.live_photo_controls).toBeUndefined();
	});
});
