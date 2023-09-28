export default { useMasonry };

export function useMasonry(el) {
	const gridItems = [...el.childNodes].filter((gridItem) => gridItem.nodeType === 1);
	const grid_widths = getComputedStyle(el).gridTemplateColumns.split(" ")
	const gap = parseInt(getComputedStyle(el).gap);

	const perChunk = grid_widths.length;
	const width = parseInt(grid_widths[0]);

	// Remove class grid because it is going to be annoying later: we use absolute coordinates.
	el.classList.remove('grid')
	 
	// Compute ratio of each item.
	const ratio = gridItems.map(function (_photo) {
		const height = _photo.dataset.height;
		const width = _photo.dataset.width;
		return height > 0 ? width / height : 1;
	});

	let columns = Array.from({length: perChunk}, (_, idx) => { return { height: 0, left: (gap + width) * idx }; });
	// console.log(columns);
	gridItems.forEach(function (e, i) {
		let idx = findSmallestIdx(columns);
		let column = columns[idx];
		const height = Math.floor(width / ratio[i]);
		e.style.top = column.height + "px";
		e.style.width = width + "px";
		e.style.height = height + "px";
		e.style.left = column.left + "px";
		column.height = column.height + height + gap;

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
		console.log(col, i, smallest);
		if (isNaN(smallest)) {
			console.log('NAN');
			smallest = col.height;
			idx = i;
		} else if (col.height < smallest) {
			smallest = col.height;
			idx = i;
			console.log('smallest!');
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
