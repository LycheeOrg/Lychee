import { isTouchDevice } from "@/utils/keybindings-utils";
import { TimelineData } from "./PhotoLayout";
import { type RouteLocationNormalizedLoaded } from "vue-router";

export function getWidth(timelineData: TimelineData, route: RouteLocationNormalizedLoaded): number {
	const baseWidth = window.innerWidth;

	const v7Container = document.querySelector(".p-panel-content") as HTMLElement | null;
	let paddingLeft: number;
	let paddingRight: number;
	if (v7Container) {
		paddingLeft = Math.max(20, parseInt(window.getComputedStyle(v7Container!).getPropertyValue("padding-left")));
		paddingRight = Math.max(20, parseInt(window.getComputedStyle(v7Container!).getPropertyValue("padding-right")));
	} else {
		const v8Container = document.getElementById("lychee_view_content");
		paddingLeft = parseInt(window.getComputedStyle(v8Container!).getPropertyValue("padding-left"));
		paddingRight = parseInt(window.getComputedStyle(v8Container!).getPropertyValue("padding-right"));
	}

	let scrollBarWidth = 15;
	if (isTouchDevice()) {
		scrollBarWidth = 0;
	}

	const width = Math.min(baseWidth - paddingLeft - paddingRight - scrollBarWidth);

	let timeLineBorder = 0;
	if (timelineData.isTimeline === true && (timelineData.isLeftBorderVisible.value && !isTouchDevice()) === true) {
		timeLineBorder = 50;
	}

	const routeName = route.name as string;
	if (routeName.includes("timeline")) {
		timeLineBorder = 50;
	}

	return width - timeLineBorder;
}
