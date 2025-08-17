declare namespace App.DTO {
	export type AlbumSortingCriterion = {
		column: App.Enum.ColumnSortingType;
		order: App.Enum.OrderSortingType;
	};
	export type PhotoSortingCriterion = {
		column: App.Enum.ColumnSortingType;
		order: App.Enum.OrderSortingType;
	};
	export type SortingCriterion = {
		column: App.Enum.ColumnSortingType;
		order: App.Enum.OrderSortingType;
	};
}
declare namespace App.Enum {
	export type AlbumDecorationOrientation = "row" | "row-reverse" | "column" | "column-reverse";
	export type AlbumDecorationType = "none" | "layers" | "album" | "photo" | "all";
	export type AspectRatioCSSType = "aspect-5x4" | "aspect-4x5" | "aspect-3x2" | "aspect-square" | "aspect-2x3" | "aspect-video";
	export type AspectRatioType = "5/4" | "3/2" | "1/1" | "2/3" | "4/5" | "16/9";
	export type CacheTag = "gallery" | "auth" | "user" | "settings" | "statistics" | "users";
	export type ColumnSortingAlbumType = "owner_id" | "created_at" | "title" | "description" | "min_taken_at" | "max_taken_at";
	export type ColumnSortingPhotoType = "owner_id" | "created_at" | "title" | "description" | "taken_at" | "is_starred" | "type";
	export type ColumnSortingType =
		| "owner_id"
		| "created_at"
		| "title"
		| "description"
		| "min_taken_at"
		| "max_taken_at"
		| "taken_at"
		| "is_starred"
		| "type";
	export type ConfigType = "int" | "positive" | "string" | "string_required" | "0|1" | "0|1|2" | "" | "admin_user" | "license" | "map_provider";
	export type CountType = "taken_at" | "created_at";
	export type CoverFitType = "cover" | "fit";
	export type DateOrderingType = "older_younger" | "younger_older";
	export type DbDriverType = "mysql" | "pgsql" | "sqlite";
	export type DefaultAlbumProtectionType = 1 | 2 | 3;
	export type DownloadVariantType = "LIVEPHOTOVIDEO" | "ORIGINAL" | "MEDIUM2X" | "MEDIUM" | "SMALL2X" | "SMALL" | "THUMB2X" | "THUMB";
	export type FileStatus = "uploading" | "processing" | "ready" | "skipped" | "done" | "error";
	export type FlowStrategy = "auto" | "opt-in";
	export type ImageOverlayType = "none" | "desc" | "date" | "exif";
	export type JobStatus = 0 | 1 | 2 | 3;
	export type LicenseType =
		| "none"
		| "reserved"
		| "CC0"
		| "CC-BY-1.0"
		| "CC-BY-2.0"
		| "CC-BY-2.5"
		| "CC-BY-3.0"
		| "CC-BY-4.0"
		| "CC-BY-ND-1.0"
		| "CC-BY-ND-2.0"
		| "CC-BY-ND-2.5"
		| "CC-BY-ND-3.0"
		| "CC-BY-ND-4.0"
		| "CC-BY-SA-1.0"
		| "CC-BY-SA-2.0"
		| "CC-BY-SA-2.5"
		| "CC-BY-SA-3.0"
		| "CC-BY-SA-4.0"
		| "CC-BY-NC-1.0"
		| "CC-BY-NC-2.0"
		| "CC-BY-NC-2.5"
		| "CC-BY-NC-3.0"
		| "CC-BY-NC-4.0"
		| "CC-BY-NC-ND-1.0"
		| "CC-BY-NC-ND-2.0"
		| "CC-BY-NC-ND-2.5"
		| "CC-BY-NC-ND-3.0"
		| "CC-BY-NC-ND-4.0"
		| "CC-BY-NC-SA-1.0"
		| "CC-BY-NC-SA-2.0"
		| "CC-BY-NC-SA-2.5"
		| "CC-BY-NC-SA-3.0"
		| "CC-BY-NC-SA-4.0";
	export type LiveMetricsAccess = "logged-in users" | "admin";
	export type MapProviders = "Wikimedia" | "OpenStreetMap.org" | "OpenStreetMap.de" | "OpenStreetMap.fr" | "RRZE";
	export type MessageType = "info" | "warning" | "error";
	export type MetricsAccess = "public" | "logged-in users" | "owner" | "admin";
	export type MetricsAction = "visit" | "favourite" | "download" | "shared";
	export type OauthProvidersType =
		| "amazon"
		| "apple"
		| "authelia"
		| "authentik"
		| "facebook"
		| "github"
		| "google"
		| "mastodon"
		| "microsoft"
		| "nextcloud"
		| "keycloak";
	export type OrderSortingType = "ASC" | "DESC";
	export type PhotoLayoutType = "square" | "justified" | "masonry" | "grid";
	export type PhotoThumbInfoType = "title" | "description";
	export type SeverityType = "emergency" | "alert" | "critical" | "error" | "warning" | "notice" | "info" | "debug";
	export type SizeVariantType = 0 | 1 | 2 | 3 | 4 | 5 | 6 | 7;
	export type SmallLargeType = "small" | "large";
	export type SmartAlbumType = "unsorted" | "starred" | "recent" | "on_this_day";
	export type StorageDiskType = "images" | "s3";
	export type ThumbAlbumSubtitleType = "description" | "takedate" | "creation" | "oldstyle";
	export type ThumbOverlayVisibilityType = "never" | "always" | "hover";
	export type TimelineAlbumGranularity = "default" | "disabled" | "year" | "month" | "day";
	export type TimelinePhotoGranularity = "default" | "disabled" | "year" | "month" | "day" | "hour";
	export type UpdateStatus = 0 | 1 | 2 | 3;
	export type UserGroupRole = "member" | "admin";
	export type VersionChannelType = "release" | "git" | "tag";
}
declare namespace App.Http.Resources.Collections {
	export type PositionDataResource = {
		id: string | null;
		title: string | null;
		track_url: string | null;
		photos: App.Http.Resources.Models.PhotoResource[];
	};
	export type RootAlbumResource = {
		smart_albums: App.Http.Resources.Models.ThumbAlbumResource[];
		tag_albums: App.Http.Resources.Models.ThumbAlbumResource[];
		pinned_albums: App.Http.Resources.Models.ThumbAlbumResource[];
		albums: App.Http.Resources.Models.ThumbAlbumResource[];
		shared_albums: App.Http.Resources.Models.ThumbAlbumResource[];
		config: App.Http.Resources.GalleryConfigs.RootConfig;
		rights: App.Http.Resources.Rights.RootAlbumRightsResource;
	};
	export type UserGroupDataResource = {
		user_groups: App.Http.Resources.Models.UserGroupResource[];
		can_create_delete_user_groups: boolean;
	};
}
declare namespace App.Http.Resources.Diagnostics {
	export type AlbumTree = {
		id: string;
		title: string;
		parent_id: string | null;
		_lft: number;
		_rgt: number;
	};
	export type ChangeLogInfo = {
		version: string;
		date: string;
		changes: string;
	};
	export type CleaningState = {
		path: string;
		base: string;
		is_not_empty: boolean;
	};
	export type ErrorLine = {
		type: App.Enum.MessageType;
		message: string;
		from: string;
		details: string[];
	};
	export type Permissions = {
		left: string;
		right: string;
	};
	export type StatisticsCheckResource = {
		missing_photos: number;
		missing_albums: number;
	};
	export type TreeState = {
		oddness: number;
		duplicates: number;
		wrong_parent: number;
		missing_parent: number;
	};
	export type UpdateCheckInfo = {
		extra: string;
		can_update: boolean;
	};
	export type UpdateInfo = {
		info: string;
		extra: string;
		channel_name: App.Enum.VersionChannelType;
		is_docker: boolean;
	};
}
declare namespace App.Http.Resources.Editable {
	export type EditableBaseAlbumResource = {
		id: string;
		title: string;
		description: string | null;
		copyright: string | null;
		license: App.Enum.LicenseType | null;
		photo_sorting: App.DTO.PhotoSortingCriterion | null;
		album_sorting: App.DTO.AlbumSortingCriterion | null;
		aspect_ratio: App.Enum.AspectRatioType | null;
		photo_layout: App.Enum.PhotoLayoutType | null;
		header_id: string | null;
		cover_id: string | null;
		album_timeline: App.Enum.TimelineAlbumGranularity | null;
		photo_timeline: App.Enum.TimelinePhotoGranularity | null;
		tags: Array<string>;
		is_and: boolean;
		is_model_album: boolean;
		is_pinned: boolean;
	};
	export type EditableConfigResource = {
		key: string;
		value: string | null;
	};
	export type UploadMetaResource = {
		file_name: string;
		extension: string | null;
		uuid_name: string | null;
		stage: App.Enum.FileStatus;
		chunk_number: number;
		total_chunks: number;
	};
}
declare namespace App.Http.Resources.Flow {
	export type FlowItemResource = {
		id: string;
		title: string;
		description: string | null;
		min_max_text: string | null;
		published_created_at: string;
		diff_published_created_at: string;
		owner_name: string | null;
		is_nsfw: boolean;
		num_photos: number;
		num_children: number;
		cover: App.Http.Resources.Models.SizeVariantsResouce | null;
		photos: App.Http.Resources.Models.PhotoResource[];
		statistics: App.Http.Resources.Models.AlbumStatisticsResource | null;
	};
	export type FlowResource = {
		albums: App.Http.Resources.Flow.FlowItemResource[];
		current_page: number;
		from: number;
		last_page: number;
		per_page: number;
		to: number;
		total: number;
	};
	export type InitResource = {
		is_mod_flow_enabled: boolean;
		is_open_album_on_click: boolean;
		is_display_open_album_button: boolean;
		is_highlight_first_picture: boolean;
		is_image_header_enabled: boolean;
		image_header_cover: App.Enum.CoverFitType;
		image_header_height: number;
		is_carousel_enabled: boolean;
		carousel_height: number;
		is_blur_nsfw_enabled: boolean;
		is_compact_mode_enabled: boolean;
	};
}
declare namespace App.Http.Resources.Frame {
	export type FrameData = {
		timeout: number;
		src: string;
		srcset: string;
	};
}
declare namespace App.Http.Resources.GalleryConfigs {
	export type AlbumConfig = {
		is_base_album: boolean;
		is_model_album: boolean;
		is_accessible: boolean;
		is_password_protected: boolean;
		is_map_accessible: boolean;
		is_mod_frame_enabled: boolean;
		is_search_accessible: boolean;
		is_nsfw_warning_visible: boolean;
		album_thumb_css_aspect_ratio: App.Enum.AspectRatioCSSType;
		photo_layout: App.Enum.PhotoLayoutType;
		is_album_timeline_enabled: boolean;
		is_photo_timeline_enabled: boolean;
	};
	export type FooterConfig = {
		footer_additional_text: string;
		footer_show_copyright: boolean;
		footer_show_social_media: boolean;
		copyright: string;
		sm_facebook_url: string;
		sm_flickr_url: string;
		sm_instagram_url: string;
		sm_twitter_url: string;
		sm_youtube_url: string;
	};
	export type InitConfig = {
		is_debug_enabled: boolean;
		are_nsfw_visible: boolean;
		is_nsfw_background_blurred: boolean;
		nsfw_banner_override: string;
		is_nsfw_banner_backdrop_blurred: boolean;
		show_keybinding_help_popup: boolean;
		image_overlay_type: App.Enum.ImageOverlayType;
		can_rotate: boolean;
		can_autoplay: boolean;
		is_exif_disabled: boolean;
		is_favourite_enabled: boolean;
		photo_previous_next_size: App.Enum.SmallLargeType;
		is_details_links_enabled: boolean;
		display_thumb_album_overlay: App.Enum.ThumbOverlayVisibilityType;
		display_thumb_photo_overlay: App.Enum.ThumbOverlayVisibilityType;
		album_subtitle_type: App.Enum.ThumbAlbumSubtitleType;
		album_decoration: App.Enum.AlbumDecorationType;
		album_decoration_orientation: App.Enum.AlbumDecorationOrientation;
		number_albums_per_row_mobile: 1 | 2 | 3;
		photo_thumb_info: App.Enum.PhotoThumbInfoType;
		is_photo_thumb_tags_enabled: boolean;
		is_thumb_download_enabled: boolean;
		is_thum2x_download_enabled: boolean;
		is_small_download_enabled: boolean;
		is_small2x_download_enabled: boolean;
		is_medium_download_enabled: boolean;
		is_medium2x_download_enabled: boolean;
		clockwork_url: string | null;
		slideshow_timeout: number;
		is_slideshow_enabled: boolean;
		is_timeline_left_border_visible: boolean;
		title: string;
		dropbox_api_key: string;
		is_se_enabled: boolean;
		is_se_preview_enabled: boolean;
		is_se_info_hidden: boolean;
		is_live_metrics_enabled: boolean;
		is_basic_auth_enabled: boolean;
		is_webauthn_enabled: boolean;
		is_registration_enabled: boolean;
		is_scroll_to_navigate_photos_enabled: boolean;
		is_swipe_vertically_to_go_back_enabled: boolean;
		default_homepage: string;
	};
	export type LandingPageResource = {
		landing_page_enable: boolean;
		landing_background_landscape: string;
		landing_background_portrait: string;
		landing_subtitle: string;
		landing_title: string;
		site_owner: string;
		site_title: string;
		footer: App.Http.Resources.GalleryConfigs.FooterConfig;
	};
	export type MapProviderData = {
		layer: string;
		attribution: string;
	};
	export type PhotoLayoutConfig = {
		photo_layout_justified_row_height: number;
		photo_layout_masonry_column_width: number;
		photo_layout_grid_column_width: number;
		photo_layout_square_column_width: number;
		photo_layout_gap: number;
	};
	export type RegisterData = {
		success: boolean;
	};
	export type RootConfig = {
		is_album_timeline_enabled: boolean;
		is_search_accessible: boolean;
		show_keybinding_help_button: boolean;
		album_thumb_css_aspect_ratio: App.Enum.AspectRatioType;
		back_button_enabled: boolean;
		back_button_text: string;
		back_button_url: string;
		timeline_album_granularity: App.Enum.TimelineAlbumGranularity;
		header_image_url: string;
		is_header_bar_transparent: boolean;
		is_header_bar_gradient: boolean;
	};
	export type SettingsConfig = {
		default_old_settings: boolean;
		default_expert_settings: boolean;
		default_all_settings: boolean;
	};
	export type UploadConfig = {
		upload_processing_limit: number;
		upload_chunk_size: number;
	};
}
declare namespace App.Http.Resources.Models {
	export type AbstractAlbumResource = {
		config: App.Http.Resources.GalleryConfigs.AlbumConfig;
		resource:
			| App.Http.Resources.Models.AlbumResource
			| App.Http.Resources.Models.SmartAlbumResource
			| App.Http.Resources.Models.TagAlbumResource
			| null;
	};
	export type AccessPermissionResource = {
		id: number | null;
		user_id: number | null;
		user_group_id: number | null;
		username: string | null;
		user_group_name: string | null;
		album_title: string | null;
		album_id: string | null;
		grants_full_photo_access: boolean;
		grants_download: boolean;
		grants_upload: boolean;
		grants_edit: boolean;
		grants_delete: boolean;
	};
	export type AlbumResource = {
		id: string;
		title: string;
		owner_name: string | null;
		description: string | null;
		copyright: string | null;
		track_url: string | null;
		license: string;
		header_id: string | null;
		parent_id: string | null;
		has_albums: boolean;
		albums: App.Http.Resources.Models.ThumbAlbumResource[];
		photos: App.Http.Resources.Models.PhotoResource[];
		cover_id: string | null;
		thumb: App.Http.Resources.Models.ThumbResource | null;
		policy: App.Http.Resources.Models.Utils.AlbumProtectionPolicy;
		rights: App.Http.Resources.Rights.AlbumRightsResource;
		preFormattedData: App.Http.Resources.Models.Utils.PreFormattedAlbumData;
		editable: App.Http.Resources.Editable.EditableBaseAlbumResource | null;
		is_pinned: boolean;
		statistics: App.Http.Resources.Models.AlbumStatisticsResource | null;
	};
	export type AlbumStatisticsResource = {
		visit_count: number;
		download_count: number;
		shared_count: number;
	};
	export type ColourPaletteResource = {
		colour_1: string;
		colour_2: string;
		colour_3: string;
		colour_4: string;
		colour_5: string;
	};
	export type ConfigCategoryResource = {
		cat: string;
		name: string;
		description: string;
		configs: App.Http.Resources.Models.ConfigResource[];
		priority: number;
	};
	export type ConfigResource = {
		key: string;
		type: App.Enum.ConfigType | string;
		value: string;
		documentation: string;
		details: string;
		is_expert: boolean;
		require_se: boolean;
		order: number | null;
	};
	export type JobHistoryResource = {
		username: string;
		status: "ready" | "success" | "failure" | "started";
		created_at: string;
		updated_at: string;
		job: string;
	};
	export type LightUserResource = {
		id: number;
		username: string;
	};
	export type LiveMetricsResource = {
		created_at: string;
		visitor_id: string;
		action: App.Enum.MetricsAction;
		photo_id: string | null;
		album_id: string;
		title: string;
		url: string | null;
	};
	export type PhotoResource = {
		id: string;
		album_id: string | null;
		altitude: number | null;
		aperture: string | null;
		checksum: string;
		created_at: string;
		description: string;
		focal: string | null;
		is_starred: boolean;
		iso: string | null;
		latitude: number | null;
		lens: string | null;
		license: App.Enum.LicenseType;
		live_photo_checksum: string | null;
		live_photo_content_id: string | null;
		live_photo_url: string | null;
		location: string | null;
		longitude: number | null;
		make: string | null;
		model: string | null;
		original_checksum: string;
		shutter: string | null;
		size_variants: App.Http.Resources.Models.SizeVariantsResouce;
		tags: Array<string>;
		taken_at: string | null;
		taken_at_orig_tz: string | null;
		title: string;
		type: string;
		updated_at: string;
		rights: App.Http.Resources.Rights.PhotoRightsResource;
		next_photo_id: string | null;
		previous_photo_id: string | null;
		preformatted: App.Http.Resources.Models.Utils.PreformattedPhotoData;
		precomputed: App.Http.Resources.Models.Utils.PreComputedPhotoData;
		timeline: App.Http.Resources.Models.Utils.TimelineData | null;
		palette: App.Http.Resources.Models.ColourPaletteResource | null;
		statistics: App.Http.Resources.Models.PhotoStatisticsResource | null;
	};
	export type PhotoStatisticsResource = {
		visit_count: number;
		download_count: number;
		favourite_count: number;
		shared_count: number;
	};
	export type SizeVariantResource = {
		type: App.Enum.SizeVariantType;
		locale: string;
		filesize: string;
		height: number;
		width: number;
		url: string | null;
	};
	export type SizeVariantsResouce = {
		original: App.Http.Resources.Models.SizeVariantResource | null;
		medium2x: App.Http.Resources.Models.SizeVariantResource | null;
		medium: App.Http.Resources.Models.SizeVariantResource | null;
		small2x: App.Http.Resources.Models.SizeVariantResource | null;
		small: App.Http.Resources.Models.SizeVariantResource | null;
		thumb2x: App.Http.Resources.Models.SizeVariantResource | null;
		thumb: App.Http.Resources.Models.SizeVariantResource | null;
		placeholder: App.Http.Resources.Models.SizeVariantResource | null;
	};
	export type SmartAlbumResource = {
		id: string;
		title: string;
		photos: App.Http.Resources.Models.PhotoResource[];
		thumb: App.Http.Resources.Models.ThumbResource | null;
		policy: App.Http.Resources.Models.Utils.AlbumProtectionPolicy;
		rights: App.Http.Resources.Rights.AlbumRightsResource;
		preFormattedData: App.Http.Resources.Models.Utils.PreFormattedAlbumData;
		statistics: null | null;
	};
	export type TagAlbumResource = {
		id: string;
		title: string;
		owner_name: string | null;
		copyright: string | null;
		is_tag_album: boolean;
		show_tags: Array<string>;
		photos: App.Http.Resources.Models.PhotoResource[];
		thumb: App.Http.Resources.Models.ThumbResource | null;
		policy: App.Http.Resources.Models.Utils.AlbumProtectionPolicy;
		rights: App.Http.Resources.Rights.AlbumRightsResource;
		preFormattedData: App.Http.Resources.Models.Utils.PreFormattedAlbumData;
		editable: App.Http.Resources.Editable.EditableBaseAlbumResource | null;
		statistics: App.Http.Resources.Models.AlbumStatisticsResource | null;
	};
	export type TargetAlbumResource = {
		id: string | null;
		title: string;
		original: string;
		short_title: string;
		thumb: string;
	};
	export type ThumbAlbumResource = {
		id: string;
		title: string;
		description: string | null;
		thumb: App.Http.Resources.Models.ThumbResource | null;
		is_nsfw: boolean;
		is_pinned: boolean;
		is_public: boolean;
		is_link_required: boolean;
		is_password_required: boolean;
		is_tag_album: boolean;
		has_subalbum: boolean;
		num_subalbums: number;
		num_photos: number;
		created_at: string;
		formatted_min_max: string | null;
		owner: string | null;
		rights: App.Http.Resources.Rights.AlbumRightsResource;
		timeline: App.Http.Resources.Models.Utils.TimelineData | null;
	};
	export type ThumbResource = {
		id: string;
		type: string;
		thumb: string | null;
		thumb2x: string | null;
		placeholder: string | null;
	};
	export type UserGroupResource = {
		id: number;
		name: string;
		description: string;
		members: App.Http.Resources.Models.UserMemberGroupResource[];
		rights: App.Http.Resources.Rights.UserGroupRightResource;
	};
	export type UserManagementResource = {
		id: number;
		username: string;
		may_administrate: boolean;
		may_upload: boolean;
		may_edit_own_settings: boolean;
		is_owner: boolean;
		quota_kb: number | null;
		description: string | null;
		note: string | null;
		space: number | null;
	};
	export type UserMemberGroupResource = {
		id: number;
		username: string;
		role: App.Enum.UserGroupRole;
	};
	export type UserResource = {
		id: number | null;
		has_token: boolean | null;
		username: string | null;
		email: string | null;
	};
	export type WebAuthnResource = {
		id: string;
		alias: string | null;
		created_at: string;
	};
}
declare namespace App.Http.Resources.Models.Duplicates {
	export type Duplicate = {
		album_id: string;
		album_title: string;
		photo_id: string;
		photo_title: string;
		checksum: string;
		url: string | null;
	};
	export type DuplicateCount = {
		pure_duplicates: number;
		title_duplicates: number;
		duplicates_within_album: number;
	};
}
declare namespace App.Http.Resources.Models.Utils {
	export type AlbumProtectionPolicy = {
		is_public: boolean;
		is_link_required: boolean;
		is_nsfw: boolean;
		grants_full_photo_access: boolean;
		grants_download: boolean;
		grants_upload: boolean;
		is_password_required: boolean;
	};
	export type PreComputedPhotoData = {
		is_video: boolean;
		is_raw: boolean;
		is_livephoto: boolean;
		is_camera_date: boolean;
		has_exif: boolean;
		has_location: boolean;
		is_taken_at_modified: boolean;
	};
	export type PreFormattedAlbumData = {
		url: string | null;
		title: string;
		min_max_text: string | null;
		album_id: string;
		license: string;
		num_children: number;
		num_photos: number;
		created_at: string | null;
		description: string | null;
		copyright: string | null;
	};
	export type PreformattedPhotoData = {
		created_at: string;
		taken_at: string | null;
		date_overlay: string;
		shutter: string;
		aperture: string;
		iso: string;
		lens: string;
		duration: string;
		fps: string;
		filesize: string;
		resolution: string;
		latitude: string | null;
		longitude: string | null;
		altitude: string | null;
		license: string;
		description: string;
	};
	export type TimelineData = {
		time_date: string;
		format: string;
	};
	export type UserToken = {
		token: string;
	};
}
declare namespace App.Http.Resources.Oauth {
	export type OauthRegistrationData = {
		provider_type: App.Enum.OauthProvidersType;
		is_enabled: boolean;
		registration_route: string;
	};
}
declare namespace App.Http.Resources.Rights {
	export type AlbumRightsResource = {
		can_edit: boolean;
		can_share: boolean;
		can_share_with_users: boolean;
		can_download: boolean;
		can_upload: boolean;
		can_move: boolean;
		can_delete: boolean;
		can_transfer: boolean;
		can_access_original: boolean;
		can_pasword_protect: boolean;
	};
	export type GlobalRightsResource = {
		root_album: App.Http.Resources.Rights.RootAlbumRightsResource;
		settings: App.Http.Resources.Rights.SettingsRightsResource;
		user_management: App.Http.Resources.Rights.UserManagementRightsResource;
		user: App.Http.Resources.Rights.UserRightsResource;
		modules: App.Http.Resources.Rights.ModulesRightsResource;
	};
	export type ModulesRightsResource = {
		is_map_enabled: boolean;
		is_mod_frame_enabled: boolean;
		is_mod_flow_enabled: boolean;
		is_photo_timeline_enabled: boolean;
	};
	export type PhotoRightsResource = {
		can_edit: boolean;
		can_download: boolean;
		can_access_full_photo: boolean;
	};
	export type RootAlbumRightsResource = {
		can_edit: boolean;
		can_upload: boolean;
		can_see_live_metrics: boolean;
	};
	export type SettingsRightsResource = {
		can_edit: boolean;
		can_see_logs: boolean;
		can_see_diagnostics: boolean;
		can_update: boolean;
		can_access_dev_tools: boolean;
		can_acess_user_groups: boolean;
	};
	export type UserGroupRightResource = {
		can_edit: boolean;
		can_manage: boolean;
	};
	export type UserManagementRightsResource = {
		can_create: boolean;
		can_list: boolean;
		can_edit: boolean;
		can_delete: boolean;
	};
	export type UserRightsResource = {
		can_edit: boolean;
	};
}
declare namespace App.Http.Resources.Root {
	export type AuthConfig = {
		oauthProviders: { [key: number]: App.Enum.OauthProvidersType };
		u2f_enabled: boolean;
	};
	export type VersionResource = {
		version: string | null;
		is_new_release_available: boolean;
		is_git_update_available: boolean;
	};
}
declare namespace App.Http.Resources.Search {
	export type InitResource = {
		search_minimum_length: number;
		photo_layout: App.Enum.PhotoLayoutType;
	};
	export type ResultsResource = {
		albums: App.Http.Resources.Models.ThumbAlbumResource[];
		photos: App.Http.Resources.Models.PhotoResource[];
		current_page: number;
		from: number;
		last_page: number;
		per_page: number;
		to: number;
		total: number;
	};
}
declare namespace App.Http.Resources.Statistics {
	export type Album = {
		username: string;
		title: string;
		is_nsfw: boolean;
		left: number;
		right: number;
		num_photos: number;
		num_descendants: number;
		size: number;
	};
	export type CountsData = {
		data: Array<App.Http.Resources.Statistics.DayCount>;
		low_number_of_shoots_per_day: number;
		medium_number_of_shoots_per_day: number;
		high_number_of_shoots_per_day: number;
		min_created_at: string;
		min_taken_at: string;
	};
	export type DayCount = {
		date: string;
		count: number;
	};
	export type Sizes = {
		type: App.Enum.SizeVariantType;
		label: string;
		size: number;
	};
	export type UserSpace = {
		id: number;
		username: string;
		size: number;
	};
}
declare namespace App.Http.Resources.Tags {
	export type TagResource = {
		id: number;
		name: string;
		num: number;
	};
	export type TagWithPhotosResource = {
		id: number;
		name: string;
		photos: App.Http.Resources.Models.PhotoResource[];
	};
	export type TagsResource = {
		can_edit: boolean;
		tags: App.Http.Resources.Tags.TagResource[];
	};
}
