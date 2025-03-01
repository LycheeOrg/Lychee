import { Ref } from "vue";

export function useHasNextPreviousPhoto(photo: Ref<App.Http.Resources.Models.PhotoResource | undefined>) {
	function hasPrevious(): boolean {
		return photo.value?.previous_photo_id !== null;
	}

	function hasNext(): boolean {
		return photo.value?.next_photo_id !== null;
	}

	return { hasPrevious, hasNext };
}
