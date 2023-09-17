// import Livewire from

export default { albumView };

export function albumView(nsfwAlbumsVisible_val, isFullscreen_val, hasDetails_val = false) {
	return {
		loginModalOpen: false,
		selectedPhotos: [],
		selectedAlbums: [],
		hasDetails:  hasDetails_val,
		detailsOpen: false,
		detailsActiveTab: 0,
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

			// f
			if (event.keyCode === 70 && !this.detailsOpen) {
				event.preventDefault();
				this.silentToggle("isFullscreen", wire);
			}

			// l
			if (event.keyCode === 76) {
				this.loginModalOpen = true;
			}

			// No details. we end there (most likely gallery page)
			if (!this.hasDetails) {
				return;
			}

			// escape
			if (event.keyCode === 27) {
				if (this.detailsOpen) {
					event.preventDefault();
					this.detailsOpen = false;
				} else {
					event.preventDefault();
					wire.back();
				}
			}

			// d
			if (event.keyCode === 68 && !this.detailsOpen) {
				event.preventDefault();
				this.detailsOpen = true;
				this.activeTab = 0;
			}

			// i
			if (event.keyCode === 73) {
				event.preventDefault();
				this.detailsOpen = !this.detailsOpen;
			}

			// m
			if (event.keyCode === 77 && !this.detailsOpen) {
				event.preventDefault();
				this.detailsOpen = true;
				this.detailsActiveTab = 2;
			}

			// r
			if (event.keyCode === 82 && !this.detailsOpen) {
				event.preventDefault();
				this.detailsOpen = true;
				this.detailsActiveTab = 0;
			}
		},
	};
}
