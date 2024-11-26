import { Ref, ref } from "vue";

type Callbacks = {
	toggleUpload: () => void;
	toggleImportFromLink: () => void;
	toggleCreateAlbum: () => void;
	toggleCreateTagAlbum: () => void;
	toggleImportFromDropbox: () => void;
};

export function useContextMenuAlbumsAdd(callbacks: Callbacks, dropbox_api_key: Ref<string>) {
	const isImportFromServerOpen = ref(false);

	const addmenu = ref(); // ! Reference to the context menu
	const addMenu = ref(
		[
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
			{
				label: "lychee.IMPORT_DROPBOX",
				icon: "pi pi-box",
				callback: callbacks.toggleImportFromDropbox,
				if: dropbox_api_key.value !== "disabled",
			},
			// {
			// 	label: "lychee.IMPORT_SERVER",
			// 	icon: "pi pi-server",
			// 	callback: () => (isImportFromServerOpen.value = true),
			// },
			{
				is_divider: true,
			},
			{
				label: "lychee.NEW_ALBUM",
				icon: "pi pi-folder",
				callback: callbacks.toggleCreateAlbum,
			},
			{
				label: "lychee.NEW_TAG_ALBUM",
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
		isImportFromServerOpen,
	};
}
