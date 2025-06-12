<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Albums;

use App\Constants\PhotoAlbum as PA;
use App\Enum\FeedStrategy;
use App\Exceptions\InvalidAlbumException;
use App\Factories\AlbumFactory;
use App\Models\Album;
use App\Models\Configs;
use App\Models\TagAlbum;
use App\Policies\AlbumQueryPolicy;
use App\SmartAlbums\BaseSmartAlbum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class Feed
{
	public function __construct(
		protected AlbumQueryPolicy $album_query_policy,
		protected AlbumFactory $album_factory,
	) {
	}

	/**
	 * Returns a query builder for albums with published_at not null, ordered by published_at desc.
	 *
	 * @return Builder
	 */
	public function do(): Builder
	{
		$feed_base = Configs::getValueAsString('feed_base');
		$feed_base = $feed_base === '' ? null : $feed_base;
		/** @var Album|TagAlbum|BaseSmartAlbum|null $base */
		$base = $this->album_factory->findNullalbleAbstractAlbumOrFail($feed_base, false);

		if ($base !== null && !$base instanceof Album) {
			throw new InvalidAlbumException('Feed base must be an Album, got: ' . get_class($base));
		}

		$include_sub_albums = Configs::getValueAsBool('feed_include_sub_albums');
		$feed_strategy = Configs::getValueAsEnum('feed_strategy', FeedStrategy::class);
		$order_by = match ($feed_strategy) {
			FeedStrategy::AUTO => 'base_albums.created_at',
			FeedStrategy::OPT_IN => 'base_albums.published_at',
		};

		$hide_nsfw = Configs::getValueAsBool('hide_nsfw_in_feed');

		$base_query = Album::query()
			->with(['statistics', 'photos', 'photos.statistics', 'photos.size_variants', 'photos.palette'])
			->join('base_albums', 'base_albums.id', '=', 'albums.id')
			->whereExists(fn ($q) => $q->select(DB::raw(1))->from(PA::PHOTO_ALBUM)->whereColumn(PA::ALBUM_ID, '=', 'albums.id'))
			->when($base === null && $include_sub_albums === false, fn ($q) => $q->whereIsRoot())
			->when($base !== null && $include_sub_albums === false, fn ($q) => $q->where('parent_id', $base->id))
			->when($base !== null && $include_sub_albums === true, fn ($q) => $q->where('_lft', '>', $base->_lft)->where('_rgt', '<', $base->_rgt))
			// The condition base === null + sub albums means that there are no restrictions AT ALL.
			// This is why it is not included in the query.
			->when($feed_strategy === FeedStrategy::OPT_IN, fn ($q) => $q->whereNotNull('base_albums.published_at')); // Do we need an index album_id_published_at?

		// there must be no unreachable album between the origin and the photo
		if ($hide_nsfw) {
			$base_query->whereNotExists(fn ($q) => $this->album_query_policy->appendRecursiveSensitiveAlbumsCondition($q, $base?->_lft, $base?->_rgt));
		}
		$base_query = $base_query->orderByDesc($order_by);

		return $base_query;
	}
}
