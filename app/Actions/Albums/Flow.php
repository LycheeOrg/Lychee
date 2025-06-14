<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Albums;

use App\Constants\PhotoAlbum as PA;
use App\Enum\FlowStrategy;
use App\Exceptions\InvalidAlbumException;
use App\Factories\AlbumFactory;
use App\Models\Album;
use App\Models\Configs;
use App\Models\TagAlbum;
use App\Policies\AlbumQueryPolicy;
use App\SmartAlbums\BaseSmartAlbum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class Flow
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
		$flow_base = Configs::getValueAsString('flow_base');
		$flow_base = $flow_base === '' ? null : $flow_base;
		/** @var Album|TagAlbum|BaseSmartAlbum|null $base */
		$base = $this->album_factory->findNullalbleAbstractAlbumOrFail($flow_base, false);

		if ($base !== null && !$base instanceof Album) {
			throw new InvalidAlbumException('Flow base must be an Album, got: ' . get_class($base));
		}

		$include_sub_albums = Configs::getValueAsBool('flow_include_sub_albums');
		$includes_photos_children = Configs::getValueAsBool('flow_include_photos_from_children');
		$flow_strategy = Configs::getValueAsEnum('flow_strategy', FlowStrategy::class);
		$order_by = match ($flow_strategy) {
			FlowStrategy::AUTO => 'base_albums.created_at',
			FlowStrategy::OPT_IN => 'base_albums.published_at',
		};

		$hide_nsfw = Configs::getValueAsBool('hide_nsfw_in_flow');

		$base_query = Album::query()
			->with(['cover', 'cover.size_variants', 'statistics', 'photos', 'photos.statistics', 'photos.size_variants', 'photos.palette'])
			// Only exclude the albums without photos if we do not want photos from children.
			->when(!$includes_photos_children, fn ($q) => $q->whereExists(fn ($q) => $q->select(DB::raw(1))->from(PA::PHOTO_ALBUM)->whereColumn(PA::ALBUM_ID, '=', 'albums.id')))
			->when($base === null && $include_sub_albums === false, fn ($q) => $q->whereIsRoot())
			->when($base !== null && $include_sub_albums === false, fn ($q) => $q->where('parent_id', $base->id))
			->when($base !== null && $include_sub_albums === true, fn ($q) => $q->where('_lft', '>', $base->_lft)->where('_rgt', '<', $base->_rgt))
			// The condition base === null + sub albums means that there are no restrictions AT ALL.
			// This is why it is not included in the query.
			->when($flow_strategy === FlowStrategy::OPT_IN, fn ($q) =>
				// Do we need an index album_id_published_at?
				$q->joinSub(DB::table('base_albums')->whereNotNull('published_at'), 'published_base_album', 'published_base_album.id', '=', 'albums.id')
			);

		// there must be no unreachable album between the origin and the photo
		if ($hide_nsfw) {
			$base_query->whereNotExists(fn ($q) => $this->album_query_policy->appendRecursiveSensitiveAlbumsCondition($q, $base?->_lft, $base?->_rgt));
		}

		// Apply the security policy to the query.
		if ($include_sub_albums) {
			// Now we restrict the query to only the browsable albums.
			$query = $this->album_query_policy->applyBrowsabilityFilter($base_query, $base?->_lft, $base?->_rgt);
		} else {
			$query = $this->album_query_policy->applyReachabilityFilter($base_query);
		}
		$query = $query->orderByDesc($order_by);

		return $query;
	}
}
