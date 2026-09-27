import { defineStore } from "pinia";

export type LeftMenuStateStore = ReturnType<typeof useLeftMenuStateStore>;

export const useLeftMenuStateStore = defineStore("leftmenu-store", {
	state: () => ({
		// Togglable
		left_menu_open: false,
	}),
	actions: {
		toggleLeftMenu() {
			this.left_menu_open = !this.left_menu_open;
		},
	},
});
