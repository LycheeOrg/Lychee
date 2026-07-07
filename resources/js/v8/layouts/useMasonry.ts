import { type RouteLocationNormalizedLoaded } from "vue-router";
import { ChildNodeWithDataStyle } from "@/layouts/types";
import { type TimelineData } from "./PhotoLayout";
import { getWidth } from "@/layouts/getWidth";
import { getRatio } from "@/layouts/ratio";
import { masonry } from "./wasmLayouts";
import { applyLayoutResult } from "./applyLayoutResult";

export function useMasonry(
	el: HTMLElement,
	target_width: number,
	gap: number = 12,
	timelineData: TimelineData,
	route: RouteLocationNormalizedLoaded,
	align: "left" | "right",
) {
	const gridItems: ChildNodeWithDataStyle[] = [...el.childNodes].filter((gridItem) => gridItem.nodeType === 1);

	const width = getWidth(timelineData, route);
	const ratio = Float64Array.from(getRatio(gridItems));

	const result = masonry(ratio, width, target_width, gap);

	applyLayoutResult(el, gridItems, result, align);
}
