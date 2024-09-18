import { ref } from "vue";

export function useDeleteAlbumOpen() {
	const isDeleteAlbumVisible = ref(false);

	function toggleDeleteAlbum() {
		isDeleteAlbumVisible.value = !isDeleteAlbumVisible.value;
	}

	return {
		isDeleteAlbumVisible,
		toggleDeleteAlbum,
	};
}
