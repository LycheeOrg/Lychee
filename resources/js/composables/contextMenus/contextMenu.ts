import { computed, Ref, ref } from "vue";

type Selectors = {
	config?: Ref<App.Http.Resources.GalleryConfigs.AlbumConfig | undefined>;
	album?: Ref<
		| App.Http.Resources.Models.AlbumResource
		| App.Http.Resources.Models.TagAlbumResource
		| App.Http.Resources.Models.SmartAlbumResource
		| undefined
	>;
	selectedPhotosIdx?: Ref<number[]>;
	selectedPhoto?: Ref<App.Http.Resources.Models.PhotoResource | undefined>;
	selectedPhotos?: Ref<App.Http.Resources.Models.PhotoResource[]>;
	selectedAlbumIdx?: Ref<number[]>;
	selectedAlbum?: Ref<App.Http.Resources.Models.ThumbAlbumResource | undefined>;
	selectedAlbums?: Ref<App.Http.Resources.Models.ThumbAlbumResource[]>;
};

type PhotoCallbacks = {
	star: () => void;
	unstar: () => void;
	setAsCover: () => void;
	setAsHeader: () => void;
	toggleTag: () => void;
	toggleRename: () => void;
	toggleCopyTo: () => void;
	toggleMove: () => void;
	toggleDelete: () => void;
	toggleDownload: () => void;
};

type AlbumCallbacks = {
	setAsCover: () => void;
	toggleRename: () => void;
	toggleMerge: () => void;
	toggleMove: () => void;
	toggleDelete: () => void;
	toggleDownload: () => void;
};

type MenuItem = {
	is_divider?: boolean;
	label?: string;
	icon?: string;
	access?: boolean;
	callback?: () => void;
};

// Helpers functions to reduce.
function canEdit(accumulator: boolean, currentValue: App.Http.Resources.Models.ThumbAlbumResource | App.Http.Resources.Models.PhotoResource) {
	return accumulator && currentValue.rights.can_edit;
}

function canMove(accumulator: boolean, currentValue: App.Http.Resources.Models.ThumbAlbumResource) {
	return accumulator && currentValue.rights.can_move;
}

function canDelete(accumulator: boolean, currentValue: App.Http.Resources.Models.ThumbAlbumResource) {
	return accumulator && currentValue.rights.can_delete;
}

function canDownload(accumulator: boolean, currentValue: App.Http.Resources.Models.ThumbAlbumResource | App.Http.Resources.Models.PhotoResource) {
	return accumulator && currentValue.rights.can_download;
}

export function useContextMenu(selectors: Selectors, photoCallbacks: PhotoCallbacks, albumCallbacks: AlbumCallbacks) {
	const menu = ref();
	const Menu = computed<MenuItem[]>(() => {
		let menu: MenuItem[] = [];
		if (selectors.selectedPhoto !== undefined && selectors.selectedPhoto.value !== undefined) {
			menu = photoMenu();
		}
		if (selectors.selectedPhotos !== undefined && selectors.selectedPhotos.value.length > 1) {
			menu = photosMenu();
		}
		if (selectors.selectedAlbum !== undefined && selectors.selectedAlbum.value !== undefined) {
			menu = albumMenu();
		}
		if (selectors.selectedAlbums !== undefined && selectors.selectedAlbums.value.length > 1) {
			menu = albumsMenu();
		}
		return menu.filter((item) => item.access !== false);
	});

	// Define the photo Menu when only one photo is selected
	function photoMenu() {
		const menuItems = [];
		const selectedPhoto = selectors.selectedPhoto!.value as App.Http.Resources.Models.PhotoResource;

		if (selectedPhoto.is_starred) {
			menuItems.push({
				label: "gallery.menus.unstar",
				icon: "pi pi-star",
				callback: photoCallbacks.unstar,
				access: selectedPhoto.rights.can_edit,
			});
		} else {
			menuItems.push({
				label: "gallery.menus.star",
				icon: "pi pi-star",
				callback: photoCallbacks.star,
				access: selectedPhoto.rights.can_edit,
			});
		}

		if (selectors.config!.value?.is_model_album) {
			const parent_album = selectors.album!.value as App.Http.Resources.Models.AlbumResource;
			menuItems.push({
				label: "gallery.menus.set_cover",
				icon: "pi pi-id-card",
				callback: photoCallbacks.setAsCover,
				access: parent_album.rights.can_edit ?? false,
			});
			if (parent_album.header_id === selectedPhoto.id) {
				menuItems.push({
					label: "gallery.menus.remove_header",
					icon: "pi pi-image",
					callback: photoCallbacks.setAsHeader,
					access: parent_album.rights.can_edit ?? false,
				});
			} else {
				menuItems.push({
					label: "gallery.menus.set_header",
					icon: "pi pi-image",
					callback: photoCallbacks.setAsHeader,
					access: parent_album.rights.can_edit ?? false,
				});
			}
		}

		menuItems.push(
			...[
				{
					label: "gallery.menus.tag",
					icon: "pi pi-tag",
					callback: photoCallbacks.toggleTag,
					access: selectedPhoto.rights.can_edit,
				},
				{
					is_divider: true,
					access: selectedPhoto.rights.can_edit,
				},
				{
					label: "gallery.menus.rename",
					icon: "pi pi-pencil",
					callback: photoCallbacks.toggleRename,
					access: selectedPhoto.rights.can_edit,
				},
				{
					label: "gallery.menus.copy_to",
					icon: "pi pi-copy",
					callback: photoCallbacks.toggleCopyTo,
					access: selectedPhoto.rights.can_edit,
				},
				{
					label: "gallery.menus.move",
					icon: "pi pi-arrow-right-arrow-left",
					callback: photoCallbacks.toggleMove,
					access: selectedPhoto.rights.can_edit,
				},
				{
					label: "gallery.menus.delete",
					icon: "pi pi-trash",
					callback: photoCallbacks.toggleDelete,
					access: selectors.album!.value?.rights.can_delete ?? false,
				},
				{
					label: "gallery.menus.download",
					icon: "pi pi-cloud-download",
					callback: photoCallbacks.toggleDownload,
					access: selectedPhoto.rights.can_download,
				},
			],
		);

		return menuItems;
	}

	// Define the photo Menu when multiple photos are selected
	function photosMenu() {
		const menuItems = [];
		if (selectors.selectedPhotos!.value.reduce((acc, photo) => acc && photo.is_starred, true)) {
			menuItems.push({
				label: "gallery.menus.unstar_all",
				icon: "pi pi-star",
				callback: photoCallbacks.unstar,
				access: selectors.selectedPhotos!.value.reduce(canEdit, true),
			});
		} else {
			menuItems.push({
				label: "gallery.menus.star_all",
				icon: "pi pi-star",
				callback: photoCallbacks.star,
				access: selectors.selectedPhotos!.value.reduce(canEdit, true),
			});
		}

		menuItems.push(
			...[
				{
					label: "gallery.menus.tag_all",
					icon: "pi pi-tag",
					callback: photoCallbacks.toggleTag,
					access: selectors.selectedPhotos!.value.reduce(canEdit, true),
				},
				{
					is_divider: true,
					access: selectors.selectedPhotos!.value.reduce(canEdit, true),
				},
				{
					label: "gallery.menus.copy_all_to",
					icon: "pi pi-copy",
					callback: photoCallbacks.toggleCopyTo,
					access: selectors.selectedPhotos!.value.reduce(canEdit, true),
				},
				{
					label: "gallery.menus.move_all",
					icon: "pi pi-arrow-right-arrow-left",
					callback: photoCallbacks.toggleMove,
					access: selectors.selectedPhotos!.value.reduce(canEdit, true),
				},
				{
					label: "gallery.menus.delete_all",
					icon: "pi pi-trash",
					callback: photoCallbacks.toggleDelete,
					access: selectors.selectedPhotos!.value.reduce(canEdit, true),
				},
				{
					label: "gallery.menus.download_all",
					icon: "pi pi-cloud-download",
					callback: photoCallbacks.toggleDownload,
					access: selectors.selectedPhotos!.value.reduce(canDownload, true),
				},
			],
		);

		return menuItems;
	}

	function albumMenu() {
		const menuItems = [];
		const selectedAlbum = selectors.selectedAlbum!.value as App.Http.Resources.Models.ThumbAlbumResource;

		if (selectors.config?.value?.is_model_album) {
			menuItems.push({
				label: "gallery.menus.set_cover",
				icon: "pi pi-id-card",
				callback: albumCallbacks.setAsCover,
				access: selectors.album?.value?.rights.can_edit ?? false,
			});
		}

		menuItems.push(
			...[
				{
					label: "gallery.menus.rename",
					icon: "pi pi-pencil",
					callback: albumCallbacks.toggleRename,
					access: selectedAlbum.rights.can_edit ?? false,
				},
				{
					label: "gallery.menus.merge",
					icon: "pi pi-paperclip",
					callback: albumCallbacks.toggleMerge,
					access: selectedAlbum.rights.can_move ?? false,
				},
				{
					label: "gallery.menus.move",
					icon: "pi pi-arrow-right-arrow-left",
					callback: albumCallbacks.toggleMove,
					access: selectedAlbum.rights.can_move ?? false,
				},
				{
					label: "gallery.menus.delete",
					icon: "pi pi-trash",
					callback: albumCallbacks.toggleDelete,
					access: selectedAlbum.rights.can_delete ?? false,
				},
				{
					label: "gallery.menus.download",
					icon: "pi pi-cloud-download",
					callback: albumCallbacks.toggleDownload,
					access: selectedAlbum.rights.can_download ?? false,
				},
			],
		);

		return menuItems;
	}

	function albumsMenu() {
		return [
			{
				label: "gallery.menus.merge_all",
				icon: "pi pi-paperclip",
				callback: albumCallbacks.toggleMerge,
				access: selectors.selectedAlbums?.value.reduce(canMove, true),
			},
			{
				label: "gallery.menus.move_all",
				icon: "pi pi-arrow-right-arrow-left",
				callback: albumCallbacks.toggleMove,
				access: selectors.selectedAlbums?.value.reduce(canMove, true),
			},
			{
				label: "gallery.menus.delete_all",
				icon: "pi pi-trash",
				callback: albumCallbacks.toggleDelete,
				access: selectors.selectedAlbums?.value.reduce(canDelete, true),
			},
			{
				label: "gallery.menus.download_all",
				icon: "pi pi-cloud-download",
				callback: albumCallbacks.toggleDownload,
				access: selectors.selectedAlbums?.value.reduce(canDownload, true),
			},
		];
	}

	function photoMenuOpen(idx: number, e: MouseEvent): void {
		// Clear up Album selection (if any)
		selectors.selectedAlbumIdx!.value = [];

		// Check if photo was selected already.
		// If not, we replace entire selection.
		if (!selectors.selectedPhotosIdx!.value.includes(idx)) {
			selectors.selectedPhotosIdx!.value = [idx];
		}

		// Show menu
		menu.value.show(e);
	}

	function albumMenuOpen(idx: number, e: MouseEvent): void {
		// Clear up Photo selection (if any)
		if (selectors.selectedPhotosIdx !== undefined) {
			selectors.selectedPhotosIdx!.value = [];
		}

		// Check if album was selected already.
		// If not, we replace entire selection.
		if (!selectors.selectedAlbumIdx!.value.includes(idx)) {
			selectors.selectedAlbumIdx!.value = [idx];
		}

		// Show menu
		menu.value.show(e);
	}

	return { menu, Menu, photoMenuOpen, albumMenuOpen };
}
