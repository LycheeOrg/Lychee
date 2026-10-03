# Settings dependencies

Relationship graph between the rows of the `configs` table. It is the source for the
`configs.required_keys` column, which the v8 settings UI uses to dim a setting whose
prerequisites are off and to highlight those prerequisites on hover
([ConfigGroup.vue](../../../resources/js/v8/components/settings/ConfigGroup.vue)).

## Mechanism

- `required_keys` is a comma-separated list of config keys. A setting is *active* when
  every listed key currently has the value `1`; otherwise it is dimmed.
- Only boolean (`0|1`) settings can be listed. The list is a conjunction: every key must
  be on. Chains are written out in full (`a,b` means *b requires a*, and the row
  requires both).
- A relation is recorded only when the dependent setting has **no observable effect**
  unless the required key is on, as verified in the consuming code. Relations the
  mechanism cannot express are listed at the end so that they are not mistaken for
  omissions.

## Dependencies by category

### Admin

| Setting | Requires | Reason |
| --- | --- | --- |
| `update_check_every_days` | `check_for_updates` | TTL of the cached remote version/changelog fetch, which `CheckUpdateAvailability` only performs when update checks are on. |

### Footer

| Setting | Requires | Reason |
| --- | --- | --- |
| `site_copyright_begin`, `site_copyright_end`, `copyright_text` | `footer_show_copyright` | Only read by `FooterConfig::get_copyright()`, and the footer renders the copyright block only when the flag is on. |
| `sm_facebook_url`, `sm_flickr_url`, `sm_instagram_url`, `sm_twitter_url`, `sm_youtube_url`, `sm_pinterest_url`, `sm_deviantart_url`, `sm_tumblr_url`, `sm_500px_url`, `sm_pixelfeed_url`, `sm_discord_url`, `sm_reddit_url` | `footer_show_social_media` | Social links are rendered only when the flag is on. |

### Gallery

| Setting | Requires | Reason |
| --- | --- | --- |
| `details_links_public` | `details_links_enabled` | `InitConfig` only consults the public flag when the links module is on. |
| `slideshow_timeout` | `slideshow_enabled` | Slideshow controls are hidden when the slideshow is off. |
| `photo_ken_burns_on_hover_scale`, `photo_ken_burns_on_hover_duration` | `photo_ken_burns_on_hover_enabled` | Parameters of the hover zoom effect. |
| `photo_flags_enabled`, `album_flags_enabled`, `smart_album_flags_enabled` | `flags_enabled` | `InitConfig` ANDs every flag with the master switch. |
| `cover_id_flag_enabled`, `header_id_flag_enabled`, `highlighted_flag_enabled`, `validated_flag_enabled` | `flags_enabled,photo_flags_enabled` | Photo flags are ANDed with both the master switch and the photo-flags switch. |
| `public_hidden_flag_enabled`, `public_visible_flag_enabled`, `password_flag_enabled`, `sensitive_flag_enabled` | `flags_enabled` | Album flags are ANDed with the master switch. Their scope switch is `album_flags_enabled` *or* `smart_album_flags_enabled` depending on the thumb, which is not expressible (see below). |

### Image Processing

| Setting | Requires | Reason |
| --- | --- | --- |
| `keep_original_untouched` | `auto_fix_orientation` | `ReplaceOriginalWithBackup` only acts on the backup file created when `PlacePhoto` normalises orientation. |
| `colour_extraction_driver` | `enable_colour_extractions` | Driver chosen inside `ExtractColoursJob`, which is dispatched only when extraction is on. |
| `download_archive_chunk_size` | `download_archive_chunked` | Chunk size of chunked archives. |
| `folder_upload_max_depth` | `folder_upload_enabled` | Recursion depth of folder drag-and-drop uploads. |
| `embed_metadata_update_checksum_enabled` | `embed_metadata_in_files_enabled` | Only evaluated after a file has been rewritten with embedded metadata. |
| `zip_bomb_max_total_size`, `zip_bomb_max_file_size`, `zip_bomb_max_entries`, `zip_bomb_max_ratio`, `zip_bomb_delete_rejected_file` | `extract_zip_on_upload` | Read by `ExtractZip`, which `PhotoController` dispatches only when zip extraction is on. |

### Mod Back Button

| Setting | Requires | Reason |
| --- | --- | --- |
| `back_button_text`, `back_button_url` | `back_button_enabled` | Label and target of the back button. |

### Mod Cache

| Setting | Requires | Reason |
| --- | --- | --- |
| `cache_ttl` | `cache_enabled` | Lifetime of cached HTTP responses. |
| `managed_cache_albums_enabled`, `managed_cache_ttl` | `managed_cache_enabled` | Album-listing consumer and lifetime of the managed cache. |

### Mod Flow

| Setting | Requires | Reason |
| --- | --- | --- |
| `flow_public`, `flow_base`, `flow_max_items`, `flow_strategy`, `flow_include_sub_albums`, `flow_include_photos_from_children`, `flow_open_album_on_click`, `flow_display_open_album_button`, `flow_highlight_first_picture`, `flow_min_max_enabled`, `flow_display_statistics`, `flow_compact_mode_enabled`, `flow_image_header_enabled`, `date_format_flow_published`, `flow_blur_nsfw_enabled`, `hide_nsfw_in_flow` | `flow_enabled` | Flow module options. The two NSFW keys live in *Mod NSFW* but only affect the Flow listing. |
| `flow_min_max_order`, `date_format_flow_min_max` | `flow_enabled,flow_min_max_enabled` | Order and format of the min-max date shown on Flow cards. |
| `flow_image_header_cover`, `flow_image_header_height` | `flow_enabled,flow_image_header_enabled` | Fit and height of the card image header. |
| `flow_carousel_enabled` | `flow_enabled,flow_image_header_enabled` | `AlbumCard` renders the carousel only when `is_image_header_enabled && is_carousel_enabled`. |
| `flow_carousel_height` | `flow_enabled,flow_image_header_enabled,flow_carousel_enabled` | Height of that carousel. |

### Mod Frame

| Setting | Requires | Reason |
| --- | --- | --- |
| `random_album_id`, `mod_frame_refresh`, `hide_nsfw_in_frame` | `mod_frame_enabled` | Frame source album, refresh rate and NSFW filter. `hide_nsfw_in_frame` lives in *Mod NSFW*. |

### Mod Map

| Setting | Requires | Reason |
| --- | --- | --- |
| `map_provider`, `map_include_subalbums`, `map_display_direction`, `map_display_public`, `hide_nsfw_in_map` | `map_display` | Map module options; `map_provider` is served by `MapController` only. `hide_nsfw_in_map` lives in *Mod NSFW*. |
| `location_decoding_timeout` | `location_decoding` | Timeout of the geodecoding request. |
| `location_show` | `location_decoding` | Only the decoded location is shown, and it only exists when decoding is on. |
| `location_show_public` | `location_decoding,location_show` | `QueryPhotoDetails` evaluates it as `location_show && (logged in \|\| location_show_public)`. |
| `gps_coordinate_display_public` | `gps_coordinate_display` | `QueryPhotoDetails` evaluates it as `gps_coordinate_display && (logged in \|\| gps_coordinate_display_public)`. |

`gps_coordinate_display` is independent of `map_display`: it governs the latitude/longitude
fields of the photo sidebar, not the map.

### Mod NSFW

| Setting | Requires | Reason |
| --- | --- | --- |
| `hide_nsfw_in_flow`, `flow_blur_nsfw_enabled` | `flow_enabled` | See *Mod Flow*. |
| `hide_nsfw_in_frame` | `mod_frame_enabled` | See *Mod Frame*. |
| `hide_nsfw_in_landing_page` | `landing_page_enable` | Filters the photos used as landing backgrounds / featured items. |
| `hide_nsfw_in_map` | `map_display` | See *Mod Map*. |
| `hide_nsfw_in_person_albums` | `ai_vision_enabled,ai_vision_face_enabled` | Person albums exist only with facial recognition. |
| `hide_nsfw_in_rss` | `rss_enable` | Filters the RSS feed. |
| `hide_nsfw_in_timeline` | `timeline_page_enabled` | Read by `Timeline`/`TimelineAlbum`, i.e. the timeline page. |

### Mod Privacy

| Setting | Requires | Reason |
| --- | --- | --- |
| `temporary_image_link_when_logged_in`, `temporary_image_link_when_admin`, `temporary_image_link_life_in_seconds` | `temporary_image_link_enabled` | `UrlGenerator::shouldNotUseSignedUrl()` short-circuits on the master switch. |

### Mod Pro

| Setting | Requires | Reason |
| --- | --- | --- |
| `metrics_access` | `metrics_enabled` | Who may read per-item statistics, which are only collected when metrics are on. |
| `live_metrics_access`, `live_metrics_max_time`, `live_metrics_result_limit`, `live_metrics_cleanup` | `live_metrics_enabled` | Live metrics listing, retention and cleanup. |
| `album_header_landing_title_enabled` | `album_enhanced_display_enabled` | Rendered inside the enhanced-header template of `AlbumHeaderPanel` only. |

`album_header_size` is independent: `AlbumHeaderPanel` applies it to both the classic and the
enhanced header.

### Mod RSS

| Setting | Requires | Reason |
| --- | --- | --- |
| `rss_title`, `rss_description`, `rss_max_items`, `rss_recent_days` | `rss_enable` | Feed metadata and window. |

### Mod Rating

| Setting | Requires | Reason |
| --- | --- | --- |
| `rating_public`, `rating_show_only_when_user_rated`, `rating_show_avg_in_details`, `rating_photo_view_mode`, `rating_show_avg_in_photo_view`, `rating_album_view_mode`, `rating_show_avg_in_album_view` | `rating_enabled` | `PhotoPolicy`/`PhotoResource` gate every rating read on the master switch. |

### Mod Renamer

| Setting | Requires | Reason |
| --- | --- | --- |
| `renamer_enforced`, `renamer_photo_title_enabled`, `renamer_album_title_enabled` | `renamer_enabled` | `Renamer` is a no-op when disabled. |
| `renamer_enforced_before`, `renamer_enforced_after` | `renamer_enabled,renamer_enforced` | Position of the owner rules, which are only applied when enforced. |

### Mod Search

| Setting | Requires | Reason |
| --- | --- | --- |
| `search_colour_distance` | `enable_colour_extractions` | `ColourStrategy` matches against palettes that only exist when extraction is on. |

### Mod Timeline

| Setting | Requires | Reason |
| --- | --- | --- |
| `timeline_photos_layout`, `timeline_photos_order`, `timeline_photos_pagination_limit`, `timeline_quick_access_date_format_year`, `timeline_quick_access_date_format_month`, `timeline_quick_access_date_format_day`, `timeline_quick_access_date_format_hour` | `timeline_page_enabled` | Read by `Timeline\InitResource`, `TimelineController`, `Timeline`/`TimelineAlbum` and `TimelineData` for the timeline page only. |

`timeline_photos_enabled` and `timeline_albums_enabled` are the *defaults* for the per-album
photo/album timelines (`AlbumConfig`); the root listing uses `timeline_albums_root_enabled`
and the timeline page uses `timeline_page_enabled`. The granularity, public and date-format
keys serve several of these at once and are therefore listed under *Not expressible*.

### Mod Watermarker

| Setting | Requires | Reason |
| --- | --- | --- |
| `watermark_photo_id`, `watermark_random_path`, `watermark_public`, `watermark_logged_in_users_enabled`, `watermark_original`, `watermark_size`, `watermark_opacity`, `watermark_position`, `watermark_shift_type`, `watermark_shift_x`, `watermark_shift_x_direction`, `watermark_shift_y`, `watermark_shift_y_direction`, `watermark_optout_disabled` | `watermark_enabled` | Watermark source, placement and audience. |

### Mod Webshop

| Setting | Requires | Reason |
| --- | --- | --- |
| `webshop_currency`, `webshop_default_description`, `webshop_allow_guest_checkout`, `webshop_terms_url`, `webshop_privacy_url`, `webshop_default_price_cents`, `webshop_default_license`, `webshop_default_size`, `webshop_offline`, `webshop_lycheeorg_disclaimer_enabled`, `webshop_auto_fulfill_enabled`, `webshop_manual_fulfill_enabled` | `webshop_enabled` | Shop options. |

### Mod Welcome

| Setting | Requires | Reason |
| --- | --- | --- |
| `landing_subtitle`, `landing_background_landscape_mode`, `landing_background_portrait`, `landing_background_portrait_mode`, `landing_logo`, `landing_header_logo`, `landing_layout`, `landing_intro_screen_enabled`, `landing_hero_text_position`, `landing_hero_text_color`, `landing_hero_text_opacity`, `landing_animation_preset`, `landing_about_enabled`, `landing_featured_items_enabled`, `landing_cta_text`, `landing_cta_position`, `landing_cta_shift_type`, `landing_cta_shift_x`, `landing_cta_shift_x_direction`, `landing_cta_shift_y`, `landing_cta_shift_y_direction`, `landing_meridian_explore_offset`, `landing_meridian_contact_offset`, `landing_meridian_explore_line_position`, `landing_meridian_contact_line_position`, `landing_login_position`, `landing_backdrop_opacity` | `landing_page_enable` | Read by `LandingPageResource` only. |
| `landing_about_text` | `landing_page_enable,landing_about_enabled` | Body of the about section. |
| `landing_featured_items_mode`, `landing_featured_items_count` | `landing_page_enable,landing_featured_items_enabled` | Featured-content source and size. |
| `gallery_header_logged_in_enabled`, `gallery_header`, `gallery_header_bar_transparent` | `gallery_header_enabled` | `RootConfig::setHeaderImageUrl()` returns early when the header is off. |
| `gallery_header_bar_gradient` | `gallery_header_enabled,gallery_header_bar_transparent` | Gradient variant of the transparent header bar. |

`landing_title` and `landing_background_landscape` are **not** gated: the former is also
rendered by the enhanced album header (`album_header_landing_title_enabled`), the latter is
the Open Graph image fallback in `Meta` when `sm_card_image_url` is empty.

### Smart Albums

| Setting | Requires | Reason |
| --- | --- | --- |
| `recent_age` | `enable_recent` | Window of the Recent album. |
| `best_pictures_count` | `enable_best_pictures` | Size of the Best Pictures album. |
| `my_best_pictures_count` | `enable_my_best_pictures` | Size of the My Best Pictures album. |
| `PA_override_visibility`, `PA_override_searchability`, `PA_albums_listing_enabled` | `ai_vision_enabled,ai_vision_face_enabled` | Person albums exist only with facial recognition. |

The rating smart albums (`enable_unrated`, `enable_1_star` … `enable_my_best_pictures`) are
listed by `SmartAlbumType::is_enabled()` without consulting `rating_enabled`, so they stay
independent.

### AI Vision

| Setting | Requires | Reason |
| --- | --- | --- |
| `ai_vision_face_enabled`, `ai_vision_nsfw_enabled` | `ai_vision_enabled` | Sub-system toggles under the master switch. |
| `ai_vision_face_permission_mode`, `ai_vision_face_selfie_confidence_threshold`, `ai_vision_face_person_is_searchable_default`, `ai_vision_face_allow_user_claim`, `ai_vision_face_overlay_enabled`, `ai_vision_face_recognition_warning` | `ai_vision_enabled,ai_vision_face_enabled` | Facial-recognition options. |
| `ai_vision_face_overlay_default_visibility` | `ai_vision_enabled,ai_vision_face_enabled,ai_vision_face_overlay_enabled` | Initial state of the overlay. |
| `ai_vision_nsfw_preset`, `ai_vision_nsfw_check_block_action`, `ai_vision_nsfw_monitor_block_action`, `ai_vision_nsfw_trust_but_verify_block_action`, `ai_vision_nsfw_trust_block_action`, `ai_vision_nsfw_sensitive_album_action`, `ai_vision_nsfw_sensitive_no_album_action`, `ai_vision_nsfw_scan_trusted_users`, `ai_vision_nsfw_monitor_hide_on_scan`, `ai_vision_nsfw_trust_but_verify_hide_on_scan`, `ai_vision_nsfw_trust_hide_on_scan` | `ai_vision_enabled,ai_vision_nsfw_enabled` | NSFW classification options. |

### access_permissions

| Setting | Requires | Reason |
| --- | --- | --- |
| `login_required_root_only` | `login_required` | `LoginRequired` middleware consults it only after `login_required` passed. |

### contact

| Setting | Requires | Reason |
| --- | --- | --- |
| `contact_form_security_question`, `contact_form_security_answer`, `contact_form_custom_consent_required`, `contact_form_header`, `contact_form_headline`, `contact_form_contact_method`, `contact_form_message_label`, `contact_form_message_answer`, `contact_form_custom_submit_button_text`, `contact_form_thank_you_message`, `contact_form_enabled_on_gallery`, `contact_form_enabled_on_album`, `contact_form_enabled_for_logged_in` | `contact_form_enabled` | Form content and placement; `InitConfig` ANDs the placement flags with the master switch. |
| `contact_form_custom_consent_text`, `contact_form_custom_privacy_url` | `contact_form_enabled,contact_form_custom_consent_required` | Both are rendered inside the consent checkbox label of `ContactForm`. |

### gestures

| Setting | Requires | Reason |
| --- | --- | --- |
| `photo_minimap_idle_opacity` | `is_photo_minimap_enabled` | Desktop minimap idle opacity. |
| `photo_minimap_idle_opacity_mobile` | `is_photo_minimap_enabled_mobile` | Touch-device minimap idle opacity. |

## Relations the mechanism cannot express

These are real dependencies that `required_keys` cannot encode. They are deliberately left
empty rather than approximated.

### Either-of (disjunction) prerequisites

| Setting | Effective when | Why not encoded |
| --- | --- | --- |
| `public_hidden_flag_enabled`, `public_visible_flag_enabled`, `password_flag_enabled`, `sensitive_flag_enabled` | `album_flags_enabled` (regular albums) or `smart_album_flags_enabled` (smart albums) | `useAlbumFlags` picks the scope switch per thumb. Only the shared `flags_enabled` is encoded. |
| `timeline_photos_public`, `timeline_photos_granularity`, `timeline_photo_date_format_year/month/day/hour` | `timeline_page_enabled` or `timeline_photos_enabled` (per-album photo timeline) or `album_date_scrubber_enabled` (day format) | Served to the timeline page, album photo buckets and the date scrubber alike. |
| `timeline_albums_public`, `timeline_albums_granularity`, `timeline_album_date_format_year/month/day` | `timeline_albums_root_enabled` (root) or `timeline_albums_enabled` (per-album) | `RootConfig` and `AlbumConfig` read them independently. |
| `timeline_left_border_enabled` | any album or photo timeline | Applied by both `AlbumThumbPanel` and `PhotoThumbPanelList`. |
| `timeline_lens_falloff`, `timeline_lens_height`, `timeline_lens_magnification` | `album_date_scrubber_enabled` or `timeline_page_enabled` | Passed to the date rail of albums and of the timeline page. |
| `nsfw_banner_blur_backdrop`, `nsfw_banner_override` | `nsfw_warning` (guests) or `nsfw_warning_admin` (logged in) | `AlbumConfig` chooses the switch by authentication state. |
| `metrics_logged_in_users_enabed` | `metrics_enabled` or `live_metrics_enabled` | `MetricsController::shouldMeasure()` measures when either is on. |
| `photo_minimap_fade_delay` | `is_photo_minimap_enabled` or `is_photo_minimap_enabled_mobile` | One delay shared by both device classes. |
| `landing_title` | `landing_page_enable` or `album_header_landing_title_enabled` | See *Mod Welcome*. |
| `landing_background_landscape` | `landing_page_enable` or Open Graph fallback | See *Mod Welcome*. |
| `cache_event_logging` | `cache_enabled` or `managed_cache_enabled` | `CacheListener` logs events of every cache store. |

### Non-boolean prerequisites

| Setting | Effective when | Why not encoded |
| --- | --- | --- |
| `title_bucket_prefix_length` | `title_bucket_mode = alphabetical` | Enum parent. |
| `photo_title_bucket_prefix_length` | `photo_title_bucket_mode = alphabetical` | Enum parent. |
| `albums_infinite_scroll_threshold` | `albums_pagination_ui_mode = infinite_scroll` | Enum parent. |
| `photos_infinite_scroll_threshold` | `photos_pagination_ui_mode = infinite_scroll` | Enum parent. |
| `photo_thumb_tags_enabled` | `photo_thumb_info = title` | Enum parent. |
| `album_decoration_orientation` | `album_decoration ≠ none` | Enum parent. |
| `thumb_min_max_order`, `date_format_album_thumb` | `album_subtitle_type = takedate` | Enum parent. |
| `photo_layout_gap`, `photo_layout_*_column_width`, `photo_layout_justified_row_height` | matching value of `layout` | Enum parent, also overridable per album. |
| `video_thumbnail_frame_seconds` | `video_thumbnail_frame_mode = custom` | Enum parent. |
| `contact_form_security_answer` | `contact_form_security_question ≠ ''` | String parent. |
| `gallery_password_cookie_lifetime` | `gallery_password` set | Password parent. |
| `exiftool_path`, `ffmpeg_path`, `ffprobe_path` | `has_exiftool` / `has_ffmpeg` ≠ 0 | Tri-state (`0|1|2`) parent. |
| `landing_meridian_*`, `landing_login_position`, `landing_hero_text_position` | matching value of `landing_layout` | Enum parent; only `landing_page_enable` is encoded. |

### Inverse prerequisites

| Setting | Effective when | Why not encoded |
| --- | --- | --- |
| `sync_delete_missing_photos`, `sync_delete_missing_albums` | `sync_dry_run = 0` | The mechanism only tests for `1`. |
| `flow_blur_nsfw_enabled` | `hide_nsfw_in_flow = 0` | Blurring is moot when sensitive albums are hidden. |

## Independent despite appearances

| Setting | Looks like it requires | Actual behaviour |
| --- | --- | --- |
| `gps_coordinate_display` | `map_display` | Governs the sidebar coordinates, not the map. |
| `album_header_size` | `album_enhanced_display_enabled` | Applied to both header styles. |
| `timeline_albums_root_enabled` | `timeline_albums_enabled` | The root listing reads only the root key. |
| `skip_duplicates_early`, `skip_duplicates_not_owned` | `skip_duplicates` | Evaluated on their own code paths (`ImportPhotos`, `ThrowUnownedDuplicate`) regardless of `skip_duplicates`. |
| `enable_unrated` … `enable_my_best_pictures` | `rating_enabled` | `SmartAlbumType::is_enabled()` does not consult it. |
| `show_selected_cover_on_locked_albums` | `show_cover_of_locked_albums` | Takes effect on its own, as its description states. |
| `nsfw_warning_admin` | `nsfw_warning` | Alternative switch for logged-in users, not a refinement. |
| `disable_thumb2x_download`, `disable_small2x_download`, `disable_medium2x_download` | `thumb_2x`, `small_2x`, `medium_2x` | Previously generated 2x variants still exist and are still served. |
