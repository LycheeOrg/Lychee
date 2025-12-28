<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Albums;

use App\Constants\PhotoAlbum as PA;
use App\Enum\FlowStrategy;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\InvalidAlbumException;
use App\Exceptions\UnexpectedException;
use App\Factories\AlbumFactory;
use App\Models\Album;
use App\Models\Builders\AlbumBuilder;
use App\Policies\AlbumQueryPolicy;
use App\Repositories\ConfigManager;
use Illuminate\Support\Facades\DB;

final class Flow
{
	public function __construct(
		protected AlbumQueryPolicy $album_query_policy,
		protected AlbumFactory $album_factory,
		protected readonly ConfigManager $config_manager,
	) {
	}

	/**
	 * Returns a query builder for albums with published_at not null, ordered by published_at desc.
	 *
	 * @return AlbumBuilder
	 */
	public function do(): AlbumBuilder
	{
		$flow_base = $this->config_manager->getValueAsString('flow_base');
		$flow_base = $flow_base === '' ? null : $flow_base;

		/** @var Album|null $base */
		$base = $this->getBase($flow_base);
		$base_query = $this->getQuery($base, true);

		$hide_nsfw = $this->config_manager->getValueAsBool('hide_nsfw_in_flow');
		if ($hide_nsfw) {
			$base_query->whereNotExists(fn ($q) => $this->album_query_policy->appendRecursiveSensitiveAlbumsCondition($q, $base?->_lft, $base?->_rgt));
		} else {
			// In this specific case we want to know if an album is recursive NSFW.
			// This will be used to determine if the album should be blurred.
			$base_query->addVirtualIsRecursiveNSFW();

			// Due to the way the virtual columns are added in AlbumBuilder::getModel(), we need to add them here.
			$base_query->addVirtualMinTakenAt();
			$base_query->addVirtualMaxTakenAt();
			$base_query->addVirtualNumChildren();
			$base_query->addVirtualNumPhotos();
		}

		// Apply the security policy to the query.
		$include_sub_albums = $this->config_manager->getValueAsBool('flow_include_sub_albums');
		if ($include_sub_albums) {
			// Now we restrict the query to only the browsable albums.
			$query = $this->album_query_policy->applyBrowsabilityFilter($base_query, $base?->_lft, $base?->_rgt);
		} else {
			// We could also use browsable filter here, but reachability filter is faster.
			$query = $this->album_query_policy->applyReachabilityFilter($base_query);
		}

		$flow_strategy = $this->config_manager->getValueAsEnum('flow_strategy', FlowStrategy::class);
		$order_by = match ($flow_strategy) {
			FlowStrategy::AUTO => 'pc_base_album.created_at',
			FlowStrategy::OPT_IN => 'pc_base_album.published_at',
		};
		$query = $query->orderByDesc($order_by);

		return $query;
	}

	private function getBase(string|null $flow_base): Album|null
	{
		if ($flow_base === null || $flow_base === '') {
			return null;
		}

		$base = $this->album_factory->findNullalbleAbstractAlbumOrFail($flow_base, false);
		if ($base !== null && !$base instanceof Album) {
			// @codeCoverageIgnoreStart
			throw new InvalidAlbumException('Flow base must be null or an Album, got: ' . get_class($base));
			// @codeCoverageIgnoreEnd
		}

		return $base;
	}

	/**
	 * Create the query for the flow.
	 *
	 * @param Album|null $base
	 * @param bool       $with_relations
	 *
	 * @return AlbumBuilder
	 *
	 * @throws ConfigurationKeyMissingException
	 * @throws UnexpectedException
	 */
	private function getQuery(Album|null $base, bool $with_relations): AlbumBuilder
	{
		$include_sub_albums = $this->config_manager->getValueAsBool('flow_include_sub_albums');
		$includes_photos_children = $this->config_manager->getValueAsBool('flow_include_photos_from_children');
		$flow_strategy = $this->config_manager->getValueAsEnum('flow_strategy', FlowStrategy::class);

		$base_query = Album::query();
		if ($with_relations) {
			$base_query->with(['cover', 'cover.size_variants', 'statistics', 'photos', 'photos.statistics', 'photos.size_variants', 'photos.palette', 'photos.tags', 'photos.rating']);
		}

		// Only join what we need for ordering.
		$base_query->joinSub(DB::table('base_albums')->select(['id', 'created_at', 'published_at']), 'pc_base_album', 'pc_base_album.id', '=', 'albums.id', 'left');

		$base_query
			// Only exclude the albums without photos if we do not want photos from children.
			->when(!$includes_photos_children, fn ($q) => $q->whereExists(fn ($q) => $q->select(DB::raw(1))->from(PA::PHOTO_ALBUM)->whereColumn(PA::ALBUM_ID, '=', 'albums.id')))
			->when($base === null && $include_sub_albums === false, fn ($q) => $q->whereIsRoot())
			->when($base !== null && $include_sub_albums === false, fn ($q) => $q->where('parent_id', '=', $base->id))
			->when($base !== null && $include_sub_albums === true, fn ($q) => $q->where('_lft', '>', $base->_lft)->where('_rgt', '<', $base->_rgt))
			// The condition base === null + sub albums means that there are no restrictions AT ALL.
			// This is why it is not included in the query.
			->when($flow_strategy === FlowStrategy::OPT_IN, fn ($q) => $q->whereNotNull('pc_base_album.published_at'));

		return $base_query;
	}
}
