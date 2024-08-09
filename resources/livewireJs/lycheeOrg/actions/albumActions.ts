import { AlbumView } from "@/data/views/types";

export default class AlbumActions {
	handleContextPhoto(event: MouseEvent, view: AlbumView) {
		if (!view.rights.can_edit) {
			return;
		}

		if (event.currentTarget === null) {
			return;
		}

		view.select.selectedAlbums = [];
		// @ts-ignore
		const photoId = event.currentTarget.dataset.id;
		const index = view.select.selectedPhotos.indexOf(photoId);
		if (index > -1 && view.select.selectedPhotos.length > 1) {
			// found and more than one element
			// @ts-ignore
			view.$wire.openPhotosDropdown(event.clientX, event.clientY, view.select.selectedPhotos);
		} else {
			// @ts-ignore
			view.$wire.openPhotoDropdown(event.clientX, event.clientY, event.currentTarget.dataset.id);
		}
	}

	handleClickPhoto(event: MouseEvent, view: AlbumView) {
		if (event.currentTarget === null) {
			return;
		}

		if (!event.ctrlKey && !event.shiftKey) {
			console.log("open:");
			// @ts-expect-error
			const photoId = event.currentTarget.dataset.id;
			console.log(photoId);
			view.goTo(photoId);
			return;
		}

		view.select.handleClickPhoto(event);
	}

	handleContextAlbum(event: MouseEvent, view: AlbumView) {
		if (!view.rights.can_edit) {
			return;
		}

		if (event.currentTarget === null) {
			return;
		}

		view.select.selectedPhotos = [];
		// @ts-ignore
		const albumId = event.currentTarget.dataset.id;
		const index = view.select.selectedAlbums.indexOf(albumId);
		if (index > -1 && view.select.selectedAlbums.length > 1) {
			// found and more than one element
			// @ts-ignore
			view.$wire.openAlbumsDropdown(event.clientX, event.clientY, view.select.selectedAlbums);
		} else {
			// @ts-ignore
			view.$wire.openAlbumDropdown(event.clientX, event.clientY, event.currentTarget.dataset.id);
		}
	}
}
