import { computed, ComputedRef, Ref, ref } from "vue";

type Selectors = {
	config: Ref<App.Http.Resources.GalleryConfigs.AlbumConfig | null> | null;
	album: ComputedRef<
		| App.Http.Resources.Models.AlbumResource
		| App.Http.Resources.Models.TagAlbumResource
		| App.Http.Resources.Models.SmartAlbumResource
		| undefined
	> | null;
	selectedPhotosIdx: Ref<number[]> | undefined;
	selectedPhoto: ComputedRef<App.Http.Resources.Models.PhotoResource | undefined> | undefined;
	selectedPhotos: ComputedRef<App.Http.Resources.Models.PhotoResource[]> | undefined;
	selectedAlbumIdx: Ref<number[]>;
	selectedAlbum: ComputedRef<App.Http.Resources.Models.ThumbAlbumResource | undefined>;
	selectedAlbums: ComputedRef<App.Http.Resources.Models.ThumbAlbumResource[]>;
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
	callback?: () => void;
};

export function useContextMenu(selectors: Selectors, photoCallbacks: PhotoCallbacks, albumCallbacks: AlbumCallbacks) {
	const menu = ref();
	const Menu = computed<MenuItem[]>(() => {
		if (selectors.selectedPhoto !== undefined && selectors.selectedPhoto.value !== undefined) {
			return photoMenu();
		}
		if (selectors.selectedPhotos !== undefined && selectors.selectedPhotos.value.length > 0) {
			return photosMenu();
		}
		if (selectors.selectedAlbum.value !== undefined) {
			return albumMenu();
		}
		if (selectors.selectedAlbums.value.length > 0) {
			return albumsMenu();
		}
		return [];
	});

	// Define the photo Menu when only one photo is selected
	function photoMenu() {
		const menuItems = [];
		const selectedPhoto = selectors.selectedPhoto!.value as App.Http.Resources.Models.PhotoResource;

		if (selectedPhoto.is_starred) {
			menuItems.push({
				label: "lychee.UNSTAR",
				icon: "pi pi-star",
				callback: photoCallbacks.unstar,
			});
		} else {
			menuItems.push({
				label: "lychee.STAR",
				icon: "pi pi-star",
				callback: photoCallbacks.star,
			});
		}

		if (selectors.config!.value?.is_model_album) {
			menuItems.push({
				label: "lychee.SET_COVER",
				icon: "pi pi-id-card",
				callback: photoCallbacks.setAsCover,
			});
			// @ts-expect-error
			if (selectors.album.value?.header_id === selectedPhoto.id) {
				menuItems.push({
					label: "lychee.REMOVE_HEADER",
					icon: "pi pi-image",
					callback: photoCallbacks.setAsHeader,
				});
			} else {
				menuItems.push({
					label: "lychee.SET_HEADER",
					icon: "pi pi-image",
					callback: photoCallbacks.setAsHeader,
				});
			}
		}

		menuItems.push(
			...[
				{
					label: "lychee.TAG",
					icon: "pi pi-tag",
					callback: photoCallbacks.toggleTag,
				},
				{
					is_divider: true,
				},
				{
					label: "lychee.RENAME",
					icon: "pi pi-pencil",
					callback: photoCallbacks.toggleRename,
				},
				{
					label: "lychee.COPY_TO",
					icon: "pi pi-copy",
					callback: photoCallbacks.toggleCopyTo,
				},
				{
					label: "lychee.MOVE",
					icon: "pi pi-arrow-right-arrow-left",
					callback: photoCallbacks.toggleMove,
				},
				{
					label: "lychee.DELETE",
					icon: "pi pi-trash",
					callback: photoCallbacks.toggleDelete,
				},
				{
					label: "lychee.DOWNLOAD",
					icon: "pi pi-cloud-download",
					callback: photoCallbacks.toggleDownload,
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
				label: "lychee.UNSTAR_ALL",
				icon: "pi pi-star",
				callback: photoCallbacks.unstar,
			});
		} else {
			menuItems.push({
				label: "lychee.STAR_ALL",
				icon: "pi pi-star",
				callback: photoCallbacks.star,
			});
		}

		menuItems.push(
			...[
				{
					label: "lychee.TAG_ALL",
					icon: "pi pi-tag",
					callback: photoCallbacks.toggleTag,
				},
				{
					is_divider: true,
				},
				{
					label: "lychee.COPY_ALL_TO",
					icon: "pi pi-copy",
					callback: photoCallbacks.toggleCopyTo,
				},
				{
					label: "lychee.MOVE_ALL",
					icon: "pi pi-arrow-right-arrow-left",
					callback: photoCallbacks.toggleMove,
				},
				{
					label: "lychee.DELETE_ALL",
					icon: "pi pi-trash",
					callback: photoCallbacks.toggleDelete,
				},
				{
					label: "lychee.DOWNLOAD_ALL",
					icon: "pi pi-cloud-download",
					callback: photoCallbacks.toggleDownload,
				},
			],
		);

		return menuItems;
	}

	function albumMenu() {
		const menuItems = [];

		if (selectors.config?.value?.is_model_album) {
			menuItems.push({
				label: "lychee.SET_COVER",
				icon: "pi pi-folder-cover",
				callback: albumCallbacks.setAsCover,
			});
		}

		menuItems.push(
			...[
				{
					label: "lychee.RENAME",
					icon: "pi pi-pencil",
					callback: albumCallbacks.toggleRename,
				},
				{
					label: "lychee.MERGE",
					icon: "pi pi-paperclip",
					callback: albumCallbacks.toggleMerge,
				},
				{
					label: "lychee.MOVE",
					icon: "pi pi-arrow-right-arrow-left",
					callback: albumCallbacks.toggleMove,
				},
				{
					label: "lychee.DELETE",
					icon: "pi pi-trash",
					callback: albumCallbacks.toggleDelete,
				},
				{
					label: "lychee.DOWNLOAD",
					icon: "pi pi-cloud-download",
					callback: albumCallbacks.toggleDownload,
				},
			],
		);

		return menuItems;
	}

	function albumsMenu() {
		return [
			{
				label: "lychee.MERGE_ALL",
				icon: "pi pi-paperclip",
				callback: albumCallbacks.toggleMerge,
			},
			{
				label: "lychee.MOVE_ALL",
				icon: "pi pi-arrow-right-arrow-left",
				callback: albumCallbacks.toggleMove,
			},
			{
				label: "lychee.DELETE_ALL",
				icon: "pi pi-trash",
				callback: albumCallbacks.toggleDelete,
			},
			{
				label: "lychee.DOWNLOAD_ALL",
				icon: "pi pi-cloud-download",
				callback: albumCallbacks.toggleDownload,
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
