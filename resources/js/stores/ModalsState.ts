import { Uploadable } from "@/composables/album/uploadEvents";
import UploadService from "@/services/upload-service";
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
		upload_config: undefined as App.Http.Resources.GalleryConfigs.UploadConfig | undefined,

		// create albums
		is_create_album_visible: false,
		is_create_tag_album_visible: false,
		is_create_person_album_visible: false,

		// Album toggleables
		is_album_edit_open: false,

		// Photo toggleables
		is_photo_edit_open: false,
		are_details_open: false,
		is_slideshow_active: false,
		is_filters_visible: false,

		// Scroll memory
		scroll_memory: {} as Record<string, number>,
		scroll_photo_id: undefined as string | undefined,

		// Modals
		is_rename_visible: false,
		is_move_visible: false,
		is_delete_visible: false,
		is_merge_album_visible: false,
		is_share_album_visible: false,
		is_embed_code_visible: false,
		embed_code_mode: "album" as "album" | "stream", // Mode for embed code dialog
		is_import_from_link_open: false,
		is_tag_visible: false,
		is_license_visible: false,
		is_copy_visible: false,
		is_import_from_dropbox_open: false,
		is_import_from_server_open: false,
		is_apply_renamer_visible: false,
		is_watermark_confirm_visible: false,
		is_camera_capture_visible: false,
		is_download_album_visible: false,
		is_download_photo_visible: false,
		is_face_assignment_visible: false,

		// Set by the Spotlight "Move current album" action so MoveDialog targets the
		// currently-open album instead of whatever child album/photo is checkbox-selected.
		move_album_override: null as App.Http.Resources.Models.ThumbAlbumResource | null,

		// Set by the Spotlight "Upload track" action so AlbumTracks opens its file picker
		// as soon as the edit drawer's tracks section mounts.
		is_track_upload_pending: false,

		// Set by the Spotlight "Share" action so AlbumEdit scrolls straight to its share
		// section as soon as the edit drawer opens.
		is_share_section_pending: false,

		// Help
		is_keybindings_help_open: false,

		// Selections Ids
		selectedPhotosIds: [] as string[],
		selectedAlbumsIds: [] as string[],

		// Touch multi-select mode (mobile)
		is_touch_select_mode: false,

		// Selections via Click and Drag
		isDragging: false,
		nonHoverSelectablePhotosIdx: [] as string[], // contains photos ids that are currently hoved but not selected
		nonHoverSelectableAlbumsIdx: [] as string[], // contains albums ids that are currently hoved but not selected

		// NavMenu
		isNavOpen: false,
	}),
	getters: {
		is_modal_open(state): boolean {
			return (
				state.is_login_open ||
				state.is_webauthn_open ||
				state.is_metrics_open ||
				state.is_upload_visible ||
				state.is_camera_capture_visible ||
				state.is_create_album_visible ||
				state.is_create_tag_album_visible ||
				state.is_create_person_album_visible ||
				state.is_album_edit_open ||
				state.is_photo_edit_open ||
				state.is_rename_visible ||
				state.is_move_visible ||
				state.is_delete_visible ||
				state.is_merge_album_visible ||
				state.is_share_album_visible ||
				state.is_embed_code_visible ||
				state.is_import_from_link_open ||
				state.is_import_from_dropbox_open ||
				state.is_import_from_server_open ||
				state.is_tag_visible ||
				state.is_license_visible ||
				state.is_copy_visible ||
				state.is_apply_renamer_visible ||
				state.is_watermark_confirm_visible ||
				state.is_keybindings_help_open ||
				state.is_download_album_visible ||
				state.is_download_photo_visible ||
				state.is_face_assignment_visible
			);
		},
	},
	actions: {
		loadUploadConfig() {
			if (this.upload_config !== undefined) {
				return;
			}
			UploadService.getSetUp().then((response) => {
				this.upload_config = response.data;
			});
		},

		toggleFullScreen() {
			this.is_full_screen = !this.is_full_screen;
		},

		toggleLogin() {
			this.is_login_open = !this.is_login_open;
		},

		rememberScrollPage(elem: HTMLElement, path: string) {
			this.scroll_memory[path] = elem.scrollTop;
		},

		toggleFilters() {
			this.is_filters_visible = !this.is_filters_visible;
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

		toggleTouchSelectMode() {
			this.is_touch_select_mode = !this.is_touch_select_mode;
			if (!this.is_touch_select_mode) {
				this.selectedPhotosIds = [];
				this.selectedAlbumsIds = [];
			}
		},

		recoverAndResetScrollThumb(thumbElem: HTMLElement) {
			if (thumbElem) {
				// check if thumbElem is actually out of view:
				const rect = thumbElem.getBoundingClientRect();
				const isVisible =
					rect.top >= 40 &&
					rect.left >= 0 &&
					rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
					rect.right <= (window.innerWidth || document.documentElement.clientWidth);

				// only scroll it into view if it's currently invisible:
				if (!isVisible) {
					thumbElem.scrollIntoView({ behavior: "smooth", block: "center" });
				}
			}

			this.scroll_photo_id = undefined;
		},
	},
});
