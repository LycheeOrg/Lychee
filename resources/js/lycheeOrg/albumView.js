// import Livewire from

export default { albumView };

export function albumView(nsfwAlbumsVisible_val, isFullscreen_val, canEdit_val, parent_id_val = null, albumIDs_val = [], photoIDs_val = []) {
	return {
		albumIDs: albumIDs_val,
		photoIDs: photoIDs_val,
		loginModalOpen: false,
		selectedPhotos: [],
		selectedAlbums: [],
		parent_id: parent_id_val,
		canEdit: canEdit_val,
		detailsOpen: false,
		detailsActiveTab: 0,
		sharingLinksOpen: false,
		nsfwAlbumsVisible: nsfwAlbumsVisible_val,
		isFullscreen: isFullscreen_val,

		silentToggle(elem) {
			this[elem] = !this[elem];

			this.$wire.silentUpdate();
		},

		handleContextPhoto(event) {
			if (!this.canEdit) {
				return;
			}

			this.selectedAlbums = [];
			const photoId = event.currentTarget.dataset.id;
			const index = this.selectedPhotos.indexOf(photoId);
			if (index > -1 && this.selectedPhotos.length > 1) {
				// found and more than one element
				this.$wire.openPhotosDropdown(event.clientX, event.clientY, this.selectedPhotos);
			} else {
				this.$wire.openPhotoDropdown(event.clientX, event.clientY, event.currentTarget.dataset.id);
			}
		},

		handleClickPhoto(event) {
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

			if (event.shiftKey) {
				event.preventDefault();
				this.selectedAlbums = [];
				const photoId = event.currentTarget.dataset.id;
				const lastInsertedAlbumId = this.selectedPhotos[this.selectedPhotos.length - 1];
				this.__addPhotoRange(lastInsertedAlbumId, photoId);
			}

			console.log("result:");
			console.log(this.selectedPhotos);
		},

		handleContextAlbum(event) {
			if (!this.canEdit) {
				return;
			}

			this.selectedPhotos = [];
			const albumId = event.currentTarget.dataset.id;
			const index = this.selectedAlbums.indexOf(albumId);
			if (index > -1 && this.selectedAlbums.length > 1) {
				// found and more than one element
				this.$wire.openAlbumsDropdown(event.clientX, event.clientY, this.selectedAlbums);
			} else {
				this.$wire.openAlbumDropdown(event.clientX, event.clientY, event.currentTarget.dataset.id);
			}
		},

		handleClickAlbum(event) {
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

			if (event.shiftKey) {
				event.preventDefault();
				this.selectedPhotos = [];
				const albumId = event.currentTarget.dataset.id;
				const lastInsertedAlbumId = this.selectedAlbums[this.selectedAlbums.length - 1];
				this.__addAlbumRange(lastInsertedAlbumId, albumId);
			}

			console.log("result:");
			console.log(this.selectedAlbums);
		},

		handleKeydown(event) {
			const skipped = ["TEXTAREA", "INPUT", "SELECT"];

			if (skipped.includes(document.activeElement.nodeName)) {
				console.log("skipped: " + document.activeElement.nodeName);
				return;
			}

			// h
			if (event.keyCode === 72 && !this.detailsOpen) {
				event.preventDefault();
				console.log("toggle hidden albums:", this.nsfwAlbumsVisible);
				this.silentToggle("nsfwAlbumsVisible");
				return;
			}

			// f
			if (event.keyCode === 70 && !this.detailsOpen) {
				event.preventDefault();
				this.silentToggle("isFullscreen");
				return;
			}

			// l
			if (event.keyCode === 76) {
				this.loginModalOpen = true;
				return;
			}

			// escape
			if (event.keyCode === 27) {
				if (this.detailsOpen) {
					event.preventDefault();
					this.detailsOpen = false;
				} else if (this.parent_id !== null) {
					event.preventDefault();
					this.$wire.back();
				}
				return;
			}

			if (!this.canEdit) {
				console.log("can't edit.");
				return;
			}

			// ctrl + a
			if (event.keyCode === 65 && event.ctrlKey) {
				event.preventDefault();
				if (this.selectedAlbums.length === this.albumIDs.length && this.albumIDs.length > 1) {
					console.log("select all albums (flip)");
					this.selectedAlbums = [];
					this.selectedPhotos = [...this.photoIDs];
				} else if (this.selectedPhotos.length === this.photoIDs.length && this.photoIDs.length > 1) {
					console.log("select all photos (flip)");
					this.selectedPhotos = [];
					this.selectedAlbums = [...this.albumIDs];
				} else if (this.selectedAlbums.length > 1) {
					console.log("select all albums");
					this.selectedAlbums = [...this.albumIDs];
					this.selectedPhotos = [];
				} else if (this.selectedPhotos.length > 1) {
					console.log("select all photos");
					this.selectedPhotos = [...this.photoIDs];
					this.selectedAlbums = [];
				} else if (this.albumIDs.length > 1) {
					console.log("select all albums");
					this.selectedAlbums = [...this.albumIDs];
					this.selectedPhotos = [];
				} else {
					console.log("select all photos");
					this.selectedPhotos = [...this.photoIDs];
					this.selectedAlbums = [];
				}
				return;
			}

			// n
			if (event.keyCode === 78 && !this.detailsOpen) {
				event.preventDefault();
				const params = ["forms.album.create", "", { parentID: this.parent_id }];
				this.$wire.$dispatch("openModal", params);
				return;
			}

			// u
			if (event.keyCode === 85 && !this.detailsOpen) {
				event.preventDefault();
				const params = ["forms.add.upload", "", { parentID: this.parent_id }];
				this.$wire.$dispatch("openModal", params);
				return;
			}

			// m on selected albums/photos
			if (event.keyCode === 77 && this.selectedAlbums.length > 0) {
				event.preventDefault();
				this.moveAlbums();
				return;
			}
			if (event.keyCode === 77 && this.selectedPhotos.length > 0) {
				event.preventDefault();
				this.movePhotos();
				return;
			}

			// r on selected albums
			if (event.keyCode === 82 && this.selectedAlbums.length > 0) {
				event.preventDefault();
				this.renameAlbums();
				return;
			}
			if (event.keyCode === 82 && this.selectedPhotos.length > 0) {
				event.preventDefault();
				this.renamePhotos();
				return;
			}

			// Following this point only if we are not in root.
			if (this.parent_id === null) {
				console.log("root");
				return;
			}

			// d
			if (event.keyCode === 68 && !this.detailsOpen) {
				event.preventDefault();
				this.detailsOpen = true;
				this.activeTab = 0;
				return;
			}

			// i
			if (event.keyCode === 73) {
				event.preventDefault();
				this.detailsOpen = !this.detailsOpen;
				return;
			}

			// m
			if (event.keyCode === 77 && !this.detailsOpen) {
				event.preventDefault();
				this.detailsOpen = true;
				this.detailsActiveTab = 2;
				return;
			}

			// r
			if (event.keyCode === 82 && !this.detailsOpen) {
				event.preventDefault();
				this.detailsOpen = true;
				this.detailsActiveTab = 0;
				return;
			}
		},

		__addAlbumRange(lastInsertedAlbumId, albumId) {
			if (lastInsertedAlbumId === undefined) {
				console.log("previous not found");
				return;
			}
			if (lastInsertedAlbumId === albumId) {
				console.log("same elem");
				return;
			}

			let toAppend;
			const index = this.albumIDs.indexOf(albumId);
			console.log(index);
			const indexLastInserted = this.albumIDs.indexOf(lastInsertedAlbumId);
			console.log(indexLastInserted);
			if (index < indexLastInserted) {
				toAppend = this.albumIDs.slice(index + 1, indexLastInserted);
			} else {
				toAppend = this.albumIDs.slice(indexLastInserted + 1, index);
			}
			// Push albumId at the end.
			toAppend.push(albumId);
			console.log("to append:");
			console.log(toAppend);

			this.selectedAlbums = this.selectedAlbums.reduce((acc, elem) => (toAppend.includes(elem) ? acc : [...acc, elem]), []);
			console.log("before:");
			console.log(this.selectedAlbums);
			this.selectedAlbums.push(...toAppend);
		},

		__addPhotoRange(lastInsertedPhotoId, photoId) {
			if (lastInsertedPhotoId === undefined) {
				console.log("previous not found");
				return;
			}
			if (lastInsertedPhotoId === photoId) {
				console.log("same elem");
				return;
			}

			let toAppend;
			const index = this.photoIDs.indexOf(photoId);
			console.log(index);
			const indexLastInserted = this.photoIDs.indexOf(lastInsertedPhotoId);
			console.log(indexLastInserted);
			if (index < indexLastInserted) {
				toAppend = this.photoIDs.slice(index + 1, indexLastInserted);
			} else {
				toAppend = this.photoIDs.slice(indexLastInserted + 1, index);
			}
			// Push photoId at the end.
			toAppend.push(photoId);
			console.log("to append:");
			console.log(toAppend);

			this.selectedPhotos = this.selectedPhotos.reduce((acc, elem) => (toAppend.includes(elem) ? acc : [...acc, elem]), []);
			console.log("before:");
			console.log(this.selectedPhotos);
			this.selectedPhotos.push(...toAppend);
		},

		moveAlbums() {
			const params = ["forms.album.move", "", { parentID: this.parent_id, albumIDs: this.selectedAlbums }];
			this.$wire.$dispatch("openModal", params);
		},

		mergeAlbums() {
			const params = ["forms.album.merge", "", { parentID: this.parent_id, albumIDs: this.selectedAlbums }];
			this.$wire.$dispatch("openModal", params);
		},

		renameAlbums() {
			const params = ["forms.album.rename", "", { parentID: this.parent_id, albumIDs: this.selectedAlbums }];
			this.$wire.$dispatch("openModal", params);
		},

		deleteAlbums() {
			const params = ["forms.album.delete", "", { parentID: this.parent_id, albumIDs: this.selectedAlbums }];
			this.$wire.$dispatch("openModal", params);
		},

		donwloadAlbums() {
			window.open("api/Album::getArchive?albumIDs=" + this.selectedAlbums.join(","));
		},

		copyPhotosTo() {
			const params = ["forms.photo.copy-to", "", { albumID: this.parent_id, photoIDs: this.selectedPhotos }];
			this.$wire.$dispatch("openModal", params);
		},

		movePhotos() {
			const params = ["forms.photo.move", "", { albumID: this.parent_id, photoIDs: this.selectedPhotos }];
			this.$wire.$dispatch("openModal", params);
		},

		renamePhotos() {
			const params = ["forms.photo.rename", "", { albumID: this.parent_id, photoIDs: this.selectedPhotos }];
			this.$wire.$dispatch("openModal", params);
		},

		deletePhotos() {
			const params = ["forms.photo.delete", "", { albumID: this.parent_id, photoIDs: this.selectedPhotos }];
			this.$wire.$dispatch("openModal", params);
		},

		tagPhotos() {
			const params = ["forms.photo.tag", "", { photoIDs: this.selectedPhotos }];
			this.$wire.$dispatch("openModal", params);
		},

		setCover() {
			this.$wire.setCover(this.selectedPhotos[0]);
		},

		donwloadPhotos() {
			window.open("api/Photo::getArchive?kind=ORIGINAL&photoIDs=" + this.selectedPhotos.join(","));
		},
	};
}
