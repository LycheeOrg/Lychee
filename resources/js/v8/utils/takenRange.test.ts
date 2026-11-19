/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { describe, expect, it } from "vite-plus/test";
import { takenRangeLabel } from "@/v8/utils/takenRange";

// Keeps the day only, like the bulk edit table's own date format.
const day = (date: string): string => date.slice(0, 10);

describe("takenRangeLabel", () => {
	it("shows both dates", () => {
		expect(takenRangeLabel("2019-06-03 10:00:00", "2023-08-28 12:00:00", day)).toBe("2019-06-03 – 2023-08-28");
	});

	it("shows one date when both fall on the same day", () => {
		expect(takenRangeLabel("2019-06-03 10:00:00", "2019-06-03 18:30:00", day)).toBe("2019-06-03");
	});

	it("has no label without capture date", () => {
		expect(takenRangeLabel(null, null, day)).toBeNull();
	});

	it("shows the date that is set when the other is missing", () => {
		expect(takenRangeLabel(null, "2023-08-28 12:00:00", day)).toBe("2023-08-28");
	});
});
