// import Livewire from

export default { albumView };

export function albumView(nsfwAlbumsVisible_val, isFullscreen_val) {
	return {
		loginModalOpen: false,
		selectedPhotos: [],
		selectedAlbums: [],
		detailsOpen: false,
		sharingLinksOpen: false,
		nsfwAlbumsVisible: nsfwAlbumsVisible_val,
		isFullscreen: isFullscreen_val,

		silentToggle(elem, wire) {
			this[elem] = !this[elem];

			wire.silentUpdate();
		},

		handleContextPhoto(event, wire) {
			this.selectedAlbums = [];
			const photoId = event.currentTarget.dataset.id;
			const index = this.selectedPhotos.indexOf(photoId);
			if (index > -1 && this.selectedPhotos.length > 1) {
				// found and more than one element
				wire.openPhotosDropdown(event.clientX, event.clientY, this.selectedPhotos);
			} else {
				wire.openPhotoDropdown(event.clientX, event.clientY, event.currentTarget.dataset.id);
			}
		},

		handleClickPhoto(event, wire) {
			if (event.ctrlKey) {
				event.preventDefault();
				this.selectedAlbums = [];
				const photoId = event.currentTarget.dataset.id;
				const index = this.selectedPhotos.indexOf(photoId);
				if (index > -1) {
					// found
					this.selectedPhotos = this.selectedPhotos.filter((e) => e !== photoId);
				} else {
					// not found
					this.selectedPhotos.push(photoId);
				}
			}
		},

		handleContextAlbum(event, wire) {
			this.selectedPhotos = [];
			const albumId = event.currentTarget.dataset.id;
			const index = this.selectedAlbums.indexOf(albumId);
			if (index > -1 && this.selectedAlbums.length > 1) {
				// found and more than one element
				wire.openAlbumsDropdown(event.clientX, event.clientY, this.selectedAlbums);
			} else {
				wire.openAlbumDropdown(event.clientX, event.clientY, event.currentTarget.dataset.id);
			}
		},

		handleClickAlbum(event, wire) {
			if (event.ctrlKey) {
				event.preventDefault();
				this.selectedPhotos = [];
				const albumId = event.currentTarget.dataset.id;
				const index = this.selectedAlbums.indexOf(albumId);
				if (index > -1) {
					// found
					this.selectedAlbums = this.selectedAlbums.filter((e) => e !== albumId);
				} else {
					// not found
					this.selectedAlbums.push(albumId);
				}
			}
		},

		handleKeydown(event, wire, focus) {
			const skipped = ["TEXTAREA", "INPUT", "SELECT"];

			if (focus.focused() !== undefined && skipped.includes(focus.focused().nodeName)) {
				console.log("skipped: " + focus.focused().nodeName);
				return;
			} else if (focus.focused() !== undefined) {
				console.log(focus.focused().nodeName);
			}

			// h
			if (event.keyCode === 72 && !this.detailsOpen) {
				event.preventDefault();
				console.log("toggle hidden albums:", this.nsfwAlbumsVisible);
				this.silentToggle("nsfwAlbumsVisible", wire);
			}

			// i
			if (event.keyCode === 73) {
				event.preventDefault();
				this.detailsOpen = !this.detailsOpen;
			}

			// f
			if (event.keyCode === 70 && !this.detailsOpen) {
				event.preventDefault();
				this.silentToggle("isFullscreen", wire);
			}

			// l
			if (event.keyCode === 76) {
				this.loginModalOpen = true;
			}
		},
	};
}
