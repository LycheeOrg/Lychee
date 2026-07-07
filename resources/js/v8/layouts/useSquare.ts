import { type RouteLocationNormalizedLoaded } from "vue-router";
import { getWidth } from "@/layouts/getWidth";
import { getRatio } from "@/layouts/ratio";
import { TimelineData } from "./PhotoLayout";
import { ChildNodeWithDataStyle } from "@/layouts/types";
import { square } from "./wasmLayouts";
import { applyLayoutResult } from "./applyLayoutResult";

export function useSquare(
	el: HTMLElement,
	target_width_height: number,
	gap: number = 12,
	timelineData: TimelineData,
	route: RouteLocationNormalizedLoaded,
	align: "left" | "right",
) {
	const gridItems: ChildNodeWithDataStyle[] = [...el.childNodes].filter((gridItem) => gridItem.nodeType === 1);

	const width = getWidth(timelineData, route);
	const ratio = Float64Array.from(getRatio(gridItems));

	const result = square(ratio, width, target_width_height, gap);

	applyLayoutResult(el, gridItems, result, align);
}
