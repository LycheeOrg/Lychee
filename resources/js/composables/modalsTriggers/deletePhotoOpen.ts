import { ref } from "vue";

export function useDeletePhotoOpen() {
	const isDeletePhotoVisible = ref(false);

	function toggleDeletePhoto() {
		isDeletePhotoVisible.value = !isDeletePhotoVisible.value;
	}

	return {
		isDeletePhotoVisible,
		toggleDeletePhoto,
	};
}
