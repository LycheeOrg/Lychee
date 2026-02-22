import { useAlbumStore } from "@/stores/AlbumState";
import { useLeftMenuStateStore } from "@/stores/LeftMenuState";
import { computed, Ref, ref } from "vue";

export type Selectors = {
	config?: Ref<App.Http.Resources.GalleryConfigs.AlbumConfig | undefined>;
	album?: Ref<
		| App.Http.Resources.Models.HeadAlbumResource
		| App.Http.Resources.Models.HeadTagAlbumResource
		| App.Http.Resources.Models.HeadSmartAlbumResource
		| undefined
	>;
	selectedPhotosIds?: Ref<string[]>;
	selectedPhoto?: Ref<App.Http.Resources.Models.PhotoResource | undefined>;
	selectedPhotos?: Ref<App.Http.Resources.Models.PhotoResource[]>;
	selectedAlbumsIds?: Ref<string[]>;
	selectedAlbum?: Ref<App.Http.Resources.Models.ThumbAlbumResource | undefined>;
	selectedAlbums?: Ref<App.Http.Resources.Models.ThumbAlbumResource[]>;
};

export type PhotoCallbacks = {
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

export type AlbumCallbacks = {
	setAsCover: () => void;
	toggleRename: () => void;
	toggleMerge: () => void;
	toggleMove: () => void;
	togglePin: () => void;
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
function canMove(accumulator: boolean, currentValue: App.Http.Resources.Models.ThumbAlbumResource) {
	return accumulator && currentValue.rights.can_move;
}

function canDelete(accumulator: boolean, currentValue: App.Http.Resources.Models.ThumbAlbumResource) {
	return accumulator && currentValue.rights.can_delete;
}

function canDownload(accumulator: boolean, currentValue: App.Http.Resources.Models.ThumbAlbumResource) {
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
	function photoMenu(): MenuItem[] {
		if (selectors.selectedPhoto === undefined) {
			return [];
		}

		const menuItems = [];
		const selectedPhoto = selectors.selectedPhoto.value as App.Http.Resources.Models.PhotoResource;
		const albumStore = useAlbumStore();
		const leftMenuStore = useLeftMenuStateStore();

		if (selectedPhoto.is_starred) {
			menuItems.push({
				label: "gallery.menus.unstar",
				icon: "pi pi-star",
				callback: photoCallbacks.unstar,
				access: leftMenuStore.initData?.root_album?.can_star ?? false,
			});
		} else {
			menuItems.push({
				label: "gallery.menus.star",
				icon: "pi pi-star",
				callback: photoCallbacks.star,
				access: leftMenuStore.initData?.root_album?.can_star ?? false,
			});
		}

		if (selectors.config?.value?.is_model_album === true && selectors.album !== undefined) {
			const parent_album = selectors.album.value as App.Http.Resources.Models.AlbumResource;
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
					access: albumStore.rights?.can_edit ?? false,
				},
				{
					is_divider: true,
					access: albumStore.rights?.can_edit ?? false,
				},
				{
					label: "gallery.menus.rename",
					icon: "pi pi-pen-to-square",
					callback: photoCallbacks.toggleRename,
					access: albumStore.rights?.can_edit ?? false,
				},
				{
					label: "gallery.menus.copy_to",
					icon: "pi pi-copy",
					callback: photoCallbacks.toggleCopyTo,
					access: albumStore.rights?.can_edit ?? false,
				},
				{
					label: "gallery.menus.move",
					icon: "pi pi-folder",
					callback: photoCallbacks.toggleMove,
					access: albumStore.rights?.can_edit ?? false,
				},
				{
					label: "gallery.menus.delete",
					icon: "pi pi-trash",
					callback: photoCallbacks.toggleDelete,
					access: albumStore.tagAlbum === undefined && (selectors.album?.value?.rights.can_delete ?? false),
				},
				{
					label: "gallery.menus.download",
					icon: "pi pi-cloud-download",
					callback: photoCallbacks.toggleDownload,
					access: albumStore.rights?.can_download ?? false,
				},
			],
		);

		return menuItems;
	}

	// Define the photo Menu when multiple photos are selected
	function photosMenu(): MenuItem[] {
		if (selectors.selectedPhotos === undefined) {
			return [];
		}

		const menuItems = [];
		const albumStore = useAlbumStore();
		const leftMenuStore = useLeftMenuStateStore();
		if (selectors.selectedPhotos.value.reduce((acc: boolean, photo: App.Http.Resources.Models.PhotoResource) => acc && photo.is_starred, true)) {
			menuItems.push({
				label: "gallery.menus.unstar_all",
				icon: "pi pi-star",
				callback: photoCallbacks.unstar,
				access: leftMenuStore.initData?.root_album?.can_star ?? false,
			});
		} else {
			menuItems.push({
				label: "gallery.menus.star_all",
				icon: "pi pi-star",
				callback: photoCallbacks.star,
				access: leftMenuStore.initData?.root_album?.can_star ?? false,
			});
		}

		menuItems.push(
			...[
				{
					label: "gallery.menus.tag_all",
					icon: "pi pi-tag",
					callback: photoCallbacks.toggleTag,
					access: albumStore.rights?.can_edit ?? false,
				},
				{
					is_divider: true,
					access: albumStore.rights?.can_edit ?? false,
				},
				{
					label: "gallery.menus.copy_all_to",
					icon: "pi pi-copy",
					callback: photoCallbacks.toggleCopyTo,
					access: albumStore.rights?.can_edit ?? false,
				},
				{
					label: "gallery.menus.move_all",
					icon: "pi pi-folder",
					callback: photoCallbacks.toggleMove,
					access: albumStore.rights?.can_edit ?? false,
				},
				{
					label: "gallery.menus.delete_all",
					icon: "pi pi-trash",
					callback: photoCallbacks.toggleDelete,
					access: albumStore.tagAlbum === undefined && (albumStore.rights?.can_edit ?? false),
				},
				{
					label: "gallery.menus.download_all",
					icon: "pi pi-cloud-download",
					callback: photoCallbacks.toggleDownload,
					access: albumStore.rights?.can_download ?? false,
				},
			],
		);

		return menuItems;
	}

	function albumMenu() {
		if (selectors.selectedAlbum === undefined) {
			return [];
		}

		const menuItems = [];
		const selectedAlbum = selectors.selectedAlbum.value as App.Http.Resources.Models.ThumbAlbumResource;

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
					icon: "pi pi-pen-to-square",
					callback: albumCallbacks.toggleRename,
					access: selectedAlbum.rights.can_edit ?? false,
				},
				{
					label: "gallery.menus.merge",
					icon: "pi pi-arrow-down-left-and-arrow-up-right-to-center",
					callback: albumCallbacks.toggleMerge,
					access: selectedAlbum.rights.can_move ?? false,
				},
				{
					label: "gallery.menus.move",
					icon: "pi pi-folder",
					callback: albumCallbacks.toggleMove,
					access: selectedAlbum.rights.can_move ?? false,
				},
				{
					label: selectedAlbum.is_pinned ? "gallery.menus.unpin" : "gallery.menus.pin",
					icon: "pi pi-thumbtack",
					callback: albumCallbacks.togglePin,
					access: selectedAlbum.rights.can_edit ?? false,
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
				icon: "pi pi-arrow-down-left-and-arrow-up-right-to-center",
				callback: albumCallbacks.toggleMerge,
				access: selectors.selectedAlbums?.value.reduce(canMove, true),
			},
			{
				label: "gallery.menus.move_all",
				icon: "pi pi-folder",
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

	function photoMenuOpen(photoId: string, e: MouseEvent): void {
		// Clear up Album selection (if any)
		if (selectors.selectedAlbumsIds !== undefined) {
			selectors.selectedAlbumsIds.value = [];
		}

		if (selectors.selectedPhotosIds === undefined) {
			return;
		}

		// Check if photo was selected already.
		// If not, we replace entire selection.
		if (!selectors.selectedPhotosIds.value.includes(photoId)) {
			selectors.selectedPhotosIds.value = [photoId]; // This allows to add to selection photos which should not be selected.
			// Fix me later.
		}

		// Show menu
		menu.value.show(e);
	}

	function albumMenuOpen(e: MouseEvent, albumId: string): void {
		// Clear up Photo selection (if any)
		if (selectors.selectedPhotosIds !== undefined) {
			selectors.selectedPhotosIds.value = [];
		}

		if (selectors.selectedAlbumsIds === undefined) {
			return;
		}

		// Check if album was selected already.
		// If not, we replace entire selection.
		if (!selectors.selectedAlbumsIds.value.includes(albumId)) {
			selectors.selectedAlbumsIds.value = [albumId]; // This allows to add to the selection albums which should not be selected.
			// Fix me later.
		}

		// Show menu
		menu.value.show(e);
	}

	return { menu, Menu, photoMenuOpen, albumMenuOpen };
}
