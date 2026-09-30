<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery\AlbumListing;

use App\Actions\Album\StructOfArrays\SideCoverIds;
use App\Factories\AlbumFactory;
use App\Http\Requests\Album\GetAlbumCategoryRequest;
use App\Http\Resources\V3\AlbumCategoryResource;
use App\Models\AlbumUserThumb;
use App\Policies\AlbumPolicy;
use App\Repositories\ConfigManager;
use App\SmartAlbums\BaseSmartAlbum;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

/**
 * Serves the flat, un-bucketed, un-scoped smart-album listing:
 * `GET /Albums/smart` — always one flat, ungrouped list, never per-owner
 * buckets, never a `/buckets` route (NG9).
 */
class AlbumSmartController extends Controller
{
	public function __construct(
		protected AlbumFactory $album_factory,
	) {
	}

	/**
	 * Reuses the existing cheap, in-memory, `Gate`-filtered
	 * `AlbumFactory::getAllBuiltInSmartAlbums(false)` list. Covers come from
	 * one batched, indexed lookup against the viewer's `album_user_thumbs`
	 * rows ({@link \App\Models\Extensions\CachesAlbumUserThumb}); a smart
	 * album without a row is resolved live through
	 * {@link BaseSmartAlbum::get_thumb()}, which seeds the row for the next
	 * request. With every cover cached, no `photos` query runs.
	 */
	public function smart(GetAlbumCategoryRequest $request): AlbumCategoryResource
	{
		/** @var Collection<int,BaseSmartAlbum> $smart_albums */
		$smart_albums = $this->album_factory
			->getAllBuiltInSmartAlbums(false)
			->filter(fn (BaseSmartAlbum $smart_album) => Gate::check(AlbumPolicy::CAN_SEE, $smart_album))
			->values();

		$ids = $smart_albums->map(fn (BaseSmartAlbum $smart_album) => $smart_album->get_id())->all();

		$cache_rows = AlbumUserThumb::rowsForViewer($ids);
		$side_covers_enabled = resolve(ConfigManager::class)->getValueAsBool('album_hover_side_covers_enabled');

		$titles = [];
		$cover_ids = [];
		$cover_ids_2 = [];
		$cover_ids_3 = [];
		$owner_ids = [];
		foreach ($smart_albums as $smart_album) {
			$titles[] = $smart_album->get_title();
			$cache_row = $cache_rows->get($smart_album->get_id());
			$cover_id = $cache_row?->photo_id ?? $smart_album->get_thumb()?->id;
			// Side covers (Feature 075, FR-075-09) come from the cache row
			// only; a cache miss seeds all three ranks for the next request.
			[$cover_id_2, $cover_id_3] = SideCoverIds::fromCacheRow($cache_row, $cover_id, $side_covers_enabled);
			$cover_ids[] = $cover_id;
			$cover_ids_2[] = $cover_id_2;
			$cover_ids_3[] = $cover_id_3;
			// Smart albums are built-in/system-wide — no real owner.
			$owner_ids[] = '0';
		}

		return new AlbumCategoryResource(ids: $ids, titles: $titles, cover_ids: $cover_ids, cover_ids_2: $cover_ids_2, cover_ids_3: $cover_ids_3, owner_ids: $owner_ids);
	}
}
