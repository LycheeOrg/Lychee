import InitService from "@/services/init-service";
import { defineStore } from "pinia";
import { useTogglablesStateStore } from "./ModalsState";

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
		is_slideshow_enabled: true,

		// configs for nsfw
		are_nsfw_visible: false,
		is_nsfw_background_blurred: false,
		is_nsfw_banner_backdrop_blurred: false,
		nsfw_banner_override: "",

		// Image overlay settings
		image_overlay_type: "exif" as App.Enum.ImageOverlayType,
		can_rotate: false,
		can_autoplay: false,
		is_exif_disabled: false,
		is_favourite_enabled: false,
		photo_previous_next_size: "small" as App.Enum.SmallLargeType,
		is_details_links_enabled: false,
		is_desktop_dock_full_transparency_enabled: false,
		is_mobile_dock_full_transparency_enabled: false,
		is_photo_details_always_open: false,

		// keybinding help
		show_keybinding_help_popup: false,

		// album stuff
		default_album_protection: "private" as App.Enum.DefaultAlbumProtectionType,
		display_thumb_album_overlay: "always" as App.Enum.VisibilityType,
		display_thumb_photo_overlay: "always" as App.Enum.VisibilityType,
		album_subtitle_type: "OLDSTYLE" as App.Enum.ThumbAlbumSubtitleType,
		album_decoration: "LAYERS" as App.Enum.AlbumDecorationType,
		album_decoration_orientation: "ROW" as App.Enum.AlbumDecorationOrientation,
		album_view_mode: "grid" as App.Enum.AlbumLayoutType,
		number_albums_per_row_mobile: 3 as 1 | 2 | 3,
		photo_thumb_info: "title" as App.Enum.PhotoThumbInfoType,
		is_photo_thumb_tags_enabled: false,

		// Download settings
		is_raw_download_enabled: false,
		is_thumb_download_enabled: false,
		is_thum2x_download_enabled: false,
		is_small_download_enabled: false,
		is_small2x_download_enabled: false,
		is_medium_download_enabled: false,
		is_medium2x_download_enabled: false,

		// menu stuff
		clockwork_url: "" as null | string,

		// Timeline settings
		is_timeline_left_border_visible: true,

		// Site title & Dropbox API key
		title: "gallery.title",
		dropbox_api_key: "disabled",
		default_homepage: "gallery",
		is_timeline_page_enabled: false,

		// Login options
		is_basic_auth_enabled: true,
		is_webauthn_enabled: true,

		// Lychee Supporter Edition
		is_se_enabled: false,
		is_pro_enabled: false,
		is_se_preview_enabled: false,
		is_se_info_hidden: false,
		is_se_expired: false,
		is_live_metrics_enabled: false,

		// Settings toggles
		is_old_style: false,
		is_expert_mode: false,
		are_all_settings_enabled: false,

		// Registration settings
		is_registration_enabled: false,

		// Gesture settings
		is_scroll_to_navigate_photos_enabled: true,
		is_swipe_vertically_to_go_back_enabled: true,

		// Rating settings
		is_rating_show_avg_in_details_enabled: false,
		is_rating_show_avg_in_photo_view_enabled: false,
		rating_photo_view_mode: "never" as App.Enum.VisibilityType,
		is_rating_show_avg_in_album_view_enabled: false,
		rating_album_view_mode: "never" as App.Enum.VisibilityType,

		// Pagination settings
		photos_pagination_mode: "infinite_scroll" as App.Enum.PaginationMode,
		albums_pagination_mode: "infinite_scroll" as App.Enum.PaginationMode,
		photos_per_page: 100,
		albums_per_page: 30,
		photos_infinite_scroll_threshold: 10,
		albums_infinite_scroll_threshold: 10,
	}),
	actions: {
		async load(): Promise<void> {
			// Check if already initialized
			if (this.is_init) {
				return Promise.resolve();
			}

			// semaphore to avoid multiple calls
			if (this.is_loading) {
				while (this.is_loading) {
					await new Promise((resolve) => setTimeout(resolve, 100));
				}

				return Promise.resolve();
			}

			this.is_loading = true;

			return InitService.fetchInitData()
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
					this.is_exif_disabled = data.is_exif_disabled;
					this.is_favourite_enabled = data.is_favourite_enabled;

					this.display_thumb_album_overlay = data.display_thumb_album_overlay;
					this.display_thumb_photo_overlay = data.display_thumb_photo_overlay;
					this.album_subtitle_type = data.album_subtitle_type;
					this.album_decoration = data.album_decoration;
					this.album_decoration_orientation = data.album_decoration_orientation;

					this.clockwork_url = data.clockwork_url;

					this.slideshow_timeout = data.slideshow_timeout;
					this.is_slideshow_enabled = data.is_slideshow_enabled;

					this.is_timeline_left_border_visible = data.is_timeline_left_border_visible;

					this.title = data.title;
					this.dropbox_api_key = data.dropbox_api_key;

					this.is_basic_auth_enabled = data.is_basic_auth_enabled;
					this.is_webauthn_enabled = data.is_webauthn_enabled;

					this.is_se_enabled = data.is_se_enabled;
					this.is_pro_enabled = data.is_pro_enabled;
					this.is_se_preview_enabled = data.is_se_preview_enabled;
					this.is_se_info_hidden = data.is_se_info_hidden;
					this.is_se_expired = data.is_se_expired;
					this.is_live_metrics_enabled = data.is_live_metrics_enabled;
					this.number_albums_per_row_mobile = data.number_albums_per_row_mobile;
					this.photo_thumb_info = data.photo_thumb_info;
					this.is_photo_thumb_tags_enabled = data.is_photo_thumb_tags_enabled;
					this.album_view_mode = data.album_layout;

					this.is_raw_download_enabled = data.is_raw_download_enabled;
					this.is_thumb_download_enabled = data.is_thumb_download_enabled;
					this.is_thum2x_download_enabled = data.is_thum2x_download_enabled;
					this.is_small_download_enabled = data.is_small_download_enabled;
					this.is_small2x_download_enabled = data.is_small2x_download_enabled;
					this.is_medium_download_enabled = data.is_medium_download_enabled;
					this.is_medium2x_download_enabled = data.is_medium2x_download_enabled;
					this.photo_previous_next_size = data.photo_previous_next_size;
					this.is_details_links_enabled = data.is_details_links_enabled;
					this.is_desktop_dock_full_transparency_enabled = data.is_desktop_dock_full_transparency_enabled;
					this.is_mobile_dock_full_transparency_enabled = data.is_mobile_dock_full_transparency_enabled;
					this.is_photo_details_always_open = data.is_photo_details_always_open;
					const togglableStore = useTogglablesStateStore();
					// Initialize the details togglable according to the always open config
					togglableStore.are_details_open = data.is_photo_details_always_open;

					this.is_registration_enabled = data.is_registration_enabled;

					this.is_scroll_to_navigate_photos_enabled = data.is_scroll_to_navigate_photos_enabled;
					this.is_swipe_vertically_to_go_back_enabled = data.is_swipe_vertically_to_go_back_enabled;

					this.is_rating_show_avg_in_details_enabled = data.is_rating_show_avg_in_details_enabled;
					this.is_rating_show_avg_in_photo_view_enabled = data.is_rating_show_avg_in_photo_view_enabled;
					this.rating_photo_view_mode = data.rating_photo_view_mode;
					this.is_rating_show_avg_in_album_view_enabled = data.is_rating_show_avg_in_album_view_enabled;
					this.rating_album_view_mode = data.rating_album_view_mode;

					this.default_homepage = data.default_homepage;
					this.is_timeline_page_enabled = data.is_timeline_page_enabled;

					this.photos_pagination_mode = data.photos_pagination_mode;
					this.albums_pagination_mode = data.albums_pagination_mode;
					this.photos_per_page = data.photos_per_page;
					this.albums_per_page = data.albums_per_page;
					this.photos_infinite_scroll_threshold = data.photos_infinite_scroll_threshold;
					this.albums_infinite_scroll_threshold = data.albums_infinite_scroll_threshold;

					this.default_album_protection = data.default_album_protection;
				})
				.catch((error) => {
					// In this specific case, even though it has been possibly disabled, we really need to see the error.
					this.is_debug_enabled = true;
					this.is_loading = false;

					const event = new CustomEvent("error", { detail: error.response.data });
					window.dispatchEvent(event);
				});
		},
	},
});
