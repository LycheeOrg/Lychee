import { TogglablesStateStore } from "@/stores/ModalsState";
import { storeToRefs } from "pinia";
import { ref } from "vue";

export function useGalleryModals(togglableStore: TogglablesStateStore) {
	const {
		is_upload_visible,
		is_rename_visible,
		is_move_visible,
		is_delete_visible,
		is_merge_album_visible,
		is_share_album_visible,
		is_import_from_link_open,
		is_tag_visible,
		is_copy_visible,
	} = storeToRefs(togglableStore);

	function toggleCreateAlbum() {
		togglableStore.is_create_album_visible = !togglableStore.is_create_album_visible;
	}

	function toggleCreateTagAlbum() {
		togglableStore.is_create_tag_album_visible = !togglableStore.is_create_tag_album_visible;
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

	const is_import_from_dropbox_open = ref(false);

	function toggleImportFromDropbox() {
		is_import_from_dropbox_open.value = !is_import_from_dropbox_open.value;
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
		toggleCreateAlbum,
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
		toggleUpload,
		is_tag_visible,
		toggleTag,
		is_copy_visible,
		toggleCopy,
	};
}
