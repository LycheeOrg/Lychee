import { ref } from "vue";

export function useGalleryModals() {
	const isCreateAlbumOpen = ref(false);

	function toggleCreateAlbum() {
		isCreateAlbumOpen.value = !isCreateAlbumOpen.value;
	}

	const isRenameVisible = ref(false);

	function toggleRename() {
		isRenameVisible.value = !isRenameVisible.value;
	}

	const isMoveVisible = ref(false);

	function toggleMove() {
		isMoveVisible.value = !isMoveVisible.value;
	}

	const isDeleteVisible = ref(false);

	function toggleDelete() {
		isDeleteVisible.value = !isDeleteVisible.value;
	}

	const isMergeAlbumVisible = ref(false);

	function toggleMergeAlbum() {
		isMergeAlbumVisible.value = !isMergeAlbumVisible.value;
	}

	const isShareAlbumVisible = ref(false);

	function toggleShareAlbum() {
		isShareAlbumVisible.value = !isShareAlbumVisible.value;
	}

	const isImportFromLinkOpen = ref(false);

	function toggleImportFromLink() {
		isImportFromLinkOpen.value = !isImportFromLinkOpen.value;
	}

	const isUploadOpen = ref(false);

	function toggleUpload() {
		isUploadOpen.value = !isUploadOpen.value;
	}

	return {
		isCreateAlbumOpen,
		toggleCreateAlbum,
		isDeleteVisible,
		toggleDelete,
		isMergeAlbumVisible,
		toggleMergeAlbum,
		isMoveVisible,
		toggleMove,
		isRenameVisible,
		toggleRename,
		isShareAlbumVisible,
		toggleShareAlbum,
		isImportFromLinkOpen,
		toggleImportFromLink,
		isUploadOpen,
		toggleUpload,
	};
}
