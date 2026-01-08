import { useAlbumStore } from "@/stores/AlbumState";

export function useAlbumActions() {

	const albumStore = useAlbumStore();

	function canInteractAlbum(album: App.Http.Resources.Models.ThumbAlbumResource): boolean {
		return album.rights.can_download || album.rights.can_move || album.rights.can_edit || album.rights.can_delete;
	}

	function canInteractPhoto(): boolean {
		return albumStore.rights?.can_download || albumStore.rights?.can_edit || false;
	}

	return {
		canInteractAlbum,
		canInteractPhoto,
	};
}
