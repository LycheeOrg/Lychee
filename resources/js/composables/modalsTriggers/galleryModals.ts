import { Ref, ref } from "vue";

export function useGalleryModals(is_upload_visible: Ref<boolean>) {
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

	const isImportFromDropboxOpen = ref(false);

	function toggleImportFromDropbox() {
		isImportFromDropboxOpen.value = !isImportFromDropboxOpen.value;
	}

	function toggleUpload() {
		is_upload_visible.value = !is_upload_visible.value;
	}

	const isTagVisible = ref(false);

	function toggleTag() {
		isTagVisible.value = !isTagVisible.value;
	}

	const isCopyVisible = ref(false);

	function toggleCopy() {
		isCopyVisible.value = !isCopyVisible.value;
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
		isImportFromDropboxOpen,
		toggleImportFromDropbox,
		toggleUpload,
		isTagVisible,
		toggleTag,
		isCopyVisible,
		toggleCopy,
	};
}
