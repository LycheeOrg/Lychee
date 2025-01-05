import { computed, Ref, ref } from "vue";

export type AddMenuItem =
	| {
			label: string;
			icon: string;
			callback: () => void;
			if?: boolean;
	  }
	| {
			is_divider: boolean;
			if?: boolean;
	  };

type Callbacks = {
	toggleCreateAlbum: () => void;
	toggleUpload: () => void;
	toggleImportFromLink: () => void;
	toggleUploadTrack: () => void;
	deleteTrack: () => void;
	toggleImportFromDropbox: () => void;
};

export function useContextMenuAlbumAdd(
	abstractAlbum:
		| App.Http.Resources.Models.AlbumResource
		| App.Http.Resources.Models.TagAlbumResource
		| App.Http.Resources.Models.SmartAlbumResource,
	config: App.Http.Resources.GalleryConfigs.AlbumConfig,
	callbacks: Callbacks,
	dropbox_api_key: Ref<string>,
) {
	const addmenu = ref(); // ! Reference to the context menu
	const addMenu = computed(function () {
		const menu: AddMenuItem[] = [
			{
				label: "gallery.menus.upload_photo",
				icon: "pi pi-upload",
				callback: callbacks.toggleUpload,
			},
			{
				is_divider: true,
			},
			{
				label: "gallery.menus.import_link",
				icon: "pi pi-link",
				callback: callbacks.toggleImportFromLink,
			},
			{
				label: "gallery.menus.import_dropbox",
				icon: "pi pi-box",
				callback: callbacks.toggleImportFromDropbox,
				if: dropbox_api_key.value !== "disabled",
			},
			{
				is_divider: true,
				if: config.is_model_album,
			},
			{
				label: "gallery.menus.new_album",
				icon: "pi pi-folder",
				callback: callbacks.toggleCreateAlbum,
				if: config.is_model_album,
			},
		];

		const album: App.Http.Resources.Models.AlbumResource = abstractAlbum as App.Http.Resources.Models.AlbumResource;
		if (album.track_url !== null) {
			menu.push({
				label: "gallery.menus.delete_track",
				icon: "pi pi-compass",
				callback: callbacks.deleteTrack,
				if: config.is_model_album,
			});
		} else {
			menu.push({
				label: "gallery.menus.upload_track",
				icon: "pi pi-compass",
				callback: callbacks.toggleUploadTrack,
				if: config.is_model_album,
			});
		}

		return menu.filter((item) => item.if === undefined || item.if !== false);
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
