import { ref } from "vue";

export function useShareAlbumOpen() {
	const isShareAlbumVisible = ref(false);

	function toggleShareAlbum() {
		isShareAlbumVisible.value = !isShareAlbumVisible.value;
	}

	return {
		isShareAlbumVisible,
		toggleShareAlbum,
	};
}
