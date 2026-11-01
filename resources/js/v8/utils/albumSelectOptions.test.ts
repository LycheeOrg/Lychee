/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { describe, expect, it } from "vite-plus/test";
import { photoSortingColumnsOptions, sortingOrdersOptions, timelinePhotoGranularityOptions } from "@/config/constants";
import {
	DEFAULT_OPTION,
	isDefaultOption,
	optionFor,
	optionThumbProps,
	sortingOrderFor,
	sortingOrderValueFor,
	timelineSelectOptions,
	withDefaultOption,
} from "@/v8/utils/albumSelectOptions";
import { NO_TILE_URLS } from "@/v8/composables/photo/tileUrlLoader";

const ascending = sortingOrdersOptions[0];
const descending = sortingOrdersOptions[1];
const title = photoSortingColumnsOptions.find((option) => option.value === "title");

describe("withDefaultOption", () => {
	it("puts the Default entry first", () => {
		const options = withDefaultOption(photoSortingColumnsOptions);
		expect(options[0]).toBe(DEFAULT_OPTION);
		expect(options.slice(1)).toEqual(photoSortingColumnsOptions);
	});
});

describe("optionFor", () => {
	const options = withDefaultOption(photoSortingColumnsOptions);

	it("maps an unset value to Default", () => {
		expect(optionFor(options, null)).toBe(DEFAULT_OPTION);
		expect(optionFor(options, undefined)).toBe(DEFAULT_OPTION);
	});

	it("finds a stored value", () => {
		expect(optionFor(options, "title")).toBe(title);
	});

	it("finds nothing for a value missing from the list", () => {
		expect(optionFor(options, "unknown")).toBeUndefined();
		expect(optionFor(sortingOrdersOptions, null)).toBeUndefined();
	});
});

describe("timelineSelectOptions", () => {
	const options = timelineSelectOptions(timelinePhotoGranularityOptions);

	it("starts with Default and labels the default value Enabled", () => {
		expect(options[0]).toBe(DEFAULT_OPTION);
		expect(options[1]).toEqual({ value: "default", label: "gallery.album.properties.timeline_enabled" });
		expect(options.slice(2)).toEqual(timelinePhotoGranularityOptions.slice(1));
	});

	it("shows a stored default as the Enabled entry", () => {
		expect(optionFor(options, "default")).toEqual(options[1]);
	});

	it("keeps a stored granularity missing from the list shown", () => {
		const visible = timelineSelectOptions(timelinePhotoGranularityOptions.slice(0, 2));
		expect(visible.map((option) => option.value)).toEqual([null, "default", "disabled"]);
		expect(optionFor(visible, "year")).toBeUndefined();
		expect(optionFor(options, "year")).toEqual({ value: "year", label: "gallery.timeline.year" });
	});
});

describe("sortingOrderFor", () => {
	it("follows a Default column", () => {
		expect(sortingOrderFor(DEFAULT_OPTION, descending)).toBe(DEFAULT_OPTION);
	});

	it("sets Ascending when a column is picked without order", () => {
		expect(sortingOrderFor(title, DEFAULT_OPTION)).toBe(ascending);
	});

	it("keeps the order of a set column", () => {
		expect(sortingOrderFor(title, descending)).toBe(descending);
	});
});

describe("isDefaultOption", () => {
	it("is true only for an entry with a null value", () => {
		expect(isDefaultOption(DEFAULT_OPTION)).toBe(true);
		expect(isDefaultOption(ascending)).toBe(false);
		expect(isDefaultOption(undefined)).toBe(false);
	});
});

describe("optionThumbProps", () => {
	it("uses a loaded thumb URL", () => {
		expect(optionThumbProps("/uploads/thumb.jpg", undefined)).toEqual({ directUrl: "/uploads/thumb.jpg", pending: false });
	});

	it("uses the tile URL of a photo without size variants", () => {
		expect(optionThumbProps(undefined, { grid: "/uploads/small.jpg", list: "/uploads/thumb.jpg" })).toEqual({
			directUrl: "/uploads/thumb.jpg",
			pending: false,
		});
	});

	it("waits while the tile URL is asked for", () => {
		expect(optionThumbProps(null, undefined)).toEqual({ directUrl: null, pending: true });
	});

	it("falls back to /Asset without tile URL", () => {
		expect(optionThumbProps(null, null)).toEqual({ directUrl: null, pending: false });
		expect(optionThumbProps(null, NO_TILE_URLS)).toEqual({ directUrl: null, pending: false });
	});
});

describe("sortingOrderValueFor", () => {
	it("clears the order of a Default column", () => {
		expect(sortingOrderValueFor(null, "DESC")).toBeNull();
	});

	it("sets Ascending for a column without order", () => {
		expect(sortingOrderValueFor("title", null)).toBe("ASC");
	});

	it("keeps the order of a set column", () => {
		expect(sortingOrderValueFor("title", "DESC")).toBe("DESC");
	});
});
