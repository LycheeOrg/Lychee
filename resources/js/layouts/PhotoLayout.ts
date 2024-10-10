import { computed, ref } from "vue";
import { useSquare } from "./useSquare";
import { useJustify } from "./useJustify";
import { useMasonry } from "./useMasonry";
import { useGrid } from "./useGrid";

export function useLayouts(config: App.Http.Resources.GalleryConfigs.PhotoLayoutConfig) {
	const layout = ref(config);
	const BASE = "my-0 w-5 h-5 mr-0 ml-0 transition-all duration-300 group-hover:scale-150 group-hover:stroke-black dark:group-hover:stroke-white ";
	const squareClass = computed(() => BASE + (layout.value.photos_layout === "square" ? "stroke-primary-400" : "stroke-neutral-400"));
	const justifiedClass = computed(() => BASE + (layout.value.photos_layout === "justified" ? "fill-primary-400" : "fill-neutral-400"));
	const masonryClass = computed(() => BASE + (layout.value.photos_layout === "masonry" ? "stroke-primary-400" : "stroke-neutral-400"));
	const gridClass = computed(() => BASE + (layout.value.photos_layout === "grid" ? "stroke-primary-400" : "stroke-neutral-400"));

	function activateLayout() {
		const photoListing = document.getElementById("photoListing");
		if (photoListing === null) {
			return; // Nothing to do
		}

		switch (layout.value.photos_layout) {
			case "square":
				return useSquare(photoListing, layout.value.photo_layout_square_column_width, layout.value.photo_layout_gap);
			case "justified":
			case "unjustified":
				return useJustify(photoListing, layout.value.photo_layout_justified_row_height);
			case "masonry":
				return useMasonry(photoListing, layout.value.photo_layout_masonry_column_width, layout.value.photo_layout_gap);
			case "grid":
				return useGrid(photoListing, layout.value.photo_layout_grid_column_width, layout.value.photo_layout_gap);
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
