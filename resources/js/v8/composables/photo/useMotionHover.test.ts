/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

// @vitest-environment jsdom
import { afterEach, beforeEach, describe, expect, it, vi } from "vite-plus/test";
import { reactive } from "vue";
import { canPlayMotionOnHover, MOTION_HOVER_DELAY_MS, useMotionHover, type MotionPhoto } from "@/v8/composables/photo/useMotionHover";

const URL = "https://lychee.test/uploads/original/ab/cd/photo.mov";

function livePhoto(url: string | null = URL): MotionPhoto {
	return reactive({ id: "p1", live_photo_url: url, precomputed: { is_livephoto: true } });
}

/** A loader that sets the URL when `resolve()` is called, like `mergePhotoDetail()` does. */
function deferredLoader(photo: MotionPhoto) {
	let resolve: () => void = () => undefined;
	let reject: (e: Error) => void = () => undefined;
	const loader = vi.fn(
		() =>
			new Promise<void>((res, rej) => {
				resolve = () => {
					photo.live_photo_url = URL;
					res();
				};
				reject = rej;
			}),
	);
	return { loader, resolve: () => resolve(), reject: () => reject(new Error("offline")) };
}

beforeEach(() => {
	vi.useFakeTimers();
});

afterEach(() => {
	vi.useRealTimers();
});

describe("useMotionHover: hover intent (S-098-15, S-098-16)", () => {
	it("plays the loaded URL after the hover delay, not before", async () => {
		const hover = useMotionHover(
			() => livePhoto(),
			undefined,
			() => true,
		);

		hover.onEnter();
		await vi.advanceTimersByTimeAsync(MOTION_HOVER_DELAY_MS - 1);
		expect(hover.videoSrc.value).toBeNull();

		await vi.advanceTimersByTimeAsync(1);
		expect(hover.videoSrc.value).toBe(URL);
	});

	it("never plays when the pointer leaves before the delay", async () => {
		const hover = useMotionHover(
			() => livePhoto(),
			undefined,
			() => true,
		);

		hover.onEnter();
		await vi.advanceTimersByTimeAsync(MOTION_HOVER_DELAY_MS - 50);
		hover.onLeave();
		await vi.advanceTimersByTimeAsync(1000);

		expect(hover.videoSrc.value).toBeNull();
	});

	it("unloads the video when the pointer leaves", async () => {
		const hover = useMotionHover(
			() => livePhoto(),
			undefined,
			() => true,
		);

		hover.onEnter();
		await vi.advanceTimersByTimeAsync(MOTION_HOVER_DELAY_MS);
		hover.onLeave();

		expect(hover.videoSrc.value).toBeNull();
	});

	it("restarts the delay when the pointer comes back quickly", async () => {
		const hover = useMotionHover(
			() => livePhoto(),
			undefined,
			() => true,
		);

		hover.onEnter();
		await vi.advanceTimersByTimeAsync(200);
		hover.onLeave();
		hover.onEnter();
		await vi.advanceTimersByTimeAsync(100);
		expect(hover.videoSrc.value).toBeNull();

		await vi.advanceTimersByTimeAsync(MOTION_HOVER_DELAY_MS - 100);
		expect(hover.videoSrc.value).toBe(URL);
	});
});

describe("useMotionHover: gates (S-098-17)", () => {
	it("ignores photos that are not live photos", async () => {
		const photo = reactive({ id: "p1", live_photo_url: null, precomputed: { is_livephoto: false } });
		const loader = vi.fn(() => Promise.resolve());
		const hover = useMotionHover(
			() => photo,
			loader,
			() => true,
		);

		hover.onEnter();
		await vi.advanceTimersByTimeAsync(1000);

		expect(hover.videoSrc.value).toBeNull();
		expect(loader).not.toHaveBeenCalled();
	});

	it("does nothing when the device cannot hover or prefers reduced motion", async () => {
		const loader = vi.fn(() => Promise.resolve());
		const hover = useMotionHover(
			() => livePhoto(null),
			loader,
			() => false,
		);

		hover.onEnter();
		await vi.advanceTimersByTimeAsync(1000);

		expect(hover.videoSrc.value).toBeNull();
		expect(loader).not.toHaveBeenCalled();
	});
});

describe("useMotionHover: fade-in state", () => {
	it("is playing from the first frame until the pointer leaves", async () => {
		const hover = useMotionHover(
			() => livePhoto(),
			undefined,
			() => true,
		);

		hover.onEnter();
		await vi.advanceTimersByTimeAsync(MOTION_HOVER_DELAY_MS);
		expect(hover.isPlaying.value).toBe(false);

		hover.onPlaying();
		expect(hover.isPlaying.value).toBe(true);

		hover.onLeave();
		expect(hover.isPlaying.value).toBe(false);
	});

	it("stops being playing on error", async () => {
		const hover = useMotionHover(
			() => livePhoto(),
			undefined,
			() => true,
		);

		hover.onEnter();
		await vi.advanceTimersByTimeAsync(MOTION_HOVER_DELAY_MS);
		hover.onPlaying();
		hover.onError();

		expect(hover.isPlaying.value).toBe(false);
	});
});

describe("useMotionHover: playback failure (S-098-18)", () => {
	it("drops the video on error and tries again on the next hover", async () => {
		const hover = useMotionHover(
			() => livePhoto(),
			undefined,
			() => true,
		);

		hover.onEnter();
		await vi.advanceTimersByTimeAsync(MOTION_HOVER_DELAY_MS);
		hover.onError();
		expect(hover.videoSrc.value).toBeNull();

		hover.onLeave();
		hover.onEnter();
		await vi.advanceTimersByTimeAsync(MOTION_HOVER_DELAY_MS);
		expect(hover.videoSrc.value).toBe(URL);
	});
});

describe("useMotionHover: URL loading (S-098-19, S-098-20)", () => {
	it("does not call the loader when the URL is already known", async () => {
		const loader = vi.fn(() => Promise.resolve());
		const hover = useMotionHover(
			() => livePhoto(),
			loader,
			() => true,
		);

		hover.onEnter();
		await vi.advanceTimersByTimeAsync(MOTION_HOVER_DELAY_MS);

		expect(loader).not.toHaveBeenCalled();
		expect(hover.videoSrc.value).toBe(URL);
	});

	it("loads a missing URL once, on enter, and waits for it past the delay", async () => {
		const photo = livePhoto(null);
		const { loader, resolve } = deferredLoader(photo);
		const hover = useMotionHover(
			() => photo,
			loader,
			() => true,
		);

		hover.onEnter();
		expect(loader).toHaveBeenCalledTimes(1);
		expect(loader).toHaveBeenCalledWith(["p1"]);

		await vi.advanceTimersByTimeAsync(MOTION_HOVER_DELAY_MS + 150);
		expect(hover.videoSrc.value).toBeNull();

		resolve();
		await vi.advanceTimersByTimeAsync(0);
		expect(hover.videoSrc.value).toBe(URL);
	});

	it("still waits for the delay when the URL arrives first", async () => {
		const photo = livePhoto(null);
		const { loader, resolve } = deferredLoader(photo);
		const hover = useMotionHover(
			() => photo,
			loader,
			() => true,
		);

		hover.onEnter();
		resolve();
		await vi.advanceTimersByTimeAsync(10);
		expect(hover.videoSrc.value).toBeNull();

		await vi.advanceTimersByTimeAsync(MOTION_HOVER_DELAY_MS);
		expect(hover.videoSrc.value).toBe(URL);
	});

	it("does not play a URL that arrives after the pointer left", async () => {
		const photo = livePhoto(null);
		const { loader, resolve } = deferredLoader(photo);
		const hover = useMotionHover(
			() => photo,
			loader,
			() => true,
		);

		hover.onEnter();
		await vi.advanceTimersByTimeAsync(MOTION_HOVER_DELAY_MS);
		hover.onLeave();
		resolve();
		await vi.advanceTimersByTimeAsync(0);

		expect(hover.videoSrc.value).toBeNull();
	});

	it("swallows a failed load and stays on the still", async () => {
		const photo = livePhoto(null);
		const { loader, reject } = deferredLoader(photo);
		const hover = useMotionHover(
			() => photo,
			loader,
			() => true,
		);

		hover.onEnter();
		reject();
		await vi.advanceTimersByTimeAsync(MOTION_HOVER_DELAY_MS);

		expect(hover.videoSrc.value).toBeNull();
	});

	it("plays nothing without a loader when the URL is unknown", async () => {
		const hover = useMotionHover(
			() => livePhoto(null),
			undefined,
			() => true,
		);

		hover.onEnter();
		await vi.advanceTimersByTimeAsync(1000);

		expect(hover.videoSrc.value).toBeNull();
	});
});

describe("canPlayMotionOnHover", () => {
	function stubMedia(matches: Record<string, boolean>) {
		vi.stubGlobal("matchMedia", (query: string) => ({ matches: matches[query] ?? false }));
	}

	afterEach(() => {
		vi.unstubAllGlobals();
	});

	it("allows a fine pointer that can hover", () => {
		stubMedia({ "(hover: hover) and (pointer: fine)": true });
		expect(canPlayMotionOnHover()).toBe(true);
	});

	it("refuses touch-only devices", () => {
		stubMedia({});
		expect(canPlayMotionOnHover()).toBe(false);
	});

	it("refuses when reduced motion is preferred", () => {
		stubMedia({ "(hover: hover) and (pointer: fine)": true, "(prefers-reduced-motion: reduce)": true });
		expect(canPlayMotionOnHover()).toBe(false);
	});
});
