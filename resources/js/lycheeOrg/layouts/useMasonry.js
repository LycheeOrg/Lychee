export default { useMasonry };

export function useMasonry(el) {
	const gridItems = [...el.childNodes].filter((gridItem) => gridItem.nodeType === 1);
	const grid_widths = getComputedStyle(el).gridTemplateColumns.split(" ");
	const perChunk = grid_widths.length;
	const grid_width = parseInt(grid_widths[0]);
	const grid_gap = parseInt(getComputedStyle(el).gap);

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

function findSmallestIdx(columns) {
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

function getMaxHeight(columns) {
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
