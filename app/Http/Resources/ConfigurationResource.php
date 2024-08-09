<?php

namespace App\Http\Resources;

use App\DTO\AlbumSortingCriterion;
use App\DTO\PhotoSortingCriterion;
use App\Enum\AlbumDecorationOrientation;
use App\Enum\AlbumDecorationType;
use App\Enum\DefaultAlbumProtectionType;
use App\Enum\ImageOverlayType;
use App\Enum\MapProviders;
use App\Enum\PhotoLayoutType;
use App\Enum\ThumbAlbumSubtitleType;
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
		// Laravel applies a shortcut when this value === null but not when it is something else.
		parent::__construct('must_not_be_null');
	}

	/**
	 * Transform the resource into an array.
	 *
	 * @param \Illuminate\Http\Request $request
	 *
	 * @return array<string,mixed>
	 */
	public function toArray($request): array
	{
		$lycheeVersion = resolve(InstalledVersion::class);
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
			'version' => $this->when(Auth::check() || !Configs::getValueAsBool('hide_version_number'), $lycheeVersion->getVersion()),
			'rss_feeds' => $rss_feeds,
			'album_decoration' => Configs::getValueAsEnum('album_decoration', AlbumDecorationType::class),
			'album_decoration_orientation' => Configs::getValueAsEnum('album_decoration_orientation', AlbumDecorationOrientation::class),
			'album_subtitle_type' => Configs::getValueAsEnum('album_subtitle_type', ThumbAlbumSubtitleType::class),
			'check_for_updates' => Configs::getValueAsBool('check_for_updates'),
			'default_album_protection' => Configs::getValueAsEnum('default_album_protection', DefaultAlbumProtectionType::class),
			'feeds' => [],
			'footer_additional_text' => Configs::getValueAsString('footer_additional_text'),
			'footer_show_copyright' => Configs::getValueAsBool('footer_show_copyright'),
			'footer_show_social_media' => Configs::getValueAsBool('footer_show_social_media'),
			'grants_download' => Configs::getValueAsBool('grants_download'),
			'grants_full_photo_access' => Configs::getValueAsBool('grants_full_photo_access'),
			'image_overlay_type' => Configs::getValueAsEnum('image_overlay_type', ImageOverlayType::class),
			'landing_page_enable' => Configs::getValueAsBool('landing_page_enable'),
			'landing_background' => Configs::getValueAsString('landing_background'),
			'landing_subtitle' => Configs::getValueAsString('landing_subtitle'),
			'landing_title' => Configs::getValueAsString('landing_title'),
			'lang' => Configs::getValueAsString('lang'),
			'layout' => Configs::getValueAsEnum('layout', PhotoLayoutType::class),
			'legacy_id_redirection' => Configs::getValueAsBool('legacy_id_redirection'),
			'location_decoding' => Configs::getValueAsBool('location_decoding'),
			'location_decoding_timeout' => Configs::getValueAsInt('location_decoding_timeout'),
			'location_show' => Configs::getValueAsBool('location_show'),
			'location_show_public' => Configs::getValueAsBool('location_show_public'),
			'map_display' => Configs::getValueAsBool('map_display'),
			'map_display_direction' => Configs::getValueAsString('map_display_direction'),
			'map_display_public' => Configs::getValueAsBool('map_display_public'),
			'map_include_subalbums' => Configs::getValueAsBool('map_include_subalbums'),
			'map_provider' => Configs::getValueAsEnum('map_provider', MapProviders::class),
			'mod_frame_enabled' => Configs::getValueAsBool('mod_frame_enabled'),
			'mod_frame_refresh' => Configs::getValueAsInt('mod_frame_refresh'),
			'new_photos_notification' => Configs::getValueAsBool('new_photos_notification'),
			'nsfw_banner_override' => Configs::getValueAsString('nsfw_banner_override'),
			'nsfw_blur' => Configs::getValueAsBool('nsfw_blur'),
			'nsfw_visible' => Configs::getValueAsBool('nsfw_visible'),
			'nsfw_warning' => Configs::getValueAsBool('nsfw_warning'),
			'nsfw_warning_admin' => Configs::getValueAsBool('nsfw_warning_admin'),
			'photos_wraparound' => Configs::getValueAsBool('photos_wraparound'),
			'public_search' => Configs::getValueAsBool('search_public'), // legacy
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
