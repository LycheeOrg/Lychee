import { defineStore } from "pinia";

export type LeftMenuStateStore = ReturnType<typeof useLeftMenuStateStore>;

export const useLeftMenuStateStore = defineStore("leftmenu-store", {
	state: () => ({
		// Togglable
		left_menu_open: false,

		// Info needed for the menu to be displayed
		initData: undefined as App.Http.Resources.Rights.GlobalRightsResource | undefined,
	}),
	actions: {
		toggleLeftMenu() {
			this.left_menu_open = !this.left_menu_open;
		},
	},
});
