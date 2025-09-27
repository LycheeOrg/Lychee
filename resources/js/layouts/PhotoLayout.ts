import { Ref } from "vue";
import { useSquare } from "./useSquare";
import { useJustify } from "./useJustify";
import { useMasonry } from "./useMasonry";
import { useGrid } from "./useGrid";
import { type RouteLocationNormalizedLoaded } from "vue-router";
import { useLtRorRtL } from "@/utils/Helpers";
import { LayoutStore } from "@/stores/LayoutState";

export type TimelineData = {
	isTimeline: boolean;
	isLeftBorderVisible: Ref<boolean>;
};

export function useLayouts(
	layoutState: LayoutStore,
	timelineData: TimelineData,
	elemId: string = "photoListing",
	route: RouteLocationNormalizedLoaded,
) {
	const elementId = elemId;
	const { isLTR } = useLtRorRtL();

	function activateLayout() {
		const photoListing = document.getElementById(elementId);
		if (photoListing === null || layoutState.config === undefined) {
			return; // Nothing to do
		}

		const align = isLTR() ? "left" : "right";

		switch (layoutState.layout) {
			case "square":
				return useSquare(
					photoListing,
					layoutState.config.photo_layout_square_column_width,
					layoutState.config.photo_layout_gap,
					timelineData,
					route,
					align,
				);
			case "justified":
				return useJustify(photoListing, layoutState.config.photo_layout_justified_row_height, timelineData, route, align);
			case "masonry":
				return useMasonry(
					photoListing,
					layoutState.config.photo_layout_masonry_column_width,
					layoutState.config.photo_layout_gap,
					timelineData,
					route,
					align,
				);
			case "grid":
				return useGrid(
					photoListing,
					layoutState.config.photo_layout_grid_column_width,
					layoutState.config.photo_layout_gap,
					timelineData,
					route,
					align,
				);
		}
	}

	return {
		activateLayout,
	};
}
