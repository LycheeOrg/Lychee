import { Uploadable } from "@/components/modals/UploadPanel.vue";
import InitService from "@/services/init-service";
import { defineStore } from "pinia";

export type TogglablesStateStore = ReturnType<typeof useTogglablesStateStore>;

export const useTogglablesStateStore = defineStore("togglables-store", {
	state: () => ({
		// togglables
		left_menu_open: false,
		is_full_screen: false,
		is_login_open: false,

		// upload
		is_upload_visible: false,
		list_upload_files: [] as Uploadable[],

		// Photo toggleables
		is_edit_open: false,
		are_details_open: false,
		is_slideshow_active: false,

		// Search stuff
		search_term: "",
		search_album_id: undefined as string | undefined,
		search_page: 1,
	}),
	getters: {
		isSearchActive(): boolean {
			return this.search_term !== "";
		},
	},
	actions: {
		toggleFullScreen() {
			this.is_full_screen = !this.is_full_screen;
		},

		toggleLogin() {
			this.is_login_open = !this.is_login_open;
		},

		toggleLeftMenu() {
			this.left_menu_open = !this.left_menu_open;
		},

		resetSearch() {
			this.search_term = "";
			this.search_album_id = undefined;
			this.search_page = 1;
		},
	},
});
