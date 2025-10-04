import AlbumService from "@/services/album-service";
import { defineStore } from "pinia";

export type LayoutStore = ReturnType<typeof useLayoutStore>;

const BASE = "my-0 w-5 h-5 mr-0 ml-0 transition-all duration-300 group-hover:scale-150 group-hover:stroke-black dark:group-hover:stroke-white ";

export const useLayoutStore = defineStore("layout-store", {
	state: () => ({
		config: undefined as App.Http.Resources.GalleryConfigs.PhotoLayoutConfig | undefined,
		layout: "justified" as App.Enum.PhotoLayoutType,
	}),
	actions: {
		async load(): Promise<void> {
			if (this.config !== undefined) {
				// We already have the config, no need to reload it
				return Promise.resolve();
			}

			// This will load the layout config from the server
			return AlbumService.getLayout().then((data) => {
				this.config = data.data;
			});
		},
	},
	getters: {
		squareClass(): string {
			return BASE + (this.layout === "square" ? "stroke-primary-400" : "stroke-neutral-400");
		},
		justifiedClass(): string {
			return BASE + (this.layout === "justified" ? "fill-primary-400" : "fill-neutral-400");
		},
		masonryClass(): string {
			return BASE + (this.layout === "masonry" ? "stroke-primary-400" : "stroke-neutral-400");
		},
		gridClass(): string {
			return BASE + (this.layout === "grid" ? "stroke-primary-400" : "stroke-neutral-400");
		},
	},
});
