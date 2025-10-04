import { defineStore } from "pinia";
import { usePhotosStore } from "./PhotosState";
import TagsService from "@/services/tags-service";

export type TagStore = ReturnType<typeof useTagStore>;
export type TagData = { name: string; id: number; num: number };

export const useTagStore = defineStore("tag-store", {
	state: () => ({
		isLoading: false,
		tagId: undefined as undefined | string,
		tag: undefined as undefined | TagData,
	}),
	actions: {
		reset() {
			this.isLoading = false;
			this.tagId = undefined;
			this.tag = undefined;
		},
		load(): Promise<void> {
			if (this.tagId === undefined) {
				return Promise.resolve();
			}

			const photosStore = usePhotosStore();
			const requestedTagId = this.tagId;
			this.isLoading = true;

			return TagsService.get(requestedTagId)
				.then((data) => {
					if (this.tagId !== requestedTagId) {
						return;
					}
					photosStore.setPhotos(data.data.photos, false);
					this.tag = { name: data.data.name, id: data.data.id, num: data.data.photos.length };
				})
				.finally(() => {
					if (this.tagId === requestedTagId) {
						this.isLoading = false;
					}
				});
		},
	},
});
