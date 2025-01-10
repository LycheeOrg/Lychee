import InitService from "@/services/init-service";
import { defineStore } from "pinia";

export type LycheeStateStore = ReturnType<typeof useLycheeStateStore>;

export const useLycheeStateStore = defineStore("lychee-store", {
	state: () => ({
		// flag to fetch data
		is_init: false,
		is_loading: false,

		// Debug mode (is default to true to see the first crash)
		is_debug_enabled: true,

		// Photo config
		slideshow_timeout: 5,

		// configs for nsfw
		are_nsfw_visible: false,
		is_nsfw_background_blurred: false,
		is_nsfw_banner_backdrop_blurred: false,
		nsfw_banner_override: "",

		nsfw_consented: [] as string[],

		// Image overlay settings
		image_overlay_type: "exif" as App.Enum.ImageOverlayType,
		can_rotate: false,
		can_autoplay: false,

		// keybinding help
		show_keybinding_help_popup: false,

		// album stuff
		display_thumb_album_overlay: "always" as App.Enum.ThumbOverlayVisibilityType,
		display_thumb_photo_overlay: "always" as App.Enum.ThumbOverlayVisibilityType,
		album_subtitle_type: "OLDSTYLE" as App.Enum.ThumbAlbumSubtitleType,
		album_decoration: "LAYERS" as App.Enum.AlbumDecorationType,
		album_decoration_orientation: "ROW" as App.Enum.AlbumDecorationOrientation,
		number_albums_per_row_mobile: 3 as 1 | 2 | 3,

		// menu stuff
		clockwork_url: "" as null | string,

		// Timeline settings
		is_timeline_left_border_visible: true,

		// Site title & Dropbox API key
		title: "gallery.title",
		dropbox_api_key: "disabled",

		// Lychee Supporter Edition
		is_se_enabled: false,
		is_se_preview_enabled: false,
		is_se_info_hidden: false,
	}),
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
					this.is_init = true;
					this.is_loading = false;

					const data = response.data;

					this.is_debug_enabled = data.is_debug_enabled;

					this.are_nsfw_visible = data.are_nsfw_visible;
					this.is_nsfw_background_blurred = data.is_nsfw_background_blurred;
					this.nsfw_banner_override = data.nsfw_banner_override;
					this.is_nsfw_banner_backdrop_blurred = data.is_nsfw_banner_backdrop_blurred;

					this.show_keybinding_help_popup = data.show_keybinding_help_popup;

					this.image_overlay_type = data.image_overlay_type;
					this.can_rotate = data.can_rotate;
					this.can_autoplay = data.can_autoplay;

					this.display_thumb_album_overlay = data.display_thumb_album_overlay;
					this.display_thumb_photo_overlay = data.display_thumb_photo_overlay;
					this.album_subtitle_type = data.album_subtitle_type;
					this.album_decoration = data.album_decoration;
					this.album_decoration_orientation = data.album_decoration_orientation;

					this.clockwork_url = data.clockwork_url;

					this.slideshow_timeout = data.slideshow_timeout;

					this.is_timeline_left_border_visible = data.is_timeline_left_border_visible;

					this.title = data.title;
					this.dropbox_api_key = data.dropbox_api_key;

					this.is_se_enabled = data.is_se_enabled;
					this.is_se_preview_enabled = data.is_se_preview_enabled;
					this.is_se_info_hidden = data.is_se_info_hidden;
					this.number_albums_per_row_mobile = data.number_albums_per_row_mobile;
				})
				.catch((error) => {
					// In this specific case, even though it has been possibly disabled, we really need to see the error.
					this.is_debug_enabled = true;

					const event = new CustomEvent("error", { detail: error.response.data });
					window.dispatchEvent(event);
				});
		},
	},
});
