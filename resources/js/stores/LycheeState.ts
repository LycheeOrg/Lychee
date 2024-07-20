import { defineStore } from "pinia";

export type LycheeStateStore = ReturnType<typeof useLycheeStateStore>;

export const useLycheeStateStore = defineStore("lychee-store", {
	state: () => ({
		is_full_screen: false,
		are_nsfw_visible: false,
		left_menu_open: false,
	}),
	actions: {
		toggleFullScreen() {
			this.is_full_screen = !this.is_full_screen;
		},
		toggleNsfwVisibility() {
			this.are_nsfw_visible = !this.are_nsfw_visible;
		},
		toggleLeftMenu() {
			this.left_menu_open = !this.left_menu_open;
		},
	},
});
