import { Column } from "./types";

export function useSquare(el: HTMLElement, target_width_height: number, grid_gap: number = 12) {
	// @ts-expect-error
	const gridItems: ChildNodeWithDataStyle[] = [...el.childNodes].filter((gridItem) => gridItem.nodeType === 1);

	const max_width = parseInt(getComputedStyle(el).width);
	const usable_width = max_width;
	const perChunk = Math.floor((usable_width + grid_gap) / target_width_height);
	const remaining_space = usable_width - perChunk * target_width_height - (perChunk - 1) * grid_gap;
	const spread = Math.ceil(remaining_space / perChunk);
	const grid_width = target_width_height + spread;

	let columns: Column[] = Array.from({ length: perChunk }, (_, idx) => {
		return { height: 0, left: (grid_gap + grid_width) * idx };
	});

	let idx = 0;
	gridItems.forEach(function (e, i) {
		if (idx % perChunk === 0) {
			const newTop = Math.max(...columns.map((column) => column.height));
			columns.forEach((column) => (column.height = newTop));
		}

		let column = columns[idx];
		e.style.top = column.height + "px";
		e.style.width = grid_width + "px";
		e.style.height = grid_width + "px";
		e.style.left = column.left + "px";
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
