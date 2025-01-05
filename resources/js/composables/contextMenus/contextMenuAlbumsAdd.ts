import { Ref, ref } from "vue";

type Callbacks = {
	toggleUpload: () => void;
	toggleImportFromLink: () => void;
	toggleCreateAlbum: () => void;
	toggleCreateTagAlbum: () => void;
	toggleImportFromDropbox: () => void;
};

export function useContextMenuAlbumsAdd(callbacks: Callbacks, dropbox_api_key: Ref<string>) {
	const addmenu = ref(); // ! Reference to the context menu
	const addMenu = ref(
		[
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
			},
			{
				label: "gallery.menus.new_album",
				icon: "pi pi-folder",
				callback: callbacks.toggleCreateAlbum,
			},
			{
				label: "gallery.menus.new_tag_album",
				icon: "pi pi-tags",
				callback: callbacks.toggleCreateTagAlbum,
			},
		].filter((item) => item.if === undefined || item.if !== false),
	);

	function openAddMenu(event: Event) {
		addmenu.value.show(event);
	}

	return {
		addmenu,
		addMenu,
		openAddMenu,
	};
}
