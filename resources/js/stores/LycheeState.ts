import InitService from "@/services/init-service";
import { defineStore } from "pinia";

export type LycheeStateStore = ReturnType<typeof useLycheeStateStore>;

export const useLycheeStateStore = defineStore("lychee-store", {
	state: () => ({
		// togglables
		left_menu_open: false,
		is_full_screen: false,

		// configs
		are_nsfw_blurred: false,
		is_nsfw_warning_visible: false,
		is_nsfw_warning_visible_for_admin: false,
		is_nsfw_background_blurred: false,
		nsfw_banner_override: "",
		is_nsfw_banner_backdrop_blurred: false,
		show_keybinding_help_popup: false,

		// menu stuff
		clockwork_url: "" as null | string,

		// togglable with defaults
		are_nsfw_visible: false,
		image_overlay_type: "exif" as App.Enum.ImageOverlayType,
		display_thumb_album_overlay: "always" as App.Enum.ThumbOverlayVisibilityType,
		display_thumb_photo_overlay: "always" as App.Enum.ThumbOverlayVisibilityType,

		// flag to fetch data
		is_init: false,
		is_loading: false,
	}),
	actions: {
		init() {
			// Check if already initialized
			if (this.is_init) {
				return;
			}

			// semaphore to avoid multiple calls
			if (this.is_loading) {
				return;
			}
			this.is_loading = true;

			InitService.fetchInitData().then((response) => {
				const data = response.data;
				this.are_nsfw_visible = data.are_nsfw_visible;
				this.are_nsfw_blurred = data.are_nsfw_blurred;
				this.is_nsfw_warning_visible = data.is_nsfw_warning_visible;
				this.is_nsfw_warning_visible_for_admin = data.is_nsfw_warning_visible_for_admin;
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
			});
		},
	},
});
