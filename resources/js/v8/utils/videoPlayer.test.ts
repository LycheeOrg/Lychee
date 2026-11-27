/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { describe, expect, it } from "vite-plus/test";
import { DEFAULT_VIDEO_ASPECT, isOnPlayerControls, isPlayerKey, videoAspect, videoBoxStyle } from "@/v8/utils/videoPlayer";

describe("videoAspect", () => {
	it("divides the width by the height", () => {
		expect(videoAspect(1920, 1080)).toBeCloseTo(16 / 9);
		expect(videoAspect(1080, 1920)).toBeCloseTo(9 / 16);
	});

	it("is unknown without both dimensions", () => {
		expect(videoAspect(0, 1080)).toBeUndefined();
		expect(videoAspect(1920, 0)).toBeUndefined();
		expect(videoAspect(undefined, 1080)).toBeUndefined();
		expect(videoAspect(1920, null)).toBeUndefined();
	});
});

describe("videoBoxStyle", () => {
	it("fits the box of that ratio in its size container", () => {
		expect(videoBoxStyle(2)).toEqual({ aspectRatio: "2", width: "min(100cqw, 100cqh * 2)" });
	});

	it("falls back to 16:9 while the ratio is unknown", () => {
		expect(videoBoxStyle(undefined)).toEqual(videoBoxStyle(DEFAULT_VIDEO_ASPECT));
	});
});

describe("isPlayerKey", () => {
	it("keeps the keys the player handled", () => {
		expect(isPlayerKey({ defaultPrevented: true, key: "ArrowRight", repeat: false })).toBe(true);
		expect(isPlayerKey({ defaultPrevented: true, key: "f", repeat: false })).toBe(true);
	});

	it("keeps Space and Enter, which activate the focused control", () => {
		expect(isPlayerKey({ defaultPrevented: false, key: " ", repeat: false })).toBe(true);
		expect(isPlayerKey({ defaultPrevented: false, key: "Enter", repeat: false })).toBe(true);
	});

	// Video.js skips the repeats of its toggle hotkeys without preventing them: holding `m` must not open Move.
	it("keeps the repeats of the player's toggle keys", () => {
		for (const key of ["k", "m", "f", "c", "i", "M"]) {
			expect(isPlayerKey({ defaultPrevented: false, key, repeat: true })).toBe(true);
		}
	});

	it("lets the other keys reach the gallery shortcuts", () => {
		expect(isPlayerKey({ defaultPrevented: false, key: "Escape", repeat: false })).toBe(false);
		expect(isPlayerKey({ defaultPrevented: false, key: "o", repeat: false })).toBe(false);
		expect(isPlayerKey({ defaultPrevented: false, key: "o", repeat: true })).toBe(false);
		expect(isPlayerKey({ defaultPrevented: false, key: "m", repeat: false })).toBe(false);
	});
});

describe("isOnPlayerControls", () => {
	/** Path of a touch on `inner`, inside an open shadow root like the one of `<video-skin>`. */
	function touchPath(inner: string, outer: string): EventTarget[] {
		const host = document.createElement("div");
		const wrapper = document.createElement(outer);
		const target = document.createElement(inner);
		wrapper.append(target);
		host.attachShadow({ mode: "open" }).append(wrapper);
		document.body.append(host);
		let path: EventTarget[] = [];
		host.addEventListener("touchstart", (event) => (path = event.composedPath()));
		target.dispatchEvent(new Event("touchstart", { bubbles: true, composed: true }));
		host.remove();
		return path;
	}

	it("finds the controls through the skin's shadow root", () => {
		expect(isOnPlayerControls(touchPath("media-time-slider", "media-controls"))).toBe(true);
	});

	it("ignores a touch outside the controls", () => {
		expect(isOnPlayerControls(touchPath("video", "media-container"))).toBe(false);
	});
});
