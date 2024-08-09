import type { Photo } from "@/lycheeOrg/backend";

export interface PhotoIDArray {
	[key: string]: number;
}
export default class Selection {
	selectedAlbums: string[];
	selectedPhotos: string[];
	private canEdit: boolean;

	constructor(albumIDs: string[], photos: Photo[], canEdit: boolean = false) {
		Alpine.store("photos", photos);
		Alpine.store("albumIDs", albumIDs);
		this.selectedPhotos = [];
		this.selectedAlbums = [];
		this.canEdit = canEdit;
		let photoIDs: string[] = [];
		photos.forEach((elem: Photo, idx) => (photoIDs[idx] = elem.id));
		Alpine.store("photoIDs", photoIDs);
	}

	updatePhotos(photos: Photo[]) {
		Alpine.store("photos", photos);
		this.selectedPhotos = [];
		let photoIDs: string[] = [];
		photos.forEach((elem: Photo, idx) => (photoIDs[idx] = elem.id));
		Alpine.store("photoIDs", photoIDs);
	}

	static getPhoto(photoId: string | null): Photo | undefined {
		if (photoId === null) {
			return undefined;
		}
		return (Alpine.store("photos") as Photo[]).find(({ id }) => id === photoId);
	}

	handleClickPhoto(event: MouseEvent): boolean {
		if (!this.canEdit) {
			return false;
		}

		if (event.currentTarget === null) {
			return false;
		}

		if (event.ctrlKey) {
			event.preventDefault();
			this.selectedAlbums = [];
			// @ts-ignore
			const photoId: string = event.currentTarget.dataset.id;
			const index: number = this.selectedPhotos.indexOf(photoId);
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
			// @ts-ignore
			const photoId = event.currentTarget.dataset.id;
			const lastInsertedAlbumId = this.selectedPhotos[this.selectedPhotos.length - 1];
			this.addPhotoRange(lastInsertedAlbumId, photoId);
		}

		console.log("result:");
		console.log(this.selectedPhotos);

		return true;
	}

	handleClickAlbum(event: MouseEvent): boolean {
		if (!this.canEdit) {
			return false;
		}

		if (event.currentTarget === null) {
			return false;
		}

		if (event.ctrlKey) {
			event.preventDefault();
			this.selectedPhotos = [];
			// @ts-ignore
			const albumId: string = event.currentTarget.dataset.id;
			const index: number = this.selectedAlbums.indexOf(albumId);
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
			// @ts-ignore
			const albumId: string = event.currentTarget.dataset.id;
			const lastInsertedAlbumId: string = this.selectedAlbums[this.selectedAlbums.length - 1];
			this.addAlbumRange(lastInsertedAlbumId, albumId);
		}

		console.log("result:");
		console.log(this.selectedAlbums);

		return true;
	}

	handleKeydown(event: KeyboardEvent): boolean {
		const skipped = ["TEXTAREA", "INPUT", "SELECT"];

		if (document.activeElement !== null && skipped.includes(document.activeElement.nodeName)) {
			console.log("skipped: " + document.activeElement.nodeName);
			return false;
		}

		if (!this.canEdit) {
			console.log("can't edit.");
			return false;
		}

		// ctrl + a
		if (event.key === "a" && event.ctrlKey) {
			event.preventDefault();
			if (this.selectedAlbums.length === (Alpine.store("albumIDs") as string[]).length && (Alpine.store("albumIDs") as string[]).length > 1) {
				// Flip => select photos instead
				return this.selectAllPhotos();
			}
			if (this.selectedPhotos.length === (Alpine.store("photoIDs") as string[]).length && (Alpine.store("photoIDs") as string[]).length > 1) {
				// Flip => select albums instead
				return this.selectAllAlbums();
			}
			if (this.selectedAlbums.length > 1) {
				// Select all albums (if one is already selected)
				return this.selectAllAlbums();
			}
			if (this.selectedPhotos.length > 1) {
				// Select all photos (if one is already selected)
				return this.selectAllPhotos();
			}
			if ((Alpine.store("albumIDs") as string[]).length > 1) {
				// Select all albums (if one exists and nothing is selected)
				return this.selectAllAlbums();
			}
			// Select all photos by default
			return this.selectAllPhotos();
		}

		return false;
	}

	private selectAllPhotos(): true {
		console.log("select all photos");
		this.selectedPhotos = [...(Alpine.store("photoIDs") as string[])];
		this.selectedAlbums = [];
		return true;
	}

	private selectAllAlbums(): true {
		console.log("select all albums");
		this.selectedAlbums = [...(Alpine.store("albumIDs") as string[])];
		this.selectedPhotos = [];
		return true;
	}

	private addAlbumRange(lastInsertedAlbumId: string, albumId: string): void {
		if (lastInsertedAlbumId === undefined) {
			console.log("previous not found");
			return;
		}
		if (lastInsertedAlbumId === albumId) {
			console.log("same elem");
			return;
		}
		let toAppend: string[];
		const index = (Alpine.store("albumIDs") as string[]).indexOf(albumId);
		const indexLastInserted = (Alpine.store("albumIDs") as string[]).indexOf(lastInsertedAlbumId);
		if (index < indexLastInserted) {
			toAppend = (Alpine.store("albumIDs") as string[]).slice(index + 1, indexLastInserted);
		} else {
			toAppend = (Alpine.store("albumIDs") as string[]).slice(indexLastInserted + 1, index);
		}
		// Push albumId at the end.
		toAppend.push(albumId);
		console.log("to append:");
		console.log(toAppend);

		this.selectedAlbums = this.selectedAlbums.reduce((acc: string[], elem: string) => (toAppend.includes(elem) ? acc : [...acc, elem]), []);
		console.log("before:");
		console.log(this.selectedAlbums);
		this.selectedAlbums.push(...toAppend);
	}

	private addPhotoRange(lastInsertedPhotoId: string, photoId: string): void {
		if (lastInsertedPhotoId === undefined) {
			console.log("previous not found");
			return;
		}
		if (lastInsertedPhotoId === photoId) {
			console.log("same elem");
			return;
		}

		let toAppend: string[];
		const index = (Alpine.store("photoIDs") as string[]).indexOf(photoId);
		console.log(index);
		const indexLastInserted = (Alpine.store("photoIDs") as string[]).indexOf(lastInsertedPhotoId);
		console.log(indexLastInserted);
		if (index < indexLastInserted) {
			toAppend = (Alpine.store("photoIDs") as string[]).slice(index + 1, indexLastInserted);
		} else {
			toAppend = (Alpine.store("photoIDs") as string[]).slice(indexLastInserted + 1, index);
		}
		// Push photoId at the end.
		toAppend.push(photoId);
		console.log("to append:");
		console.log(toAppend);

		this.selectedPhotos = this.selectedPhotos.reduce((acc: string[], elem: string) => (toAppend.includes(elem) ? acc : [...acc, elem]), []);
		console.log("before:");
		console.log(this.selectedPhotos);
		this.selectedPhotos.push(...toAppend);
	}

	areSelectedPhotosAllStarred(): boolean {
		let allStarred = true;
		this.selectedPhotos.forEach((v) => {
			allStarred = allStarred && (Selection.getPhoto(v) as Photo).is_starred;
		});

		return allStarred;
	}
}
