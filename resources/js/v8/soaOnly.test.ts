/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { describe, expect, it } from "vite-plus/test";

/**
 * Feature 109 (FR-109-02): v8 always runs its listings on the v3
 * struct-of-arrays endpoints, so no v8 source may branch on the switch the
 * shared stores keep for v7. `isPhotoSoaActive` is not listed: in v8 it is
 * false only while an album has a tag or person filter (FR-109-06).
 */
const FORBIDDEN = ["is_struct_of_array_enabled", "isTimelineSoaActive", "isSearchSoaActive", "isMapSoaActive", "isFlowSoaActive"];

const sources = import.meta.glob(["./**/*.ts", "./**/*.vue", "!./**/*.test.ts"], { query: "?raw", import: "default", eager: true }) as Record<
	string,
	string
>;

describe("v8 sources", () => {
	it("are read", () => {
		expect(Object.keys(sources).length).toBeGreaterThan(100);
	});

	it.each(FORBIDDEN)("never read %s", (name) => {
		const pattern = new RegExp(`\\b${name}\\b`);
		const offenders = Object.entries(sources)
			.filter(([, source]) => pattern.test(source))
			.map(([path]) => path)
			.sort();

		expect(offenders).toEqual([]);
	});
});
