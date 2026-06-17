import { defineStore } from "pinia";
import { ref } from "vue";
import PhotoFacesService from "@/services/photo-faces-service";

export interface FaceData {
	faces: App.Http.Resources.Models.FaceResource[];
	hiddenFaceCount: number;
}

const EMPTY: FaceData = Object.freeze({ faces: [], hiddenFaceCount: 0 });

export const usePhotoFacesStore = defineStore("photo-faces-store", () => {
	const cache = ref(new Map<string, FaceData>());
	const pending = new Map<string, Promise<FaceData>>();

	function fetch(photoId: string): Promise<FaceData> {
		const cached = cache.value.get(photoId);
		if (cached) return Promise.resolve(cached);

		const inflight = pending.get(photoId);
		if (inflight) return inflight;

		const promise = PhotoFacesService.getPhotoFaces(photoId)
			.then((response) => {
				const data: FaceData = {
					faces: response.data.faces,
					hiddenFaceCount: response.data.hidden_face_count,
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

	function prefetch(photoId: string): void {
		fetch(photoId);
	}

	function invalidate(photoId: string): Promise<FaceData> {
		cache.value.delete(photoId);
		pending.delete(photoId);
		return fetch(photoId);
	}

	function get(photoId: string): FaceData {
		return cache.value.get(photoId) ?? EMPTY;
	}

	function removeFace(photoId: string, faceId: string): void {
		const data = cache.value.get(photoId);
		if (!data) return;
		const idx = data.faces.findIndex((f: App.Http.Resources.Models.FaceResource) => f.id === faceId);
		if (idx !== -1) {
			data.faces.splice(idx, 1);
		}
	}

	return { cache, fetch, prefetch, invalidate, get, removeFace };
});
