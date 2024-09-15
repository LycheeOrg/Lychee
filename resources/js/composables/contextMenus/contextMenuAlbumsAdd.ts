import { ref } from "vue";

type Callbacks = {
	toggleUpload: () => void;
	toggleImportFromLink: () => void;
	toggleCreateAlbum: () => void;
};

export function useContextMenuAlbumsAdd(callbacks: Callbacks) {
	const isCreateTagAlbumOpen = ref(false);
	const isImportFromServerOpen = ref(false);

	const addmenu = ref(); // ! Reference to the context menu
	const addMenu = ref([
		{
			label: "lychee.UPLOAD_PHOTO",
			icon: "pi pi-upload",
			callback: callbacks.toggleUpload,
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
			label: "lychee.IMPORT_SERVER",
			icon: "pi pi-server",
			callback: () => (isImportFromServerOpen.value = true),
		},
		{
			label: "lychee.NEW_ALBUM",
			icon: "pi pi-folder",
			callback: callbacks.toggleCreateAlbum,
		},
		{
			label: "lychee.NEW_TAG_ALBUM",
			icon: "pi pi-tags",
			callback: () => (isCreateTagAlbumOpen.value = true),
		},
	]);

	function openAddMenu(event: Event) {
		addmenu.value.show(event);
	}

	return {
		addmenu,
		addMenu,
		openAddMenu,
		isCreateTagAlbumOpen,
		isImportFromServerOpen,
	};
}
