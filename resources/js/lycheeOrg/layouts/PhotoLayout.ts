import { Layouts, PhotoLayoutType } from "../backend";
import { useGrid } from "./useGrid";
import { useJustify } from "./useJustify";
import { useMasonry } from "./useMasonry";
import { useSquare } from "./useSquare";

export default class PhotoLayout {
	type: PhotoLayoutType;
	justifiedHeight: number;
	masonryWidth: number;
	gridWidth: number;
	gap: number;
	squareWidth: number;

	constructor(layout: Layouts) {
		this.type = layout.photos_layout;
		this.justifiedHeight = layout.photo_layout_justified_row_height;
		this.masonryWidth = layout.photo_layout_masonry_column_width;
		this.gridWidth = layout.photo_layout_grid_column_width;
		this.squareWidth = layout.photo_layout_square_column_width;
		this.gap = layout.photo_layout_gap;

		window.addEventListener("resize", () => this.activateLayout());
	}

	activateLayout() {
		const photoListing = document.getElementById("photoListing");
		if (photoListing === null) {
			return; // Nothing to do
		}

		if (this.type === "square") {
			useSquare(photoListing, this.squareWidth, this.gap);
		}

		if (this.type === "grid") {
			useGrid(photoListing, this.gridWidth, this.gap);
		}

		if (this.type === "justified" || this.type === "unjustified") {
			useJustify(photoListing, this.justifiedHeight);
		}

		if (this.type === "masonry") {
			useMasonry(photoListing, this.masonryWidth, this.gap);
		}
	}
}
