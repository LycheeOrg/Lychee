/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { describe, expect, it } from "vite-plus/test";
import { DEFAULT_VIDEO_ASPECT, isPlayerKey, videoAspect, videoBoxStyle } from "@/v8/utils/videoPlayer";

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
		expect(isPlayerKey({ defaultPrevented: true, key: "ArrowRight" })).toBe(true);
		expect(isPlayerKey({ defaultPrevented: true, key: "f" })).toBe(true);
	});

	it("keeps Space and Enter, which activate the focused control", () => {
		expect(isPlayerKey({ defaultPrevented: false, key: " " })).toBe(true);
		expect(isPlayerKey({ defaultPrevented: false, key: "Enter" })).toBe(true);
	});

	it("lets the other keys reach the gallery shortcuts", () => {
		expect(isPlayerKey({ defaultPrevented: false, key: "Escape" })).toBe(false);
		expect(isPlayerKey({ defaultPrevented: false, key: "o" })).toBe(false);
	});
});
