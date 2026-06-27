import { defineStore } from "pinia";
import { ref } from "vue";
import PhotoNsfwDetectionsService from "@/services/photo-nsfw-detections-service";

export interface NsfwDetectionData {
	detections: App.Http.Resources.Models.NsfwDetectionResource[];
	imageWidth: number;
	imageHeight: number;
}

const EMPTY: NsfwDetectionData = Object.freeze({ detections: [], imageWidth: 1, imageHeight: 1 });

export const usePhotoNsfwDetectionsStore = defineStore("photo-nsfw-detections-store", () => {
	const cache = ref(new Map<string, NsfwDetectionData>());
	const pending = new Map<string, Promise<NsfwDetectionData>>();

	function fetch(photoId: string): Promise<NsfwDetectionData> {
		const cached = cache.value.get(photoId);
		if (cached) return Promise.resolve(cached);

		const inflight = pending.get(photoId);
		if (inflight) return inflight;

		const promise = PhotoNsfwDetectionsService.getPhotoNsfwDetections(photoId)
			.then((response) => {
				const data: NsfwDetectionData = {
					detections: response.data.detections,
					imageWidth: response.data.image_width,
					imageHeight: response.data.image_height,
				};
				cache.value.set(photoId, data);
				return data;
			})
			.catch(() => {
				return EMPTY;
			})
			.finally(() => {
				pending.delete(photoId);
			});

		pending.set(photoId, promise);
		return promise;
	}

	function get(photoId: string): NsfwDetectionData {
		return cache.value.get(photoId) ?? EMPTY;
	}

	function invalidate(photoId: string): Promise<NsfwDetectionData> {
		cache.value.delete(photoId);
		pending.delete(photoId);
		return fetch(photoId);
	}

	return { cache, fetch, get, invalidate };
});
