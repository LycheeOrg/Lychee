import { computed, ref } from "vue";

type Callbacks = {
	toggleCreateAlbum: () => void;
	toggleUpload: () => void;
	toggleImportFromLink: () => void;
	toggleUploadTrack: () => void;
	deleteTrack: () => void;
};

export function useContextMenuAlbumAdd(
	abstractAlbum:
		| App.Http.Resources.Models.AlbumResource
		| App.Http.Resources.Models.TagAlbumResource
		| App.Http.Resources.Models.SmartAlbumResource,
	callbacks: Callbacks,
) {
	const addmenu = ref(); // ! Reference to the context menu
	const addMenu = computed(function () {
		const menu = [
			{
				label: "lychee.UPLOAD_PHOTO",
				icon: "pi pi-upload",
				callback: callbacks.toggleUpload,
			},
			{
				is_divider: true,
			},
			{
				label: "lychee.IMPORT_LINK",
				icon: "pi pi-link",
				callback: callbacks.toggleImportFromLink,
			},
			// {
			// 	label: "lychee.IMPORT_DROPBOX",
			// 	icon: "pi pi-box",
			// 	callback: () => {},
			// },
			{
				is_divider: true,
			},
			{
				label: "lychee.NEW_ALBUM",
				icon: "pi pi-folder",
				callback: callbacks.toggleCreateAlbum,
			},
		];

		const album: App.Http.Resources.Models.AlbumResource = abstractAlbum as App.Http.Resources.Models.AlbumResource;
		if (album.track_url !== null) {
			menu.push({
				label: "lychee.DELETE_TRACK",
				icon: "pi pi-compass",
				callback: callbacks.deleteTrack,
			});
		} else {
			menu.push({
				label: "lychee.UPLOAD_TRACK",
				icon: "pi pi-compass",
				callback: callbacks.toggleUploadTrack,
			});
		}
		return menu;
	});

	function openAddMenu(event: Event) {
		addmenu.value.show(event);
	}

	return {
		addmenu,
		addMenu,
		openAddMenu,
	};
}
