/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { beforeEach, describe, expect, it, vi } from "vite-plus/test";
import { ref } from "vue";

const keyCalls = vi.hoisted(() => ({ count: 0 }));

vi.mock("@/v8/utils/dateScrubber", async (importOriginal) => {
	const original = await importOriginal<typeof import("@/v8/utils/dateScrubber")>();
	return {
		...original,
		tileKey: (...args: Parameters<typeof original.tileKey>) => {
			keyCalls.count++;
			return original.tileKey(...args);
		},
	};
});

import { useTileKeys } from "@/v8/composables/photo/useTileKeys";
import type { DateScrubberField } from "@/v8/utils/dateScrubber";

type Photo = App.Http.Resources.Models.PhotoResource;

function photos(days: string[]): Photo[] {
	return days.map(
		(day, i) =>
			({
				id: `p${i}`,
				title: "",
				taken_at: `${day}T10:00:00+00:00`,
				taken_at_orig_tz: null,
				created_at: `${day}T10:00:00+00:00`,
			}) as unknown as Photo,
	);
}

beforeEach(() => {
	keyCalls.count = 0;
});

describe("useTileKeys (FR-103-06, S-103-15)", () => {
	it("computes each key once per photo array", () => {
		const list = ref(photos(["2024-01-02", "2024-01-02", "2024-01-03"]));
		const field = ref<DateScrubberField | null>("taken_at");
		const keys = useTileKeys(
			() => list.value,
			() => field.value,
			() => false,
		);

		expect(keys.value).toEqual(["2024-01-02", "2024-01-02", "2024-01-03"]);
		void keys.value;
		expect(keyCalls.count).toBe(3);

		list.value = photos(["2025-05-05"]);
		expect(keys.value).toEqual(["2025-05-05"]);
		expect(keyCalls.count).toBe(4);
	});

	it("has no keys without a scrubber field", () => {
		const keys = useTileKeys(
			() => photos(["2024-01-02"]),
			() => null,
			() => false,
		);
		expect(keys.value).toEqual([]);
	});
});
