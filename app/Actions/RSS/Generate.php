<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\RSS;

use App\Constants\PhotoAlbum as PA;
use App\Contracts\Exceptions\InternalLycheeException;
use App\Enum\SizeVariantType;
use App\Exceptions\Internal\FrameworkException;
use App\Models\Extensions\UTCBasedTimes;
use App\Models\Photo;
use App\Policies\AlbumPolicy;
use App\Policies\AlbumQueryPolicy;
use App\Policies\PhotoQueryPolicy;
use App\Repositories\ConfigManager;
use App\Services\UrlGenerator;
use Carbon\Exceptions\InvalidFormatException;
use Carbon\Exceptions\UnitException;
use GrahamCampbell\Markdown\Facades\Markdown;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use function Safe\parse_url;
use Spatie\Feed\FeedItem;

/**
 * @template T of object{id:string,title:string,description:?string,type:string,created_at:string,updated_at:string,short_path:string,filesize:int,storage_disk:string,size_variant_type:string,username:string}
 */
class Generate
{
	use UTCBasedTimes;

	public function __construct(
		protected PhotoQueryPolicy $photo_query_policy,
		protected AlbumQueryPolicy $album_query_policy,
		protected readonly ConfigManager $config_manager,
		protected readonly UrlGenerator $url_generator,
	) {
	}

	/**
	 * @param T        $data       the row supplying the item's photo/user fields
	 * @param string   $album_id   the album whose view of the photo the item links to
	 * @param string[] $categories album titles to list on the item as `<category>`
	 *
	 * @return FeedItem
	 *
	 * @throws BindingResolutionException
	 */
	private function toFeedItem(object $data, string $album_id, array $categories): FeedItem
	{
		$page_link = route('gallery', ['albumId' => $album_id, 'photoId' => $data->id]);
		// A tag: URI (RFC 4151) is an opaque, album-independent identity for the
		// photo. Unlike $page_link (which points at the newest album and moves if
		// that album changes), it depends only on the host and the photo's
		// immutable id/created_at, so a photo keeps the same <guid> for life.
		$host = parse_url((string) config('app.url'), PHP_URL_HOST) ?? 'lychee';
		$guid = sprintf('tag:%s,%s:photo/%s', $host, Carbon::parse($data->created_at)->format('Y-m-d'), $data->id);
		$feed_item = [
			'id' => $guid,
			'title' => $data->title,
			'summary' => Markdown::convert($data->description ?? '')->getContent(),
			'updated' => $this->asDateTime($data->updated_at),
			'link' => $page_link,
			'enclosure' => $this->url_generator->pathToUrl($data->short_path, $data->storage_disk, SizeVariantType::ORIGINAL),
			'enclosureType' => $data->type,
			'enclosureLength' => $data->filesize,
			'authorName' => ($data->display_name !== null && $data->display_name !== '')
				? $data->display_name
				: $data->username,
			'category' => $categories,
		];

		return FeedItem::create($feed_item);
	}

	/**
	 * @return Collection<int,FeedItem>
	 *
	 * @throws InternalLycheeException
	 */
	public function do(): Collection
	{
		$user = Auth::user();
		$unlocked_album_ids = AlbumPolicy::getUnlockedAlbumIDs();

		$rss_recent = $this->config_manager->getValueAsInt('rss_recent_days');
		$rss_max = $this->config_manager->getValueAsInt('rss_max_items');
		try {
			$now_minus = Carbon::now()->subDays($rss_recent)->toDateTimeString();
		} catch (UnitException|InvalidFormatException $e) {
			throw new FrameworkException('Date/Time component (Carbon)', $e);
		}

		/** @var Collection<int,T> $photos */
		$photos = $this->photo_query_policy
			->applySearchabilityFilter(
				query: Photo::query(),
				user: $user,
				unlocked_album_ids: $unlocked_album_ids,
				origin: null,
				include_nsfw: !$this->config_manager->getValueAsBool('hide_nsfw_in_rss')
			)
			->join('size_variants', 'size_variants.photo_id', '=', 'photos.id')
			->join('users', 'users.id', '=', 'photos.owner_id')
			->where('size_variants.type', '=', SizeVariantType::ORIGINAL->value)
			// Require at least one album (needed for the item's link) without
			// re-introducing the per-album fan-out a join would add. The
			// base_albums join mirrors the categories query below, so every
			// selected photo is guaranteed a row there.
			->whereExists(fn (BaseBuilder $q) => $q
				->from(PA::PHOTO_ALBUM)
				->join('base_albums', 'base_albums.id', '=', PA::ALBUM_ID)
				->whereColumn(PA::PHOTO_ID, 'photos.id'))
			->select([
				'photos.id',
				'photos.title',
				'photos.description',
				'photos.type',
				'photos.created_at',
				'photos.updated_at',
				'size_variants.short_path',
				'size_variants.filesize',
				'size_variants.storage_disk',
				'users.username',
				'users.display_name',
			]
			)
			// distinct() collapses the photo_album fan-out that
			// applySearchabilityFilter's internal left-join introduces, so each
			// photo yields a single row (and the LIMIT counts photos).
			->distinct()
			->where('photos.created_at', '>=', $now_minus)
			->limit($rss_max)
			->orderBy('photos.created_at', 'desc')
			->toBase() // We use toBase() to avoid the use of the Eloquent casts etc.
			->get();

		// All album memberships of the selected photos, newest-first so first()
		// is the album each item links to: the most recently created album that
		// holds the photo (album_id breaks created_at ties for a stable choice).
		//
		// The same accessibility/NSFW constraints the photo query applies must
		// also be applied here: a photo can be shared into a locked or sensitive
		// album, and that album must not surface as the item's link or as a
		// <category> to a viewer who cannot reach it.
		$albums_query = DB::table(PA::PHOTO_ALBUM)
			->join('base_albums', 'base_albums.id', '=', PA::ALBUM_ID)
			->whereIn(PA::PHOTO_ID, $photos->pluck('id')->all());

		// Accessibility: keep only albums the current user (or a guest, when
		// $user is null) may access, honouring password locks. Admins may reach
		// every album, so — like the album policies themselves — they skip this.
		if ($user?->may_administrate !== true) {
			$this->album_query_policy->joinSubComputedAccessPermissions(
				query: $albums_query,
				second: PA::ALBUM_ID,
				type: 'left',
				user: $user,
			);
			$albums_query->where(fn (BaseBuilder $q) => $this->album_query_policy
				->appendAccessibilityConditions($q, $user, $unlocked_album_ids));
		}

		// NSFW: when the feed is configured to hide sensitive content, drop
		// albums that are marked sensitive or sit under a sensitive ancestor.
		// This mirrors the include_nsfw filter applied to the photo query above
		// (which is applied to admins too), so it is applied unconditionally.
		if ($this->config_manager->getValueAsBool('hide_nsfw_in_rss')) {
			$albums_query
				->join('albums', 'albums.id', '=', PA::ALBUM_ID)
				->whereNotExists(fn (BaseBuilder $q) => $this->album_query_policy
					->appendRecursiveSensitiveAlbumsCondition($q, null, null));
		}

		$albums_by_photo = $albums_query
			->orderBy('base_albums.created_at', 'desc')
			->orderBy(PA::ALBUM_ID)
			->get([
				PA::PHOTO_ID . ' as photo_id',
				PA::ALBUM_ID . ' as album_id',
				'base_albums.title as album_title',
			])
			->groupBy('photo_id');

		return $photos
			->map(function (object $photo) use ($albums_by_photo): ?FeedItem {
				/** @var Collection<int,object{album_id:string,album_title:string}>|null $albums */
				$albums = $albums_by_photo->get($photo->id);
				// The two queries are not a single snapshot: a concurrent request
				// can detach the photo from its last album between them, leaving
				// no album to link to. Drop the photo rather than fatal on null.
				if ($albums === null) {
					return null;
				}

				// The computed-access-permissions join can return an album more
				// than once (e.g. shared to several of the user's groups), so
				// collapse to one row per album before listing categories. first()
				// still yields the newest album for the link (order is preserved).
				$albums = $albums->unique('album_id');

				return $this->toFeedItem(
					$photo,
					$albums->first()->album_id,
					$albums->pluck('album_title')->all(),
				);
			})
			->filter()
			->values();
	}
}
