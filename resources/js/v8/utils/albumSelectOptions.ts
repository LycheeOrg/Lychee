/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { type SelectOption, sortingOrdersOptions } from "@/config/constants";
import type { TileUrls } from "@/v8/composables/photo/tileUrlLoader";

/**
 * Option lists of the v8 album edit drawer (Feature 105). A stored `null`
 * means the album follows the gallery setting; it is shown and checked as
 * the "Default" entry instead of an empty select (FR-105-02).
 */
export const DEFAULT_OPTION: SelectOption<null> = { value: null, label: "gallery.album.properties.default" };

const ASCENDING = sortingOrdersOptions[0];

export function withDefaultOption<T>(options: SelectOption<T>[]): SelectOption<T | null>[] {
	return [DEFAULT_OPTION, ...options];
}

export function isDefaultOption(option: SelectOption<unknown> | undefined): boolean {
	return option?.value === null;
}

/** The entry holding the stored `value`; an unset value finds the "Default" entry of a list that has one. */
export function optionFor<T>(options: SelectOption<T>[], value: string | null | undefined): SelectOption<T> | undefined {
	return options.find((option) => option.value === (value ?? null));
}

/**
 * Timeline entries: "Default" (`null`, follows `timeline_*_enabled`) first,
 * then the `default` value, which turns the timeline on with the gallery
 * granularity, labelled "Enabled" (FR-105-06).
 */
export function timelineSelectOptions<T extends string>(options: SelectOption<T>[]): SelectOption<T | null>[] {
	return withDefaultOption(
		options.map((option) => (option.value === "default" ? { value: option.value, label: "gallery.album.properties.timeline_enabled" } : option)),
	);
}

/**
 * The order to pair with a newly picked sort column: "Default" with a
 * "Default" column, Ascending for a column without order, otherwise the
 * current order. The API requires both or neither (FR-105-03).
 */
export function sortingOrderFor(
	column: SelectOption<unknown> | undefined,
	order: SelectOption<App.Enum.OrderSortingType | null> | undefined,
): SelectOption<App.Enum.OrderSortingType | null> | undefined {
	if (isDefaultOption(column)) {
		return DEFAULT_OPTION;
	}
	return isDefaultOption(order) ? ASCENDING : order;
}

/**
 * The order stored with `column` when a column alone is picked (bulk album
 * edit): none for "Default", Ascending when the album has no order yet,
 * otherwise its order (FR-105-10).
 */
export function sortingOrderValueFor(column: string | null, order: string | null): string | null {
	if (column === null) {
		return null;
	}
	return order ?? "ASC";
}

/**
 * `Thumb` props of a header or cover entry (FR-105-08): the photo's loaded
 * thumb URL, else its tile URL (a struct-of-arrays photo carries no size
 * variants until its details load); `pending` while the tile URL store has
 * not answered, `/Asset` when it has no URL.
 */
export function optionThumbProps(
	thumb: string | null | undefined,
	urls: TileUrls | null | undefined,
): { directUrl: string | null; pending: boolean } {
	if (typeof thumb === "string") {
		return { directUrl: thumb, pending: false };
	}
	return { directUrl: urls?.list ?? null, pending: urls === undefined };
}
