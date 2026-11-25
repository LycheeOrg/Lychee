/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { describe, expect, it } from "vite-plus/test";
import { descriptionLength, isDescriptionTooLong } from "@/v8/utils/descriptionLength";

// Same unit as the server's StringRule (PHP `strlen`, UTF-8 bytes).
describe("descriptionLength", () => {
	it("counts nothing for an empty description", () => {
		expect(descriptionLength(null)).toBe(0);
		expect(descriptionLength(undefined)).toBe(0);
		expect(descriptionLength("")).toBe(0);
	});

	it("counts one per ASCII character", () => {
		expect(descriptionLength("abc")).toBe(3);
	});

	it("counts the UTF-8 bytes of other characters", () => {
		expect(descriptionLength("é")).toBe(2);
		expect(descriptionLength("日本")).toBe(6);
		expect(descriptionLength("😀")).toBe(4);
	});
});

describe("isDescriptionTooLong", () => {
	it("accepts a description at the limit", () => {
		expect(isDescriptionTooLong(1000, 1000)).toBe(false);
	});

	it("rejects a description over the limit", () => {
		expect(isDescriptionTooLong(1001, 1000)).toBe(true);
	});
});
