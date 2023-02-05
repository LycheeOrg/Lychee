<?php

namespace App\Http\Resources;

use App\DTO\AlbumSortingCriterion;
use App\DTO\PhotoSortingCriterion;
use App\Exceptions\Handler;
use App\Metadata\Versions\InstalledVersion;
use App\Models\Configs;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Spatie\Feed\Helpers\FeedContentType;

class ConfigurationResource extends JsonResource
{
	public function __construct()
	{
		parent::__construct(null);
	}

	/**
	 * Transform the resource into an array.
	 *
	 * @param \Illuminate\Http\Request $request
	 *
	 * @return array
	 */
	public function toArray($request): array
	{
		$lycheeVersion = resolve(InstalledVersion::class);
		$isAdmin = Auth::user()?->may_administrate === true;
		$rss_feeds = [];

		if (Configs::getValueAsBool('rss_enable')) {
			try {
				/** @var array<string, array{format: ?string, title: ?string}> $feeds */
				$feeds = resolve(Repository::class)->get('feed.feeds', []);
				foreach ($feeds as $name => $feed) {
					$rss_feeds[] = [
						'url' => route("feeds.{$name}"),
						'mimetype' => FeedContentType::forLink($feed['format'] ?? 'atom'),
						'title' => $feed['title'] ?? '',
					];
				}
			} catch (\Throwable $e) {
				// do nothing, but report the exception, if the
				// configuration for the RSS feed cannot be loaded or
				// if the route to any RSS feed or the mime type of any
				// feed cannot be resolved
				Handler::reportSafely($e);
				$rss_feeds = [];
			}
		}

		return [
			// Computed
			'lang_available' => $this->when(Auth::check(), config('app.supported_locale')),
			'version' => $this->when(Auth::check() || !Configs::getValueAsBool('hide_version_number'), $lycheeVersion->getVersion()),
			'rss_feeds' => $rss_feeds,
			'allow_username_change' => $this->when(Auth::check(), Configs::getValueAsBool('allow_username_change')),

			// Config attributes
			// Admin
			$this->mergeWhen($isAdmin, [
				// computerd
				'location' => base_path('public/'),

				// from config
				'SA_enabled' => Configs::getValueAsBool('SA_enabled'),
				'SL_enable' => Configs::getValueAsBool('SL_enable'),
				'SL_for_admin' => Configs::getValueAsBool('SL_for_admin'),
				'SL_life_time_days' => Configs::getValueAsInt('SL_life_time_days'),
				'allow_online_git_pull' => Configs::getValueAsBool('allow_online_git_pull'),
				'apply_composer_update' => Configs::getValueAsBool('apply_composer_update'),
				'compression_quality' => Configs::getValueAsInt('compression_quality'),
				'default_license' => Configs::getValueAsString('default_license'),
				'delete_imported' => Configs::getValueAsBool('delete_imported'),
				'dropbox_key' => Configs::getValueAsString('dropbox_key'),
				'editor_enabled' => Configs::getValueAsBool('editor_enabled'),
				'force_32bit_ids' => Configs::getValueAsBool('force_32bit_ids'),
				'force_migration_in_production' => Configs::getValueAsBool('force_migration_in_production'),
				'has_exiftool' => Configs::getValueAsBool('has_exiftool'),
				'has_ffmpeg' => Configs::getValueAsBool('has_ffmpeg'),
				'hide_version_number' => Configs::getValueAsBool('hide_version_number'),
				'imagick' => Configs::getValueAsBool('imagick'),
				'import_via_symlink' => Configs::getValueAsBool('import_via_symlink'),
				'landing_background' => Configs::getValueAsString('landing_background'),
				'landing_subtitle' => Configs::getValueAsString('landing_subtitle'),
				'landing_title' => Configs::getValueAsString('landing_title'),
				'local_takestamp_video_formats' => Configs::getValueAsString('local_takestamp_video_formats'),
				'log_max_num_line' => Configs::getValueAsInt('log_max_num_line'),
				'lossless_optimization' => Configs::getValueAsBool('lossless_optimization'),
				'medium_2x' => Configs::getValueAsBool('medium_2x'),
				'medium_max_height' => Configs::getValueAsInt('medium_max_height'),
				'medium_max_width' => Configs::getValueAsInt('medium_max_width'),
				'prefer_available_xmp_metadata' => Configs::getValueAsBool('prefer_available_xmp_metadata'),
				'raw_formats' => Configs::getValueAsString('raw_formats'),
				'recent_age' => Configs::getValueAsInt('recent_age'),
				'skip_duplicates' => Configs::getValueAsBool('skip_duplicates'),
				'small_2x' => Configs::getValueAsBool('small_2x'),
				'small_max_height' => Configs::getValueAsInt('small_max_height'),
				'small_max_width' => Configs::getValueAsInt('small_max_width'),
				'thumb_2x' => Configs::getValueAsBool('thumb_2x'),
				'unlock_password_photos_with_url_param' => Configs::getValueAsBool('unlock_password_photos_with_url_param'),
			]),

			'album_subtitle_type' => Configs::getValueAsString('album_subtitle_type'),
			'check_for_updates' => Configs::getValueAsBool('check_for_updates'),
			'default_album_protection' => Configs::getValueAsString('default_album_protection'),
			'feeds' => [],
			'footer_additional_text' => Configs::getValueAsString('footer_additional_text'),
			'footer_show_copyright' => Configs::getValueAsBool('footer_show_copyright'),
			'footer_show_social_media' => Configs::getValueAsBool('footer_show_social_media'),
			'grants_download' => Configs::getValueAsBool('grants_download'),
			'grants_full_photo_access' => Configs::getValueAsBool('grants_full_photo_access'),
			'image_overlay_type' => Configs::getValueAsString('image_overlay_type'),
			'landing_page_enable' => Configs::getValueAsBool('landing_page_enable'),
			'lang' => Configs::getValueAsString('lang'),
			'layout' => Configs::getValueAsString('layout'),
			'legacy_id_redirection' => Configs::getValueAsBool('legacy_id_redirection'),
			'location_decoding' => Configs::getValueAsBool('location_decoding'),
			'location_decoding_timeout' => Configs::getValueAsInt('location_decoding_timeout'),
			'location_show' => Configs::getValueAsBool('location_show'),
			'location_show_public' => Configs::getValueAsBool('location_show_public'),
			'map_display' => Configs::getValueAsBool('map_display'),
			'map_display_direction' => Configs::getValueAsString('map_display_direction'),
			'map_display_public' => Configs::getValueAsBool('map_display_public'),
			'map_include_subalbums' => Configs::getValueAsBool('map_include_subalbums'),
			'map_provider' => Configs::getValueAsString('map_provider'),
			'mod_frame_enabled' => Configs::getValueAsBool('mod_frame_enabled'),
			'mod_frame_refresh' => Configs::getValueAsInt('mod_frame_refresh'),
			'new_photos_notification' => Configs::getValueAsBool('new_photos_notification'),
			'nsfw_banner_override' => Configs::getValueAsString('nsfw_banner_override'),
			'nsfw_blur' => Configs::getValueAsBool('nsfw_blur'),
			'nsfw_visible' => Configs::getValueAsBool('nsfw_visible'),
			'nsfw_warning' => Configs::getValueAsBool('nsfw_warning'),
			'nsfw_warning_admin' => Configs::getValueAsBool('nsfw_warning_admin'),
			'photos_wraparound' => Configs::getValueAsBool('photos_wraparound'),
			'public_photos_hidden' => Configs::getValueAsBool('public_photos_hidden'),
			'public_recent' => Configs::getValueAsBool('public_recent'),
			'public_search' => Configs::getValueAsBool('public_search'),
			'public_starred' => Configs::getValueAsBool('public_starred'),
			'rss_enable' => Configs::getValueAsBool('rss_enable'),
			'rss_max_items' => Configs::getValueAsInt('rss_max_items'),
			'rss_recent_days' => Configs::getValueAsInt('rss_recent_days'),
			'share_button_visible' => Configs::getValueAsBool('share_button_visible'),
			'site_copyright_begin' => Configs::getValueAsInt('site_copyright_begin'),
			'site_copyright_end' => Configs::getValueAsInt('site_copyright_end'),
			'site_owner' => Configs::getValueAsString('site_owner'),
			'site_title' => Configs::getValueAsString('site_title'),
			'sm_facebook_url' => Configs::getValueAsString('sm_facebook_url'),
			'sm_flickr_url' => Configs::getValueAsString('sm_flickr_url'),
			'sm_instagram_url' => Configs::getValueAsString('sm_instagram_url'),
			'sm_twitter_url' => Configs::getValueAsString('sm_twitter_url'),
			'sm_youtube_url' => Configs::getValueAsString('sm_youtube_url'),
			'sorting_albums' => AlbumSortingCriterion::createDefault(),
			'sorting_photos' => PhotoSortingCriterion::createDefault(),
			'swipe_tolerance_x' => Configs::getValueAsInt('swipe_tolerance_x'),
			'swipe_tolerance_y' => Configs::getValueAsInt('swipe_tolerance_y'),
			'update_check_every_days' => Configs::getValueAsInt('update_check_every_days'),
			'upload_processing_limit' => Configs::getValueAsInt('upload_processing_limit'),
			'zip64' => Configs::getValueAsBool('zip64'),
			'zip_deflate_level' => Configs::getValueAsInt('zip_deflate_level'),
		];
	}
}
