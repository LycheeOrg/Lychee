import { ref } from "vue";

export function useMoveAlbumOpen() {
	const isMoveAlbumVisible = ref(false);

	function toggleMoveAlbum() {
		isMoveAlbumVisible.value = !isMoveAlbumVisible.value;
	}

	return {
		isMoveAlbumVisible,
		toggleMoveAlbum,
	};
}
