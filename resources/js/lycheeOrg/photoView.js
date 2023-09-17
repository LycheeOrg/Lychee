// import Livewire from

export default { photoView };

export function photoView(detailsOpen_val, isFullscreen_val, has_description_val, overlayType_val) {
	return {
		detailsOpen: detailsOpen_val,
		isFullscreen: isFullscreen_val,
		has_description: has_description_val,
		overlayType: overlayType_val,
		editOpen: false,
		donwloadOpen: false,

		silentToggle(elem, wire) {
			this[elem] = !this[elem];

			wire.silentUpdate();
		},

		rotateOverlay() {
			switch (this.overlayType) {
				case "exif":
					this.overlayType = "date";
					break;
				case "date":
					if (this.has_description) {
						this.overlayType = "description";
					} else {
						this.overlayType = "none";
					}
					break;
				case "description":
					this.overlayType = "none";
					break;
				default:
					this.overlayType = "exif";
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

			// del (46) or backspace (8)
			if (event.ctrlKey && (event.keyCode === 46 || event.keyCode === 8)) {
				wire.delete();
			}

			// i
			if (event.keyCode === 73) {
				event.preventDefault();
				this.detailsOpen = !this.detailsOpen;
				this.editOpen = false;
			}

			// e
			if (event.keyCode === 69) {
				event.preventDefault();
				this.detailsOpen = false;
				this.editOpen = !this.editOpen;
			}

			// f
			if (event.keyCode === 70) {
				event.preventDefault();
				this.silentToggle("isFullscreen", wire);
			}

			// m
			if (event.ctrlKey && event.keyCode === 77) {
				wire.move();
			}

			// o
			if (event.keyCode === 79) {
				event.preventDefault();
				this.rotateOverlay();
			}

			// s
			if (event.keyCode === 83) {
				wire.set_star();
			}

			// left arrow
			if (event.keyCode === 37 && event.ctrlKey) {
				wire.rotate_ccw();
			}

			// right arrow
			if (event.keyCode === 39 && event.ctrlKey) {
				wire.rotate_cw();
			}
		},
	};
}

// 	{
// 		loginModalOpen:false,
// 		selectedPhotos: [],
// 		selectedAlbums: [],
// 		detailsOpen: false,
// 		sharingLinksOpen: false,
// 		nsfwAlbumsVisible: nsfwAlbumsVisible_val,
// 		isFullscreen: isFullscreen_val,

// 		silentToggle(elem, wire) {
// 			this[elem] = !this[elem];

// 			wire.silentUpdate();
// 		},

// 		handleContextPhoto(event, wire) {
// 			this.selectedAlbums = [];
// 			const photoId = event.currentTarget.dataset.id;
// 			const index = this.selectedPhotos.indexOf(photoId);
// 			if (index > -1 && this.selectedPhotos.length > 1) {
// 				// found and more than one element
// 				wire.openPhotosDropdown(event.clientX, event.clientY, this.selectedPhotos);
// 			} else {
// 				wire.openPhotoDropdown(event.clientX, event.clientY, event.currentTarget.dataset.id);
// 			}
// 		},

// 		handleClickPhoto(event, wire) {
// 			if (event.ctrlKey) {
// 				event.preventDefault();
// 				this.selectedAlbums = [];
// 				const photoId = event.currentTarget.dataset.id;
// 				const index = this.selectedPhotos.indexOf(photoId);
// 				if (index > -1) {
// 					// found
// 					this.selectedPhotos = this.selectedPhotos.filter((e) => e !== photoId);
// 				} else {
// 					// not found
// 					this.selectedPhotos.push(photoId);
// 				}
// 			}
// 		},

// 		handleContextAlbum(event, wire) {
// 			this.selectedPhotos = [];
// 			const albumId = event.currentTarget.dataset.id;
// 			const index = this.selectedAlbums.indexOf(albumId);
// 			if (index > -1 && this.selectedAlbums.length > 1) {
// 				// found and more than one element
// 				wire.openAlbumsDropdown(event.clientX, event.clientY, this.selectedAlbums);
// 			} else {
// 				wire.openAlbumDropdown(event.clientX, event.clientY, event.currentTarget.dataset.id);
// 			}
// 		},

// 		handleClickAlbum(event, wire) {
// 			if (event.ctrlKey) {
// 				event.preventDefault();
// 				this.selectedPhotos = [];
// 				const albumId = event.currentTarget.dataset.id;
// 				const index = this.selectedAlbums.indexOf(albumId);
// 				if (index > -1) {
// 					// found
// 					this.selectedAlbums = this.selectedAlbums.filter((e) => e !== albumId);
// 				} else {
// 					// not found
// 					this.selectedAlbums.push(albumId);
// 				}
// 			}
// 		},

// 		handleKeydown(event, wire, focus) {
// 			const skipped = ["TEXTAREA", "INPUT", "SELECT"];

// 			if (focus.focused() !== undefined && skipped.includes(focus.focused().nodeName)) {
// 				console.log("skipped: " + focus.focused().nodeName);
// 				return;
// 			} else if (focus.focused() !== undefined) {
// 				console.log(focus.focused().nodeName);
// 			}

// 			// h
// 			if (event.keyCode === 72 && !this.detailsOpen) {
// 				event.preventDefault();
// 				console.log("toggle hidden albums:", this.nsfwAlbumsVisible);
// 				this.silentToggle("nsfwAlbumsVisible", wire);
// 			}

// 			// i
// 			if (event.keyCode === 73) {
// 				event.preventDefault();
// 				this.detailsOpen = !this.detailsOpen;
// 			}

// 			// f
// 			if (event.keyCode === 70 && !this.detailsOpen) {
// 				event.preventDefault();
// 				this.silentToggle("isFullscreen", wire);
// 			}

// 			// l
// 			if (event.keyCode === 76) { this.loginModalOpen = true }

// 		},
// 	};
// }
