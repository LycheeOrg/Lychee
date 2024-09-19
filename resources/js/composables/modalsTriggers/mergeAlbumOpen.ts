import { ref } from "vue";

export function useMergeAlbumOpen() {
	const isMergeAlbumVisible = ref(false);

	function toggleMergeAlbum() {
		isMergeAlbumVisible.value = !isMergeAlbumVisible.value;
	}

	return {
		isMergeAlbumVisible,
		toggleMergeAlbum,
	};
}
