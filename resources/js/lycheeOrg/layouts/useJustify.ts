import { ChildNodeWithDataStyle } from "./types";

export function useJustify(el: HTMLElement, photoDefaultHeight: number = 320) {
	const view = document.getElementById("lychee_view_content");
	if (view === null) {
		return;
	}

	const multiplier = view.scrollHeight > view.clientHeight ? 0 : 1;
	const containerWidth = parseInt(getComputedStyle(el).width) - multiplier * 20;

	// @ts-expect-error
	const justifiedItems: ChildNodeWithDataStyle[] = [...el.childNodes].filter((gridItem) => gridItem.nodeType === 1);

	const ratio: number[] = justifiedItems.map(function (_photo) {
		const height = _photo.dataset.height;
		const width = _photo.dataset.width;
		return height > 0 ? width / height : 1;
	});
	const layoutGeometry = require("justified-layout")(ratio, {
		containerWidth: containerWidth,
		containerPadding: 0,
		targetRowHeight: photoDefaultHeight,
	});
	el.style.height = layoutGeometry.containerHeight + "px";
	justifiedItems.forEach((e, i) => {
		if (!layoutGeometry.boxes[i]) {
			// Race condition in search.find -- window content
			// and `photos` can get out of sync as search
			// query is being modified.
			console.log("race!");
			return false;
		}

		e.style.top = layoutGeometry.boxes[i].top + "px";
		e.style.width = layoutGeometry.boxes[i].width + "px";
		e.style.height = layoutGeometry.boxes[i].height + "px";
		e.style.left = layoutGeometry.boxes[i].left + "px";
	});
}
