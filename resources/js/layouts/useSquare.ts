import { type RouteLocationNormalizedLoaded } from "vue-router";
import { getWidth } from "./getWidth";
import { TimelineData } from "./PhotoLayout";
import { ChildNodeWithDataStyle, Column } from "./types";

export function useSquare(
	el: HTMLElement,
	target_width_height: number,
	grid_gap: number = 12,
	timelineData: TimelineData,
	route: RouteLocationNormalizedLoaded,
	align: "left" | "right",
) {
	const gridItems: ChildNodeWithDataStyle[] = [...el.childNodes].filter((gridItem) => gridItem.nodeType === 1);

	const max_width = getWidth(timelineData, route);
	const perChunk = Math.floor((max_width + grid_gap) / target_width_height);
	const remaining_space = max_width - perChunk * target_width_height - (perChunk - 1) * grid_gap;
	const spread = Math.ceil(remaining_space / perChunk);
	const grid_width = target_width_height + spread;

	const columns: Column[] = Array.from({ length: perChunk }, (_, idx) => {
		return { height: 0, left: (grid_gap + grid_width) * idx };
	});

	let idx = 0;
	gridItems.forEach(function (e) {
		if (idx % perChunk === 0) {
			const newTop = Math.max(...columns.map((column) => column.height));
			columns.forEach((column) => (column.height = newTop));
		}

		const column = columns[idx];
		e.style = e.style ?? {};
		e.style.top = column.height + "px";
		e.style.width = grid_width + "px";
		e.style.height = grid_width + "px";
		e.style[align] = column.left + "px";
		column.height = column.height + grid_width + grid_gap;

		// update
		columns[idx] = column;
		idx = (idx + 1) % perChunk;
	});

	const height = getMaxHeight(columns);
	el.style.height = height + "px";
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
