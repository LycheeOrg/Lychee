import Selection from "@/lycheeOrg/actions/selection";
import AlbumActions from "@/lycheeOrg/actions/albumActions";
import type { Alpine } from "alpinejs";
import type { AlbumRightsDTO, Layouts, OverlayTypes, Photo } from "@/lycheeOrg/backend";
import { AlbumView } from "./types";
import Keybindings from "@/lycheeOrg/actions/keybindings";
import AlbumFlags from "@/lycheeOrg/flags/albumFlags";
import PhotoFlagsView from "@/lycheeOrg/flags/photoFlags";
import PhotoLayout from "@/lycheeOrg/layouts/PhotoLayout";
import SwipeActions from "@/lycheeOrg/actions/swipeActions";

export const albumView = (Alpine: Alpine) =>
	Alpine.data(
		"albumView",
		(
			base_url: string,
			nsfwAlbumsVisible: boolean,
			isFullscreen: boolean,
			isPhotoDetailsOpen: boolean,
			rights: AlbumRightsDTO,
			parent_id: string | null = null,
			albumIDs: string[] = [],
			photos: Photo[] = [],
			selectedPhoto: string = "",
			overlayType: OverlayTypes = "none",
			layouts: Layouts = {
				photos_layout: "square",
				photo_layout_grid_column_width: 250,
				photo_layout_justified_row_height: 320,
				photo_layout_masonry_column_width: 300,
				photo_layout_square_column_width: 200,
				photo_layout_gap: 12,
			},
			isSearch: boolean = false,
		): AlbumView => ({
			base_url: base_url,
			isSearch: isSearch,
			select: new Selection(albumIDs, photos, rights.can_edit),
			actions: new AlbumActions(),
			keybinds: new Keybindings(),
			albumFlags: new AlbumFlags(nsfwAlbumsVisible),
			photoFlags: new PhotoFlagsView(isPhotoDetailsOpen, overlayType),
			rights: rights,
			loginModalOpen: false,
			parent_id: parent_id,
			isFullscreen: isFullscreen,
			photoLayout: new PhotoLayout(layouts),
			photo_id: null,
			swiper: new SwipeActions(),

			init() {
				this.swiper.register(this);
				console.log("init albumView!");
				if (selectedPhoto !== "" && selectedPhoto !== null) {
					this.$store.photo = Selection.getPhoto(selectedPhoto);
					if (this.$store.photo === undefined) {
						console.log(selectedPhoto + " not found!");
						return;
					}
					// Just make sure that the photo actually exists.
					this.photo_id = (this.$store.photo as Photo).id;
				}

				this.photoLayout.activateLayout();

				// TODO: See if we really need this $watch.
				this.$watch("photoLayout.type", (value, oldValue) => {
					if (value !== oldValue) {
						this.photoLayout.activateLayout();
					}
				});
			},

			toggleFullScreen() {
				this.isFullscreen = !this.isFullscreen;

				// @ts-expect-error
				this.$wire.silentUpdate();
			},

			toggleNSFW() {
				this.albumFlags.areNsfwVisible = !this.albumFlags.areNsfwVisible;
				// @ts-expect-error
				this.$wire.silentUpdate();
			},

			toggleDetails() {
				this.albumFlags.isDetailsOpen = !this.albumFlags.isDetailsOpen;
			},

			handleContextPhoto(event: MouseEvent) {
				// @ts-expect-error
				this.actions.handleContextPhoto(event, this);
			},

			handleClickPhoto(event: MouseEvent) {
				// @ts-expect-error
				this.actions.handleClickPhoto(event, this);
			},

			handleContextAlbum(event: MouseEvent) {
				// @ts-expect-error
				this.actions.handleContextAlbum(event, this);
			},

			handlePopState(event: PopStateEvent) {
				const photoId = event.state.photoId;
				const photo = Selection.getPhoto(photoId);
				if (photo === undefined) {
					this.photo_id = null;
					return;
				}
				this.photo_id = photo.id;
			},

			handleKeydown(event: KeyboardEvent) {
				const skipped = ["TEXTAREA", "INPUT", "SELECT"];

				if (document.activeElement !== null && skipped.includes(document.activeElement.nodeName)) {
					console.log("skipped: " + document.activeElement.nodeName);
					return;
				}

				// [h] hide
				// [f] fullscreen
				// [l] login
				// [?] keybinds
				// [esc] back
				// @ts-expect-error
				if (this.keybinds.handleGlobalKeydown(event, this)) {
					return;
				}

				// [ ->] next
				// [ <-] previous
				// [i] info
				// [o] overlay
				// [e] edit
				// [m] move
				// [ctrl + del] delete
				// [s] Star
				// [ctrl + ->] rotate CW
				// [ctrl + <-] rotate CCW
				// @ts-expect-error
				if (this.photo_id !== null && this.keybinds.handlePhotoKeyDown(event, this)) {
					return;
				}

				// ctrl + a
				if (this.select.handleKeydown(event)) {
					return;
				}

				// [n] new
				// [u] upload
				// [m] move (if selection is active)
				// [r] rename (if selection is active)
				// [d] description
				// [i] info
				// [m] move (without select)
				// [r] rename (without select)
				// @ts-expect-error
				this.keybinds.handleAlbumKeydown(event, this);
			},

			moveAlbums() {
				const params = ["forms.album.move", "", { parentID: this.parent_id, albumIDs: this.select.selectedAlbums }];
				Livewire.dispatch("openModal", params);
			},

			mergeAlbums() {
				const params = ["forms.album.merge", "", { parentID: this.parent_id, albumIDs: this.select.selectedAlbums }];
				Livewire.dispatch("openModal", params);
			},

			renameAlbums() {
				const params = ["forms.album.rename", "", { parentID: this.parent_id, albumIDs: this.select.selectedAlbums }];
				Livewire.dispatch("openModal", params);
			},

			deleteAlbums() {
				const params = ["forms.album.delete", "", { parentID: this.parent_id, albumIDs: this.select.selectedAlbums }];
				Livewire.dispatch("openModal", params);
			},

			donwloadAlbums() {
				window.open("api/Album::getArchive?albumIDs=" + this.select.selectedAlbums.join(","));
			},

			copyPhotosTo() {
				const params = ["forms.photo.copy-to", "", { albumID: this.parent_id, photoIDs: this.select.selectedPhotos }];
				Livewire.dispatch("openModal", params);
			},

			movePhotos() {
				const params = ["forms.photo.move", "", { albumID: this.parent_id, photoIDs: this.select.selectedPhotos }];
				Livewire.dispatch("openModal", params);
			},

			renamePhotos() {
				const params = ["forms.photo.rename", "", { albumID: this.parent_id, photoIDs: this.select.selectedPhotos }];
				Livewire.dispatch("openModal", params);
			},

			deletePhotos() {
				const params = ["forms.photo.delete", "", { albumID: this.parent_id, photoIDs: this.select.selectedPhotos }];
				Livewire.dispatch("openModal", params);
			},

			tagPhotos() {
				const params = ["forms.photo.tag", "", { photoIDs: this.select.selectedPhotos }];
				Livewire.dispatch("openModal", params);
			},

			starPhotos() {
				// @ts-ignore
				this.$wire.setStar(this.select.selectedPhotos);
				this.select.selectedPhotos.forEach((photo_id) => {
					(Selection.getPhoto(photo_id) as Photo).is_starred = true;
				});
			},

			unstarPhotos() {
				// @ts-ignore
				this.$wire.unsetStar(this.select.selectedPhotos);
				this.select.selectedPhotos.forEach((photo_id) => {
					(Selection.getPhoto(photo_id) as Photo).is_starred = false;
				});
			},

			setCover() {
				// @ts-ignore
				this.$wire.setCover(this.select.selectedPhotos[0]);
			},

			donwloadPhotos() {
				window.open("api/Photo::getArchive?kind=ORIGINAL&photoIDs=" + this.select.selectedPhotos.join(","));
			},

			rotateOverlay() {
				if (this.$store.photo === undefined) {
					console.log("undefined");
					return;
				}
				this.photoFlags.rotateOverlay();
			},

			goTo(photoId: string | null): void {
				this.albumFlags.isDetailsOpen = false;
				const elder_id = this.parent_id ?? (this.$store.photo as Photo | null)?.album_id;

				if (photoId === null) {
					this.$store.photo = undefined;
					this.photo_id = null;
					this.history({}, this.base_url + "/" + elder_id);
					return;
				}

				this.$store.photo = Selection.getPhoto(photoId);
				if (this.$store.photo === undefined) {
					console.log(photoId + " not found!");
					return;
				}
				console.log(this.$store.photo);
				this.photo_id = photoId;
				this.history({ photoId: photoId }, this.base_url + "/" + elder_id + "/" + photoId);
				this.$dispatch("photo-updated");
			},

			history(obj: object, url: string) {
				if (this.isSearch !== true) {
					history.pushState(obj, "", url);
				}
			},

			previous(): void {
				const previousId = (this.$store.photo as Photo | null)?.previous_photo_id ?? null;
				this.goTo(previousId);
			},

			next(): void {
				const nextId = (this.$store.photo as Photo | null)?.next_photo_id ?? null;
				this.goTo(nextId);
			},

			async toggleStar(): Promise<void> {
				if (this.$store.photo === undefined) {
					return;
				}

				// @ts-expect-error
				const success = await this.$wire.toggleStar([this.photo.id]);
				if (success) {
					(this.$store.photo as Photo).is_starred = !(this.$store.photo as Photo).is_starred;
				}
			},

			movePhoto(): void {
				if (this.$store.photo === undefined) {
					return;
				}
				const params = ["forms.photo.move", "", { photoIDs: [(this.$store.photo as Photo).id], albumID: this.parent_id }];
				Livewire.dispatch("openModal", params);
			},

			deletePhoto(): void {
				if (this.$store.photo === undefined) {
					return;
				}

				const params = ["forms.photo.delete", "", { photoIDs: [(this.$store.photo as Photo).id], albumID: this.parent_id }];
				Livewire.dispatch("openModal", params);
			},

			downloadPhoto(): void {
				if (this.$store.photo === undefined) {
					return;
				}

				const params = ["forms.photo.download", "", { photoIDs: [(this.$store.photo as Photo).id], albumID: this.parent_id }];
				Livewire.dispatch("openModal", params);
			},

			rotatePhotoCCW(): void {
				if (this.$store.photo === undefined) {
					return;
				}

				// @ts-expect-error
				this.$wire.rotate_ccw((this.$store.photo as Photo).id);
			},

			rotatePhotoCW(): void {
				if (this.$store.photo === undefined) {
					return;
				}

				// @ts-expect-error
				this.$wire.rotate_cw((this.$store.photo as Photo).id);
			},
		}),
	);
