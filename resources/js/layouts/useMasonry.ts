import { Column } from "./types";

export function useMasonry(el: HTMLElement, target_width: number, grid_gap: number = 12) {
	// @ts-expect-error
	const gridItems: ChildNodeWithDataStyle[] = [...el.childNodes].filter((gridItem) => gridItem.nodeType === 1);

	const max_width = parseInt(getComputedStyle(el).width);
	const usable_width = max_width;
	const perChunk = Math.floor((usable_width + grid_gap) / target_width);
	const remaining_space = usable_width - perChunk * target_width - (perChunk - 1) * grid_gap;
	const spread = Math.ceil(remaining_space / perChunk);
	const grid_width = target_width + spread;

	// Compute ratio of each item.
	const ratio = gridItems.map(function (_photo) {
		const height = _photo.dataset.height;
		const width = _photo.dataset.width;
		return height > 0 ? width / height : 1;
	});

	let columns = Array.from({ length: perChunk }, (_, idx) => {
		return { height: 0, left: (grid_gap + grid_width) * idx };
	});

	let idx = 0;
	gridItems.forEach(function (e, i) {
		idx = findSmallestIdx(columns);
		let column = columns[idx];
		const height = grid_width / ratio[i];
		e.style.top = column.height + "px";
		e.style.width = grid_width + "px";
		e.style.height = height + "px";
		e.style.left = column.left + "px";
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
