import InitService from "@/services/init-service";
import { defineStore } from "pinia";

export type LycheeStateStore = ReturnType<typeof useLycheeStateStore>;

export const useLycheeStateStore = defineStore("lychee-store", {
	state: () => ({
		is_debug_enabled: true,

		// togglables
		left_menu_open: false,
		is_full_screen: false,
		is_login_open: false,

		// Photo toggleables
		is_edit_open: false,
		are_details_open: false,

		// Search stuff
		search_term: "",
		search_album_id: undefined as string | undefined,
		search_page: 1,

		// configs for nsfw
		is_nsfw_background_blurred: false,
		is_nsfw_banner_backdrop_blurred: false,
		nsfw_banner_override: "",

		// keybinding help
		show_keybinding_help_popup: false,

		// Lychee Supporter Edition
		is_se_enabled: false,
		is_se_preview_enabled: false,
		is_se_info_hidden: false,

		// album stuff
		album_decoration: "LAYERS" as App.Enum.AlbumDecorationType,
		album_decoration_orientation: "ROW" as App.Enum.AlbumDecorationOrientation,
		album_subtitle_type: "OLDSTYLE" as App.Enum.ThumbAlbumSubtitleType,
		display_thumb_album_overlay: "always" as App.Enum.ThumbOverlayVisibilityType,
		display_thumb_photo_overlay: "always" as App.Enum.ThumbOverlayVisibilityType,

		can_rotate: false,
		can_autoplay: false,

		// menu stuff
		clockwork_url: "" as null | string,

		// togglable with defaults
		are_nsfw_visible: false,
		image_overlay_type: "exif" as App.Enum.ImageOverlayType,

		// Site title
		title: "lychee.GALLERY",

		// flag to fetch data
		is_init: false,
		is_loading: false,

		nsfw_consented: [] as string[],

		// Dropbox API key
		dropbox_api_key: "",
	}),
	getters: {
		isSearchActive(): boolean {
			return this.search_term !== "";
		},
	},
	actions: {
		init() {
			// Check if already initialized
			if (this.is_init) {
				return;
			}
			this.load();
		},

		load() {
			// semaphore to avoid multiple calls
			if (this.is_loading) {
				return;
			}
			this.is_loading = true;

			InitService.fetchInitData()
				.then((response) => {
					const data = response.data;
					this.are_nsfw_visible = data.are_nsfw_visible;
					this.is_nsfw_background_blurred = data.is_nsfw_background_blurred;
					this.nsfw_banner_override = data.nsfw_banner_override;
					this.is_nsfw_banner_backdrop_blurred = data.is_nsfw_banner_backdrop_blurred;
					this.image_overlay_type = data.image_overlay_type;
					this.display_thumb_album_overlay = data.display_thumb_album_overlay;
					this.display_thumb_photo_overlay = data.display_thumb_photo_overlay;
					this.is_init = true;
					this.is_loading = false;
					this.show_keybinding_help_popup = data.show_keybinding_help_popup;
					this.clockwork_url = data.clockwork_url;
					this.can_rotate = data.can_rotate;
					this.can_autoplay = data.can_autoplay;
					this.album_decoration = data.album_decoration;
					this.album_decoration_orientation = data.album_decoration_orientation;
					this.album_subtitle_type = data.album_subtitle_type;
					this.title = data.title;
					this.is_debug_enabled = data.is_debug_enabled;
					this.is_se_enabled = data.is_se_enabled;
					this.is_se_preview_enabled = data.is_se_preview_enabled;
					this.is_se_info_hidden = data.is_se_info_hidden;
					this.dropbox_api_key = data.dropbox_api_key;
				})
				.catch((error) => {
					// In this specific case, even though it has been possibly disabled, we really need to see the error.
					this.is_debug_enabled = true;

					const event = new CustomEvent("error", { detail: error.response.data });
					window.dispatchEvent(event);
				});
		},

		toggleFullScreen() {
			this.is_full_screen = !this.is_full_screen;
		},

		toggleLogin() {
			this.is_login_open = !this.is_login_open;
		},

		resetSearch() {
			this.search_term = "";
			this.search_album_id = undefined;
			this.search_page = 1;
		},
	},
});
