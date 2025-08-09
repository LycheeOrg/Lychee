import { TimelineData } from "./PhotoLayout";
import { ChildNodeWithDataStyle } from "./types";
import createJustifiedLayout from "justified-layout";
import { getWidth } from "./getWidth";
import { type RouteLocationNormalizedLoaded } from "vue-router";
import { getRatio } from "./ratio";

export function useJustify(
	el: HTMLElement,
	photoDefaultHeight: number = 320,
	timelineData: TimelineData,
	route: RouteLocationNormalizedLoaded,
	align: "left" | "right",
) {
	const width = getWidth(timelineData, route);

	const justifiedItems: ChildNodeWithDataStyle[] = [...el.childNodes].filter((gridItem) => gridItem.nodeType === 1);

	const ratio = getRatio(justifiedItems);

	const layoutGeometry = createJustifiedLayout(ratio, {
		containerWidth: width,
		containerPadding: 0,
		targetRowHeight: photoDefaultHeight,
	});
	el.style.height = layoutGeometry.containerHeight + "px";
	justifiedItems.forEach((e, i) => {
		if (!layoutGeometry.boxes[i]) {
			// Race condition in search.find -- window content
			// and `photos` can get out of sync as search
			// query is being modified.
			console.error("race!");
			return false;
		}

		e.style = e.style ?? {};
		e.style.top = layoutGeometry.boxes[i].top + "px";
		e.style.width = layoutGeometry.boxes[i].width + "px";
		e.style.height = layoutGeometry.boxes[i].height + "px";
		e.style[align] = layoutGeometry.boxes[i].left + "px";
	});
}
