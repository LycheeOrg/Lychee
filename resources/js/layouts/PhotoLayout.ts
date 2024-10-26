import { computed, ref } from "vue";
import { useSquare } from "./useSquare";
import { useJustify } from "./useJustify";
import { useMasonry } from "./useMasonry";
import { useGrid } from "./useGrid";

export function useLayouts(config: App.Http.Resources.GalleryConfigs.PhotoLayoutConfig, photo_layout: App.Enum.PhotoLayoutType) {
	const configRef = ref(config);
	const layout = ref(photo_layout);
	const BASE = "my-0 w-5 h-5 mr-0 ml-0 transition-all duration-300 group-hover:scale-150 group-hover:stroke-black dark:group-hover:stroke-white ";
	const squareClass = computed(() => BASE + (layout.value === "square" ? "stroke-primary-400" : "stroke-neutral-400"));
	const justifiedClass = computed(() => BASE + (layout.value === "justified" ? "fill-primary-400" : "fill-neutral-400"));
	const masonryClass = computed(() => BASE + (layout.value === "masonry" ? "stroke-primary-400" : "stroke-neutral-400"));
	const gridClass = computed(() => BASE + (layout.value === "grid" ? "stroke-primary-400" : "stroke-neutral-400"));

	function activateLayout() {
		const photoListing = document.getElementById("photoListing");
		if (photoListing === null) {
			return; // Nothing to do
		}

		switch (layout.value) {
			case "square":
				return useSquare(photoListing, configRef.value.photo_layout_square_column_width, configRef.value.photo_layout_gap);
			case "justified":
			case "unjustified":
				return useJustify(photoListing, configRef.value.photo_layout_justified_row_height);
			case "masonry":
				return useMasonry(photoListing, configRef.value.photo_layout_masonry_column_width, configRef.value.photo_layout_gap);
			case "grid":
				return useGrid(photoListing, configRef.value.photo_layout_grid_column_width, config.photo_layout_gap);
		}
	}

	return {
		layout,
		squareClass,
		justifiedClass,
		masonryClass,
		gridClass,
		activateLayout,
	};
}
