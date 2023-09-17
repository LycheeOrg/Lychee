// import Livewire from

export default { albumView };

export function albumView(nsfwAlbumsVisible_val, isFullscreen_val, canEdit_val, parent_id_val = null) {
	return {
		loginModalOpen: false,
		selectedPhotos: [],
		selectedAlbums: [],
		parent_id:  parent_id_val,
		canEdit: canEdit_val,
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
			if (!this.canEdit) {
				return;
			}
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
			if (!this.canEdit) {
				return;
			}

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
			if (!this.canEdit) {
				return;
			}

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
			if (!this.canEdit) {
				return;
			}

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

		handleKeydown(event, wire) {
			const skipped = ["TEXTAREA", "INPUT", "SELECT"];

			if (skipped.includes(document.activeElement.nodeName)) {
				console.log("skipped: " + document.activeElement.nodeName);
				return;
			}
			console.log(document.activeElement.nodeName);

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

			// escape
			if (event.keyCode === 27) {
				if (this.detailsOpen) {
					event.preventDefault();
					this.detailsOpen = false;
				} else if(this.parent_id !== null) {
					event.preventDefault();
					wire.back();
				}
			}

			console.log(this.canEdit);
			if (!this.canEdit) {
				return;
			}			

			// n
			if (event.keyCode === 78 && !this.detailsOpen) {
				event.preventDefault();
				const params = ["forms.album.create", "", {"parentID": this.parent_id}];
				wire.$dispatch('openModal', params);
			}
			
			if (this.parent_id === null) {
				console.log("root")
				return;
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
