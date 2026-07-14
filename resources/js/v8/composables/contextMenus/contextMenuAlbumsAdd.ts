import { computed, Ref, ref } from "vue";
import { AddMenuItem } from "./contextMenuAlbumAdd";

type Callbacks = {
	toggleUpload: () => void;
	toggleCameraCapture: () => void;
	toggleImportFromLink: () => void;
	toggleCreateAlbum: () => void;
	toggleCreateTagAlbum: () => void;
	toggleCreatePersonAlbum: () => void;
	toggleImportFromDropbox: () => void;
	toggleImportFromServer: () => void;
};

export function useContextMenuAlbumsAdd(
	callbacks: Callbacks,
	dropbox_api_key: Ref<string>,
	is_owner: Ref<boolean>,
	is_person_album_enabled: Ref<boolean>,
) {
	const addmenu = ref(); // ! Reference to the context menu
	const addMenu = computed(function () {
		const menu: AddMenuItem[] = [
			{
				label: "gallery.menus.upload_photo",
				icon: "lucide:upload",
				callback: callbacks.toggleUpload,
			},
			{
				label: "gallery.menus.take_photo",
				icon: "lucide:camera",
				callback: callbacks.toggleCameraCapture,
			},
			{
				is_divider: true,
			},
			{
				label: "gallery.menus.import_link",
				icon: "lucide:link",
				callback: callbacks.toggleImportFromLink,
			},
			{
				label: "gallery.menus.import_dropbox",
				icon: "lucide:box",
				callback: callbacks.toggleImportFromDropbox,
				if: dropbox_api_key.value !== "disabled",
			},
			{
				label: "gallery.menus.import_server",
				icon: "lucide:server",
				callback: callbacks.toggleImportFromServer,
				if: is_owner.value === true,
			},
			{
				is_divider: true,
			},
			{
				label: "gallery.menus.new_album",
				icon: "lucide:folder",
				callback: callbacks.toggleCreateAlbum,
			},
			{
				label: "gallery.menus.new_tag_album",
				icon: "lucide:tags",
				callback: callbacks.toggleCreateTagAlbum,
			},
			{
				label: "gallery.menus.new_person_album",
				icon: "lucide:users",
				callback: callbacks.toggleCreatePersonAlbum,
				if: is_person_album_enabled.value === true,
			},
		];

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
