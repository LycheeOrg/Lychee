import { AlbumStore } from "@/stores/AlbumState";
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
	toggleImportFromServer: () => void;
};

export function useContextMenuAlbumAdd(albumStore: AlbumStore, callbacks: Callbacks, dropbox_api_key: Ref<string>) {
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
				label: "gallery.menus.import_server",
				icon: "pi pi-server",
				callback: callbacks.toggleImportFromServer,
				if: albumStore.rights?.can_import_from_server && albumStore.config?.is_model_album,
			},
			{
				is_divider: true,
				if: albumStore.config?.is_model_album,
			},
			{
				label: "gallery.menus.new_album",
				icon: "pi pi-folder",
				callback: callbacks.toggleCreateAlbum,
				if: albumStore.config?.is_model_album,
			},
		];

		if (albumStore.modelAlbum?.track_url !== null && albumStore.modelAlbum?.track_url !== undefined) {
			menu.push({
				label: "gallery.menus.delete_track",
				icon: "pi pi-compass",
				callback: callbacks.deleteTrack,
				if: albumStore.config?.is_model_album,
			});
		} else {
			menu.push({
				label: "gallery.menus.upload_track",
				icon: "pi pi-compass",
				callback: callbacks.toggleUploadTrack,
				if: albumStore.config?.is_model_album,
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
