import { TogglablesStateStore } from "@/stores/ModalsState";
import { ref } from "vue";

export function useGalleryModals(togglableStore: TogglablesStateStore) {
	function toggleCreateAlbum() {
		togglableStore.is_create_album_visible = !togglableStore.is_create_album_visible;
	}

	function toggleCreateTagAlbum() {
		togglableStore.is_create_tag_album_visible = !togglableStore.is_create_tag_album_visible;
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
		togglableStore.is_upload_visible = !togglableStore.is_upload_visible;
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
		toggleCreateAlbum,
		toggleCreateTagAlbum,
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
