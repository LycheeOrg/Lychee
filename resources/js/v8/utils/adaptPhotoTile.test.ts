/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { beforeEach, describe, expect, it, vi } from "vite-plus/test";
import { effect, reactive } from "vue";

const formatCalls = vi.hoisted(() => ({ count: 0 }));

vi.mock("@/v8/utils/phpDateFormat", async (importOriginal) => {
	const original = await importOriginal<typeof import("@/v8/utils/phpDateFormat")>();
	return {
		...original,
		formatServerDateTime: (...args: Parameters<typeof original.formatServerDateTime>) => {
			formatCalls.count++;
			return original.formatServerDateTime(...args);
		},
	};
});

import { adaptPhotoTile, linkNeighbours, type AdaptablePhotoTierTwo, type PhotoTileDateFormats } from "@/v8/utils/adaptPhotoTile";
import { formatServerDateTime } from "@/v8/utils/phpDateFormat";

const FORMATS: PhotoTileDateFormats = {
	date_format_photo_overlay: "M j, Y",
	date_format_sidebar_uploaded: "Y-m-d H:i",
	date_format_sidebar_taken_at: "j F Y, H:i",
};

function ratios(): AdaptablePhotoTierTwo {
	return {
		ids: ["a", "b", "c"],
		titles: ["A", "B", "C"],
		types: ["image/jpeg", "image/jpeg", "video/mp4"],
		ratios: [1.5, 0.66, 1.77],
		owner_ids: [1, 1, 1],
		is_highlighteds: [false, false, false],
		is_validateds: [true, true, true],
		is_videos: [false, false, true],
		is_raws: [false, false, false],
		is_live_photos: [false, false, false],
		is_360s: [false, false, false],
		taken_ats: ["2024-05-17T14:03:00+02:00", null, "2019-12-31T23:59:59+00:00"],
		created_ats: ["2024-06-01T08:00:00+00:00", "2023-01-02T03:04:05+00:00", "2020-01-01T00:00:00+00:00"],
		taken_at_orig_tzs: ["Europe/Paris", null, null],
	};
}

/** What `adaptPhotoTile()` computed eagerly before FR-103-04. */
function eagerDates(i: number) {
	const r = ratios();
	const takenAt = r.taken_ats[i];
	const takenAtTz = r.taken_at_orig_tzs[i];
	const formattedTakenAt = formatServerDateTime(takenAt, FORMATS.date_format_sidebar_taken_at, takenAtTz);
	return {
		created_at: formatServerDateTime(r.created_ats[i], FORMATS.date_format_sidebar_uploaded),
		taken_at: formattedTakenAt === "" ? null : formattedTakenAt,
		date_overlay:
			takenAt === null
				? formatServerDateTime(r.created_ats[i], FORMATS.date_format_photo_overlay)
				: formatServerDateTime(takenAt, FORMATS.date_format_photo_overlay, takenAtTz),
	};
}

beforeEach(() => {
	formatCalls.count = 0;
});

describe("adaptPhotoTile lazy dates (FR-103-04, S-103-12)", () => {
	it("formats no date until one is read", () => {
		const r = ratios();
		[0, 1, 2].forEach((i) => adaptPhotoTile(i, r, "album", FORMATS));
		expect(formatCalls.count).toBe(0);
	});

	it("reads the same strings as eager formatting", () => {
		const r = ratios();
		[0, 1, 2].forEach((i) => {
			const tile = adaptPhotoTile(i, r, "album", FORMATS);
			const expected = eagerDates(i);
			expect(tile.preformatted.created_at).toBe(expected.created_at);
			expect(tile.preformatted.taken_at).toBe(expected.taken_at);
			expect(tile.preformatted.date_overlay).toBe(expected.date_overlay);
		});
	});

	it("formats each date once", () => {
		const tile = adaptPhotoTile(0, ratios(), "album", FORMATS);
		void tile.preformatted.date_overlay;
		const after_first_read = formatCalls.count;
		void tile.preformatted.date_overlay;
		expect(formatCalls.count).toBe(after_first_read);
	});

	it("keeps an assigned value", () => {
		const tile = adaptPhotoTile(0, ratios(), "album", FORMATS);
		tile.preformatted.taken_at = "edited";
		tile.preformatted.date_overlay = "overlay";
		expect(tile.preformatted.taken_at).toBe("edited");
		expect(tile.preformatted.date_overlay).toBe("overlay");
	});

	it("spreads and serializes the formatted strings", () => {
		const tile = adaptPhotoTile(0, ratios(), "album", FORMATS);
		const expected = eagerDates(0);
		const copy = { ...tile.preformatted };
		expect(copy.created_at).toBe(expected.created_at);
		expect(copy.date_overlay).toBe(expected.date_overlay);
		expect(JSON.parse(JSON.stringify(tile)).preformatted.taken_at).toBe(expected.taken_at);
	});

	it("stays reactive through a Vue proxy", () => {
		const tile = reactive(adaptPhotoTile(0, ratios(), "album", FORMATS));
		let seen: string | null = null;
		effect(() => {
			seen = tile.preformatted.date_overlay;
		});
		expect(seen).toBe(eagerDates(0).date_overlay);
		tile.preformatted.date_overlay = "changed";
		expect(seen).toBe("changed");
	});
});

describe("linkNeighbours (FR-103-03, S-103-11)", () => {
	it("links each tile to its neighbours in array order", () => {
		const r = ratios();
		const tiles = [0, 1, 2].map((i) => adaptPhotoTile(i, r, "album", FORMATS));
		linkNeighbours(tiles);
		expect(tiles.map((t) => [t.previous_photo_id, t.next_photo_id])).toEqual([
			[null, "b"],
			["a", "c"],
			["b", null],
		]);
	});
});
