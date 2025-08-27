import { TogglablesStateStore } from "@/stores/ModalsState";
import { storeToRefs } from "pinia";

export function useGalleryModals(togglableStore: TogglablesStateStore) {
	const {
		is_create_album_visible,
		is_create_tag_album_visible,
		is_upload_visible,
		is_rename_visible,
		is_move_visible,
		is_delete_visible,
		is_merge_album_visible,
		is_share_album_visible,
		is_import_from_link_open,
		is_tag_visible,
		is_copy_visible,
		is_import_from_dropbox_open,
		is_import_from_server_open,
	} = storeToRefs(togglableStore);

	function toggleCreateAlbum() {
		is_create_album_visible.value = !is_create_album_visible.value;
	}

	function toggleCreateTagAlbum() {
		is_create_tag_album_visible.value = !is_create_tag_album_visible.value;
	}

	function toggleRename() {
		is_rename_visible.value = !is_rename_visible.value;
	}

	function toggleMove() {
		is_move_visible.value = !is_move_visible.value;
	}

	function toggleDelete() {
		is_delete_visible.value = !is_delete_visible.value;
	}

	function toggleMergeAlbum() {
		is_merge_album_visible.value = !is_merge_album_visible.value;
	}

	function toggleShareAlbum() {
		is_share_album_visible.value = !is_share_album_visible.value;
	}

	function toggleImportFromLink() {
		is_import_from_link_open.value = !is_import_from_link_open.value;
	}

	function toggleImportFromDropbox() {
		is_import_from_dropbox_open.value = !is_import_from_dropbox_open.value;
	}

	function toggleImportFromServer() {
		is_import_from_server_open.value = !is_import_from_server_open.value;
	}

	function toggleUpload() {
		is_upload_visible.value = !is_upload_visible.value;
	}

	function toggleTag() {
		is_tag_visible.value = !is_tag_visible.value;
	}

	function toggleCopy() {
		is_copy_visible.value = !is_copy_visible.value;
	}

	return {
		is_create_album_visible,
		toggleCreateAlbum,
		is_create_tag_album_visible,
		toggleCreateTagAlbum,
		is_delete_visible,
		toggleDelete,
		is_merge_album_visible,
		toggleMergeAlbum,
		is_move_visible,
		toggleMove,
		is_rename_visible,
		toggleRename,
		is_share_album_visible,
		toggleShareAlbum,
		is_import_from_link_open,
		toggleImportFromLink,
		is_import_from_dropbox_open,
		toggleImportFromDropbox,
		is_import_from_server_open,
		toggleImportFromServer,
		is_upload_visible,
		toggleUpload,
		is_tag_visible,
		toggleTag,
		is_copy_visible,
		toggleCopy,
	};
}
