import { isTouchDevice } from "@/utils/keybindings-utils";
import { TimelineData } from "./PhotoLayout";
import { useRoute } from "vue-router";

export function getWidth(timelineData: TimelineData): number {
	const baseWidth = window.innerWidth;
	const paddingLeftRight = 2 * 18;

	let scrollBarWidth = 15;
	if (isTouchDevice()) {
		scrollBarWidth = 0;
	}

	const width = Math.min(baseWidth - paddingLeftRight - scrollBarWidth);

	let timeLineBorder = 0;
	if (timelineData.isTimeline.value === true && (timelineData.isLeftBorderVisible.value && !isTouchDevice()) === true) {
		timeLineBorder = 50;
	}

	const route = useRoute();
	const routeName = route.name as string;
	if (routeName.includes("timeline")) {
		timeLineBorder = 50;
	}

	return width - timeLineBorder;
}
