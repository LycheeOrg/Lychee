import { ref } from "vue";

type Callbacks = {
	toggleCreateAlbum: () => void;
	toggleUpload: () => void;
	toggleImportFromLink: () => void;
};

export function useContextMenuAlbumAdd(callbacks: Callbacks) {
	const addmenu = ref(); // ! Reference to the context menu
	const addMenu = ref([
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
			callback: () => callbacks.toggleImportFromLink,
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
			callback: () => callbacks.toggleCreateAlbum,
		},
		// { //! Upload tracks
		// 	label: "lychee.NEW_TAG_ALBUM",
		// 	icon: "pi pi-tags",
		// 	callback: () => {},
		// }
	]);

	function openAddMenu(event: Event) {
		addmenu.value.show(event);
	}

	return {
		addmenu,
		addMenu,
		openAddMenu,
	};
}
