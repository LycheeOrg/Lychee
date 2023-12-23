import type { AlbumView } from "@/data/views/types";

export default class Keybindings {
	handleGlobalKeydown(event: KeyboardEvent, view: AlbumView): boolean {
		const skipped = ["TEXTAREA", "INPUT", "SELECT"];

		if (document.activeElement !== null && skipped.includes(document.activeElement.nodeName)) {
			console.log("skipped: " + document.activeElement.nodeName);
			return false;
		}

		if (event.key === "h" && !view.albumFlags.isDetailsOpen) {
			event.preventDefault();
			console.log("toggle hidden albums:", view.albumFlags.areNsfwVisible);
			view.toggleNSFW();
			return true;
		}

		if (event.key === "f" && !view.albumFlags.isDetailsOpen) {
			event.preventDefault();
			view.toggleFullScreen();
			return true;
		}

		if (event.key === "l") {
			view.loginModalOpen = true;
			console.log("open login");
			return true;
		}

		if (event.key === "/" && event.ctrlKey === true) {
			event.preventDefault();
			// @ts-expect-error
			Alpine.navigate("/livewire/search");
		}

		if (event.key === "Escape") {
			if (view.photoFlags.isEditOpen) {
				event.preventDefault();
				view.photoFlags.isEditOpen = false;
			} else if (Alpine.store("photo") !== undefined) {
				event.preventDefault();
				view.goTo(null);
			} else if (view.albumFlags.isDetailsOpen) {
				event.preventDefault();
				view.albumFlags.isDetailsOpen = false;
			} else if (view.parent_id !== null) {
				event.preventDefault();
				const url = document.getElementById("backButton")?.getAttribute("href");
				// @ts-expect-error
				Alpine.navigate(url);
			} else if (view.isSearch) {
				event.preventDefault();
				const url = document.getElementById("backButton")?.getAttribute("href");
				// @ts-expect-error
				Alpine.navigate(url);
			}
			return true;
		}

		return false;
	}

	handleAlbumKeydown(event: KeyboardEvent, view: AlbumView) {
		const skipped = ["TEXTAREA", "INPUT", "SELECT"];

		if (document.activeElement !== null && skipped.includes(document.activeElement.nodeName)) {
			console.log("skipped: " + document.activeElement.nodeName);
			return false;
		}

		if (!view.rights.can_edit) {
			console.log("can't edit.");
			return false;
		}

		// n
		if (event.key === "n" && !view.albumFlags.isDetailsOpen) {
			event.preventDefault();
			const params = ["forms.album.create", "", { parentID: view.parent_id }];
			// @ts-ignore
			view.$wire.$dispatch("openModal", params);
			return true;
		}

		// u
		if (event.key === "u" && !view.albumFlags.isDetailsOpen) {
			event.preventDefault();
			const params = ["forms.add.upload", "", { parentID: view.parent_id }];
			// @ts-ignore
			view.$wire.$dispatch("openModal", params);
			return true;
		}

		// m on selected albums
		if (event.key === "m" && view.select.selectedAlbums.length > 0) {
			event.preventDefault();
			view.moveAlbums();
			return true;
		}
		// m on selected photos
		if (event.key === "m" && view.select.selectedPhotos.length > 0) {
			event.preventDefault();
			view.movePhotos();
			return true;
		}

		// r on selected albums
		if (event.key === "r" && view.select.selectedAlbums.length > 0) {
			event.preventDefault();
			view.renameAlbums();
			return true;
		}
		// r on selected photos
		if (event.key === "r" && view.select.selectedPhotos.length > 0) {
			event.preventDefault();
			view.renamePhotos();
			return true;
		}

		// Following this point only if we are not in root.
		if (view.parent_id === null) {
			console.log("root");
			return true;
		}

		// d
		if (event.key === "d" && !view.albumFlags.isDetailsOpen) {
			event.preventDefault();
			view.albumFlags.isDetailsOpen = true;
			view.albumFlags.activeTab = 0;
			return true;
		}

		// i
		if (event.key === "i") {
			event.preventDefault();
			view.albumFlags.isDetailsOpen = !view.albumFlags.isDetailsOpen;
			return true;
		}

		// m
		if (event.key === "m" && !view.albumFlags.isDetailsOpen) {
			event.preventDefault();
			view.albumFlags.isDetailsOpen = true;
			view.albumFlags.activeTab = 2;
			return true;
		}

		// r
		if (event.key === "r" && !view.albumFlags.isDetailsOpen) {
			event.preventDefault();
			view.albumFlags.isDetailsOpen = true;
			view.albumFlags.activeTab = 0;
			return true;
		}

		return false;
	}

	handlePhotoKeyDown(event: KeyboardEvent, view: AlbumView): boolean {
		if (Alpine.store("photo") === undefined) {
			return false;
		}

		// left arrow (without ctrKey !!)
		if (event.key === "ArrowLeft" && !event.ctrlKey) {
			view.previous();
			return true;
		}

		// right arrow (without ctrKey !!)
		if (event.key === "ArrowRight" && !event.ctrlKey) {
			view.next();
			return true;
		}

		// i
		if (event.key === "i") {
			console.log("toggle details photo");
			event.preventDefault();
			view.photoFlags.isEditOpen = false;
			view.photoFlags.isDetailsOpen = !view.photoFlags.isDetailsOpen;
			return true;
		}

		// o
		if (event.key === "o") {
			view.rotateOverlay();
			return true;
		}

		if (!view.rights.can_edit) {
			console.log("can't edit.");
			return false;
		}

		// del or backspace
		if (event.ctrlKey && (event.key === "Delete" || event.key === "Backspace")) {
			view.deletePhoto();
			return true;
		}

		// e
		if (event.key === "e") {
			view.photoFlags.isDetailsOpen = false;
			view.photoFlags.isEditOpen = !view.photoFlags.isEditOpen;
			return true;
		}

		// m
		if (event.key === "m") {
			view.movePhoto();
			return true;
		}

		// s
		if (event.key === "s") {
			view.toggleStar();
			return true;
		}

		// ctrl + left arrow
		if (event.key === "ArrowLeft" && event.ctrlKey) {
			view.rotatePhotoCCW();
			return true;
		}

		// ctrl + right arrow
		if (event.key === "ArrowRight" && event.ctrlKey) {
			view.rotatePhotoCW();
			return true;
		}

		return false;
	}
}
