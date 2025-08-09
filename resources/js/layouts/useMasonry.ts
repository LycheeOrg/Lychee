import { type RouteLocationNormalizedLoaded } from "vue-router";
import { ChildNodeWithDataStyle, Column } from "./types";
import { type TimelineData } from "./PhotoLayout";
import { getWidth } from "./getWidth";
import { getRatio } from "./ratio";

export function useMasonry(
	el: HTMLElement,
	target_width: number,
	grid_gap: number = 12,
	timelineData: TimelineData,
	route: RouteLocationNormalizedLoaded,
	align: "left" | "right",
) {
	const gridItems: ChildNodeWithDataStyle[] = [...el.childNodes].filter((gridItem) => gridItem.nodeType === 1);

	const max_width = getWidth(timelineData, route);
	const perChunk = Math.floor((max_width + grid_gap) / target_width);
	const remaining_space = max_width - perChunk * target_width - (perChunk - 1) * grid_gap;
	const spread = Math.ceil(remaining_space / perChunk);
	const grid_width = target_width + spread;

	// Compute ratio of each item.
	const ratio = getRatio(gridItems);

	const columns: Column[] = Array.from({ length: perChunk }, (_, idx) => {
		return { height: 0, left: (grid_gap + grid_width) * idx };
	});

	let idx = 0;
	gridItems.forEach(function (e, i) {
		idx = findSmallestIdx(columns);
		const column = columns[idx];
		const height = grid_width / ratio[i];
		e.style = e.style ?? {};
		e.style.top = column.height + "px";
		e.style.width = grid_width + "px";
		e.style.height = height + "px";
		e.style[align] = column.left + "px";
		column.height = column.height + height + grid_gap;

		// update
		columns[idx] = column;
	});

	const height = getMaxHeight(columns);
	el.style.height = height + "px";
}

function findSmallestIdx(columns: Column[]) {
	let idx = 0;
	let smallest = NaN;
	columns.forEach((col, i) => {
		if (isNaN(smallest)) {
			smallest = col.height;
			idx = i;
		} else if (col.height < smallest) {
			smallest = col.height;
			idx = i;
		}
	});
	return idx;
}

function getMaxHeight(columns: Column[]) {
	let highest = NaN;
	columns.forEach((col) => {
		if (isNaN(highest)) {
			highest = col.height;
		} else if (col.height > highest) {
			highest = col.height;
		}
	});

	return highest;
}
