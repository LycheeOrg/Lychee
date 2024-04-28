export type LycheeException = {
	message: string;
	exception: string;
	file?: string;
	line?: number;
	trace?: string[];
	previous_exception?: LycheeException | null;
};

export type Version = {
	major: number;
	minor: number;
	patch: number;
};

export type OverlayTypes = "none" | "exif" | "date" | "desc";

export type PhotoLayoutType = "square" | "justified" | "unjustified" | "masonry" | "grid";

export type Layouts = {
	photos_layout: PhotoLayoutType;
	photo_layout_justified_row_height: number;
	photo_layout_masonry_column_width: number;
	photo_layout_grid_column_width: number;
	photo_layout_square_column_width: number;
	photo_layout_gap: number;
};

export type Photo = {
	id: string;
	title: string;
	description: string | null;
	tags: string[];
	is_public: boolean;
	type: string | null;
	iso: string | null;
	aperture: string | null;
	make: string | null;
	model: string | null;
	lens: string | null;
	shutter: string | null;
	focal: string | null;
	latitude: number | null;
	longitude: number | null;
	altitude: number | null;
	img_direction: number | null;
	location: string | null;
	taken_at: string | null;
	taken_at_orig_tz: string | null;
	is_starred: boolean;
	live_photo_url: string | null;
	album_id: string | null;
	checksum: string;
	license: string;
	created_at: string;
	updated_at: string;
	live_photo_content_id: string | null;
	live_photo_checksum: string | null;
	size_variants: SizeVariants;
	next_photo_id?: string | null;
	previous_photo_id?: string | null;
	rights: PhotoRightsDTO;
	preformatted: PreFormatted;
	precomputed: PreComputed;
};

export type SizeVariants = {
	original: SizeVariant;
	medium2x: SizeVariant | null;
	medium: SizeVariant | null;
	small2x: SizeVariant | null;
	small: SizeVariant | null;
	thumb2x: SizeVariant | null;
	thumb: SizeVariant | null;
};

export type SizeVariant = {
	type: number;
	url: string;
	width: number;
	height: number;
	filesize: number;
};

export type PreFormatted = {
	created_at: string;
	taken_at: string;
	date_overlay: OverlayTypes;

	shutter: string;
	aperture: string;
	iso: string;

	duration: string;
	fps: string;

	filesize: string;
	resolution: string;
	latitude: string;
	longitude: string;
	altitude: string;

	license: string;
	description: string;
};

export type PreComputed = {
	is_video: boolean;
	is_raw: boolean;
	is_livephoto: boolean;
	is_camera_date: boolean;
	has_exif: boolean;
	has_location: boolean;
};

export type SortingCriterion = {
	column: string;
	order: string;
};

export type Album = {
	id: string;
	parent_id: string;
	created_at: string;
	updated_at: string;
	title: string;
	description: string | null;
	license: string;
	photos: Photo[];
	albums?: Album[];
	cover_id: string | null;
	thumb: Thumb | null;
	owner_name?: string;
	is_nsfw: boolean;
	rights: AlbumRightsDTO;
	policy: AlbumProtectionPolicy;
	num_albums: boolean;
	num_photos: boolean;
	min_taken_at: string | null;
	max_taken_at: string | null;
	sorting: SortingCriterion | null;
};

export type TagAlbum = {
	id: string;
	created_at: string;
	updated_at: string;
	title: string;
	description: string | null;
	show_tags: string[];
	photos: Photo[];
	thumb: Thumb | null;
	owner_name?: string;
	is_nsfw: boolean;
	rights: AlbumRightsDTO;
	policy: AlbumProtectionPolicy;
	min_taken_at: string | null;
	max_taken_at: string | null;
	sorting: SortingCriterion | null;
	is_tag_album: boolean;
};

export type SmartAlbum = {
	id: string;
	title: string;
	photos?: Photo[];
	thumb: Thumb | null;
	rights: AlbumRightsDTO;
	policy: AlbumProtectionPolicy;
};

export type Thumb = {
	id: string;
	type: string;
	thumb: string;
	thumb2x: string | null;
};

export type SharingInfo = {
	shared: { id: number; album_id: string; user_id: number; username: string; title: string }[];
	albums: { id: string; title: string }[];
	users: { id: number; username: string }[];
};

export type SearchResult = {
	albums: Album[];
	tag_albums: TagAlbum[];
	photos: Photo[];
	checksum: string;
};

export type Albums = {
	smart_albums: SmartAlbums;
	tag_albums: TagAlbum[];
	albums: Album[];
	shared_albums: Album[];
};

export type SmartAlbums = {
	unsorted: SmartAlbum | null;
	starred: SmartAlbum | null;
	public: SmartAlbum | null; // TO BE KILLED
	recent: SmartAlbum | null;
	on_this_day: SmartAlbum | null;
};

/**
 * The IDs of the built-in, smart albums.
 */
export enum SmartAlbumID {
	UNSORTED = "unsorted",
	STARRED = "starred",
	PUBLIC = "public",
	RECENT = "recent",
	ON_THIS_DAY = "on_this_day",
}

export type User = {
	id: number;
	username: string;
	email: string;
	has_token: boolean;
};

export type UserWithCapabilitiesDTO = {
	id: number;
	username: string;
	may_administrate: boolean;
	may_upload: boolean;
	may_edit_own_settings: boolean;
};

export type WebAuthnCredential = {
	id: string;
};

export type PositionData = {
	id: string | null;
	title: string | null;
	photos: Photo[];
	track_url: string | null;
};

export type ConfigSetting = {
	id: number;
	key: string;
	value: string | null;
	cat: string;
	type_range: string;
	confidentiality: number;
	description: string;
};

export type LogEntry = {
	id: number;
	created_at: string;
	updated_at: string;
	type: string;
	function: string;
	line: number;
	text: string;
};

export type DiagnosticInfo = {
	errors: string[];
	infos: string[];
	configs: string[];
	update: number;
};

export type FrameSettings = {
	refresh: number;
};

export type LocaleArray = {
	[key: string]: string;
};

export type InitializationData = {
	user: User | null;
	rights: GlobalRightsDTO;
	update_json: boolean;
	update_available: boolean;
	locale: LocaleArray;
	config: ConfigurationData;
};

export type Feed = {
	url: string;
	mimetype: string;
	title: string;
};

export type ConfigurationData = {
	album_decoration: string;
	album_decoration_orientation: string;
	album_subtitle_type: string;
	allow_username_change: string;
	check_for_updates: string;
	default_license?: string;
	delete_imported?: string;
	grants_download: string;
	dropbox_key?: string;
	editor_enabled: string;
	rss_enable: string;
	rss_feeds: Feed[];
	grants_full_photo_access: string;
	image_overlay_type: OverlayTypes;
	landing_page_enable: string;
	lang: string;
	lang_available: string[];
	layout: PhotoLayoutType;
	location?: string;
	location_decoding: string;
	location_show: string;
	location_show_public: string;
	map_display: string;
	map_display_direction: string;
	map_display_public: string;
	map_include_subalbums: string;
	map_provider: string;
	new_photos_notification: string;
	nsfw_blur: string;
	nsfw_visible: string;
	nsfw_warning: string;
	nsfw_warning_admin: string;
	nsfw_banner_override: string;
	public_photos_hidden: string;
	public_search: string;
	share_button_visible: string;
	skip_duplicates?: string;
	sorting_albums: SortingCriterion;
	sorting_photos: SortingCriterion;
	swipe_tolerance_x: string;
	swipe_tolerance_y: string;
	upload_processing_limit: string;
	version: Version | null;
	smart_album_visibilty: SmartAlbumVisibility;
};

/**
 * The JSON object for incremental reports sent by the
 * back-end within a streamed response.
 */
export type ImportReport = {
	type: string;
};

/**
 * The JSON object for cumulative progress reports sent by the
 * back-end within a streamed response.
 */
export type ImportProgressReport = {
	type: string;
	path: string;
	progress: number;
};

/**
 * The JSON object for events sent by the back-end within a streamed response.
 */
export type ImportEventReport = {
	type: string;
	subtype: string;
	severity: number;
	path: string | null;
	message: string;
};

/**
 * The JSON object for Policy on Albums
 */
export type AlbumProtectionPolicy = {
	is_nsfw: boolean;
	is_public: boolean;
	is_link_required: boolean;
	is_password_required: boolean;
	grants_full_photo_access: boolean;
	grants_download: boolean;
};

/**
 * The JSON object for Rights on users management
 */
export type UserManagementRightsDTO = {
	can_create: boolean;
	can_list: boolean;
	can_edit: boolean;
	can_delete: boolean;
};

/**
 * The JSON object for Rights on a User
 */
export type UserRightsDTO = {
	can_edit: boolean;
	can_use_2fa: boolean;
};

/**
 * The JSON object for Rights on Settings
 */
export type SettingsRightsDTO = {
	can_edit: boolean;
	can_see_logs: boolean;
	can_clear_logs: boolean;
	can_see_diagnostics: boolean;
	can_update: boolean;
};

/**
 * The JSON object for Rights on Settings
 */
export type RootAlbumRightsDTO = {
	can_edit: boolean;
	can_upload: boolean;
	can_download: boolean;
	can_import_from_server: boolean;
};

/**
 * The JSON object for Rights on Photos
 */
export type PhotoRightsDTO = {
	can_edit: boolean;
	can_download: boolean;
	can_access_full_photo: boolean;
};

/**
 * The JSON object for Rights on Album
 */
export type AlbumRightsDTO = {
	can_edit: boolean;
	can_share_with_users: boolean;
	can_download: boolean;
	can_upload: boolean;
};

/**
 * The JSON object for Rights on Global Application
 */
export type GlobalRightsDTO = {
	root_album: RootAlbumRightsDTO;
	settings: SettingsRightsDTO;
	user_management: UserManagementRightsDTO;
	user: UserRightsDTO;
};

/**
 * The JSON object containing the visibility of smart albums
 */
export type SmartAlbumVisibility = {
	recent: boolean;
	starred: boolean;
	on_this_day: boolean;
};
