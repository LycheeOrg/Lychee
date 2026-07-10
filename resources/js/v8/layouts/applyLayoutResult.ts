import { ChildNodeWithDataStyle } from "@/layouts/types";
import { LayoutResult } from "./types";

export function applyLayoutResult(el: HTMLElement, items: ChildNodeWithDataStyle[], result: LayoutResult, align: "left" | "right") {
	el.style.height = result.containerHeight + "px";
	items.forEach((e, i) => {
		const box = result.boxes[i];
		if (!box) {
			// Race condition in search.find -- window content
			// and `photos` can get out of sync as search
			// query is being modified.
			console.error("race!");
			return;
		}

		e.style = e.style ?? {};
		e.style.top = box.top + "px";
		e.style.width = box.width + "px";
		e.style.height = box.height + "px";
		e.style[align] = box.left + "px";
	});
}
