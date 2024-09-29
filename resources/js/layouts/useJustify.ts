import { ChildNodeWithDataStyle } from "./types";
import createJustifiedLayout from "justified-layout";

export function useJustify(el: HTMLElement, photoDefaultHeight: number = 320) {
	const containerWidth = parseInt(getComputedStyle(el).width);
	
	// const width = el.clientWidth;
	// const body_width = document.body.scrollWidth;
	// console.log("containerWidth: " + containerWidth);
	// console.log("width: " + width)
	// console.log("body_width: " + body_width);

	// @ts-expect-error
	const justifiedItems: ChildNodeWithDataStyle[] = [...el.childNodes].filter((gridItem) => gridItem.nodeType === 1);

	const ratio: number[] = justifiedItems.map(function (_photo) {
		const height = _photo.dataset.height;
		const width = _photo.dataset.width;
		return height > 0 ? width / height : 1;
	});
	const layoutGeometry = createJustifiedLayout(ratio, {
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
