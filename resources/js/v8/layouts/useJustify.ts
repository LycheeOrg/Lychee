import { TimelineData } from "./PhotoLayout";
import { ChildNodeWithDataStyle } from "@/layouts/types";
import { getWidth } from "@/layouts/getWidth";
import { getRatio } from "@/layouts/ratio";
import { type RouteLocationNormalizedLoaded } from "vue-router";
import { justified } from "./wasmLayouts";
import { applyLayoutResult } from "./applyLayoutResult";

// justified-layout's own default box spacing, kept for parity with the previous implementation.
const DEFAULT_SPACING = 10;

export function useJustify(
	el: HTMLElement,
	photoDefaultHeight: number = 320,
	timelineData: TimelineData,
	route: RouteLocationNormalizedLoaded,
	align: "left" | "right",
) {
	const width = getWidth(timelineData, route);

	const justifiedItems: ChildNodeWithDataStyle[] = [...el.childNodes].filter((gridItem) => gridItem.nodeType === 1);

	const ratio = Float64Array.from(getRatio(justifiedItems));

	const result = justified(ratio, width, photoDefaultHeight, DEFAULT_SPACING);

	applyLayoutResult(el, justifiedItems, result, align);
}
