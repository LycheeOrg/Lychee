export function useAlbumActions() {
	function canInteractAlbum(album: App.Http.Resources.Models.ThumbAlbumResource): boolean {
		return album.rights.can_download || album.rights.can_move || album.rights.can_edit || album.rights.can_delete;
	}

	function canInteractPhoto(photo: App.Http.Resources.Models.PhotoResource): boolean {
		return photo.rights.can_download || photo.rights.can_edit;
	}

	return {
		canInteractAlbum,
		canInteractPhoto,
	};
}
