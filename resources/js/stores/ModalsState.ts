import { Uploadable } from "@/composables/album/uploadEvents";
import { defineStore } from "pinia";

export type TogglablesStateStore = ReturnType<typeof useTogglablesStateStore>;

export const useTogglablesStateStore = defineStore("togglables-store", {
	state: () => ({
		// togglables
		is_full_screen: false,
		is_login_open: false,
		is_webauthn_open: false,
		is_metrics_open: false,

		// upload
		is_upload_visible: false,
		list_upload_files: [] as Uploadable[],

		// create albums
		is_create_album_visible: false,
		is_create_tag_album_visible: false,

		// Album toggleables
		is_album_edit_open: false,

		// Photo toggleables
		is_photo_edit_open: false,
		are_details_open: false,
		is_slideshow_active: false,

		// Scroll memory
		scroll_memory: {} as Record<string, number>,
		scroll_photo_id: undefined as string | undefined,

		// Modals
		is_rename_visible: false,
		is_move_visible: false,
		is_delete_visible: false,
		is_merge_album_visible: false,
		is_share_album_visible: false,
		is_import_from_link_open: false,
		is_tag_visible: false,
		is_copy_visible: false,

		// Selections Ids
		selectedPhotosIdx: [] as number[],
		selectedAlbumsIdx: [] as number[],
	}),
	actions: {
		toggleFullScreen() {
			this.is_full_screen = !this.is_full_screen;
		},

		toggleLogin() {
			this.is_login_open = !this.is_login_open;
		},

		rememberScrollPage(elem: HTMLElement, path: string) {
			this.scroll_memory[path] = elem.scrollTop;
		},

		recoverScrollPage(elem: HTMLElement, path: string) {
			if (!(path in this.scroll_memory)) {
				return;
			}
			const scroll = this.scroll_memory[path];
			if (scroll) {
				elem.scrollTop = scroll;
				// Smooth scrolling
				// elem.scrollTo({
				// 	top: scroll,
				// 	behavior: "smooth",
				// });
			}
		},

		rememberScrollThumb(photo_id: string | undefined) {
			this.scroll_photo_id = photo_id;
		},

		recoverAndResetScrollThumb(thumbElem: HTMLElement) {
			if (thumbElem) {
				// check if thumbElem is actually out of view:
				const rect = thumbElem.getBoundingClientRect();
				const isVisible = (
					rect.top >= 40 &&
					rect.left >= 0 &&
					rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
					rect.right <= (window.innerWidth || document.documentElement.clientWidth)
				);

				// only scroll it into view if it's currently invisible:
				if (!isVisible) {
					thumbElem.scrollIntoView({ behavior: "smooth", block: "nearest" });
				}
			}

			this.scroll_photo_id = undefined;
		},
	},
});