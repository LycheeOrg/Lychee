import { computed, Ref, ref } from "vue";
import { useSquare } from "./useSquare";
import { useJustify } from "./useJustify";
import { useMasonry } from "./useMasonry";
import { useGrid } from "./useGrid";
import AlbumService from "@/services/album-service";

export type TimelineData = {
	isTimeline: Ref<boolean>;
	isLeftBorderVisible: Ref<boolean>;
};

export function useLayouts(
	config: App.Http.Resources.GalleryConfigs.PhotoLayoutConfig,
	layout: Ref<App.Enum.PhotoLayoutType>,
	timelineData: TimelineData,
	elemId: string = "photoListing",
) {
	const configRef = ref(config);
	const elementId = elemId;

	function activateLayout() {
		const photoListing = document.getElementById(elementId);
		if (photoListing === null) {
			return; // Nothing to do
		}

		switch (layout.value) {
			case "square":
				return useSquare(photoListing, configRef.value.photo_layout_square_column_width, configRef.value.photo_layout_gap);
			case "justified":
			case "unjustified":
				return useJustify(photoListing, configRef.value.photo_layout_justified_row_height, timelineData);
			case "masonry":
				return useMasonry(photoListing, configRef.value.photo_layout_masonry_column_width, configRef.value.photo_layout_gap);
			case "grid":
				return useGrid(photoListing, configRef.value.photo_layout_grid_column_width, config.photo_layout_gap);
		}
	}

	return {
		activateLayout,
	};
}

export function useLayoutClass(layout: Ref<App.Enum.PhotoLayoutType>) {
	const BASE = "my-0 w-5 h-5 mr-0 ml-0 transition-all duration-300 group-hover:scale-150 group-hover:stroke-black dark:group-hover:stroke-white ";
	const squareClass = computed(() => BASE + (layout.value === "square" ? "stroke-primary-400" : "stroke-neutral-400"));
	const justifiedClass = computed(() => BASE + (layout.value === "justified" ? "fill-primary-400" : "fill-neutral-400"));
	const masonryClass = computed(() => BASE + (layout.value === "masonry" ? "stroke-primary-400" : "stroke-neutral-400"));
	const gridClass = computed(() => BASE + (layout.value === "grid" ? "stroke-primary-400" : "stroke-neutral-400"));

	return {
		squareClass,
		justifiedClass,
		masonryClass,
		gridClass,
	};
}

export function useGetLayoutConfig() {
	const layoutConfig = ref(null) as Ref<null | App.Http.Resources.GalleryConfigs.PhotoLayoutConfig>;
	const layout = ref("square") as Ref<App.Enum.PhotoLayoutType>;

	function loadLayoutConfig(): Promise<void> {
		return AlbumService.getLayout().then((data) => {
			layoutConfig.value = data.data;
		});
	}

	return {
		layout,
		layoutConfig,
		loadLayoutConfig,
	};
}
