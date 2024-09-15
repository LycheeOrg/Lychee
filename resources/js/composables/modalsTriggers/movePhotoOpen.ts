import { ref } from "vue";

export function useMovePhotoOpen() {
	const isMovePhotoVisible = ref(false);

	function toggleMovePhoto() {
		isMovePhotoVisible.value = !isMovePhotoVisible.value;
	}

	return {
		isMovePhotoVisible,
		toggleMovePhoto,
	};
}
