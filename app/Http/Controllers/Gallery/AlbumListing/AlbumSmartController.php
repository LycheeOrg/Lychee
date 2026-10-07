<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery\AlbumListing;

use App\Factories\AlbumFactory;
use App\Http\Requests\Album\GetAlbumCategoryRequest;
use App\Http\Resources\V3\AlbumCategoryResource;
use App\Models\AlbumUserThumb;
use App\Policies\AlbumPolicy;
use App\SmartAlbums\BaseSmartAlbum;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
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
	 * request; so is a row {@link BaseSmartAlbum::isCachedThumbValid()}
	 * rejects (a date-dependent album, `on_this_day` or `recent`, whose cached
	 * cover has dropped out). With every cover cached, the only `photos`
	 * queries are those two albums' validity checks.
	 */
	public function smart(GetAlbumCategoryRequest $request): AlbumCategoryResource
	{
		/** @var Collection<int,BaseSmartAlbum> $smart_albums */
		$smart_albums = $this->album_factory
			->getAllBuiltInSmartAlbums(false)
			->filter(fn (BaseSmartAlbum $smart_album) => Gate::check(AlbumPolicy::CAN_SEE, $smart_album))
			->values();

		$ids = $smart_albums->map(fn (BaseSmartAlbum $smart_album) => $smart_album->get_id())->all();

		/** @var array<string,string> $cached_covers album_id => photo_id */
		$cached_covers = AlbumUserThumb::query()
			->whereIn('album_id', $ids)
			->where('user_id', '=', Auth::id())
			->pluck('photo_id', 'album_id')
			->all();

		$titles = [];
		$cover_ids = [];
		$owner_ids = [];
		foreach ($smart_albums as $smart_album) {
			$titles[] = $smart_album->get_title();
			$cached_cover = $cached_covers[$smart_album->get_id()] ?? null;
			$cover_ids[] = $cached_cover !== null && $smart_album->isCachedThumbValid($cached_cover)
				? $cached_cover
				: $smart_album->get_thumb()?->id;
			// Smart albums are built-in/system-wide — no real owner.
			$owner_ids[] = '0';
		}

		return new AlbumCategoryResource(ids: $ids, titles: $titles, cover_ids: $cover_ids, owner_ids: $owner_ids);
	}
}
