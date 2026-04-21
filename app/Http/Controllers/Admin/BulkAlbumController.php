<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\BulkEditAlbumsAction;
use App\Actions\Album\Delete;
use App\Actions\Album\Transfer;
use App\Actions\Search\Strategies\Album\AlbumFieldLikeStrategy;
use App\DTO\Search\SearchToken;
use App\Enum\ColumnSortingAlbumType;
use App\Enum\ColumnSortingPhotoType;
use App\Enum\LicenseType;
use App\Enum\OrderSortingType;
use App\Http\Requests\Admin\BulkAlbumEdit\DeleteBulkAlbumRequest;
use App\Http\Requests\Admin\BulkAlbumEdit\IdsBulkAlbumRequest;
use App\Http\Requests\Admin\BulkAlbumEdit\IndexBulkAlbumRequest;
use App\Http\Requests\Admin\BulkAlbumEdit\PatchBulkAlbumRequest;
use App\Http\Requests\Admin\BulkAlbumEdit\SetOwnerBulkAlbumRequest;
use App\Http\Resources\Admin\BulkAlbumIdsResource;
use App\Http\Resources\Admin\BulkAlbumResource;
use App\Http\Resources\Admin\PaginatedBulkAlbumResource;
use App\Models\Album;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

/**
 * Admin controller for the Bulk Album Edit feature.
 *
 * All endpoints require an authenticated administrator.
 */
class BulkAlbumController extends Controller
{
	/**
	 * List albums paginated, filtered, ordered by _lft.
	 *
	 * Joins base_albums + albums + users + access_permissions (public record, user_id IS NULL and user_group_id IS NULL).
	 * Returns BulkAlbumResource rows with _lft / _rgt so the Vue frontend
	 * can compute indentation depth in a single O(n) pass.
	 */
	public function index(IndexBulkAlbumRequest $request): PaginatedBulkAlbumResource
	{
		$search = $request->validated('search');
		$per_page = (int) $request->validated('per_page', 50);
		$page = (int) $request->validated('page', 1);

		$query = Album::query()->without(
			['cover', 'cover.size_variants', 'min_privilege_cover', 'min_privilege_cover.size_variants', 'max_privilege_cover', 'max_privilege_cover.size_variants', 'thumb']
		)->orderBy('albums._lft', 'asc');

		if ($search !== null && $search !== '') {
			$strategy = new AlbumFieldLikeStrategy('title');
			$strategy->apply($query, new SearchToken(null, null, null, value: $search, is_prefix: false));
		}

		/** @var \Illuminate\Pagination\LengthAwarePaginator<int,Album> $paginated */
		$paginated = $query->paginate(perPage: $per_page, page: $page);

		$resource_collection = $paginated->getCollection()->map(fn ($row) => $this->rowToResource($row));
		$mapped = new \Illuminate\Pagination\LengthAwarePaginator(
			$resource_collection,
			$paginated->total(),
			$paginated->perPage(),
			$paginated->currentPage(),
		);

		return new PaginatedBulkAlbumResource($mapped);
	}

	/**
	 * Return up to 1 000 album IDs matching the optional search filter.
	 *
	 * When there are more than 1 000 matches, capped is true and the list is truncated.
	 */
	public function ids(IdsBulkAlbumRequest $request): BulkAlbumIdsResource
	{
		$search = $request->validated('search');

		$query = DB::table('albums')
			->join('base_albums', 'base_albums.id', '=', 'albums.id')
			->orderBy('albums._lft', 'asc')
			->limit(1001)
			->select('albums.id');

		if ($search !== null && $search !== '') {
			$strategy = new AlbumFieldLikeStrategy('title');
			$strategy->apply($query, new SearchToken(null, null, null, value: $search, is_prefix: false));
		}

		$results = $query->pluck('id')->all();

		$capped = count($results) > 1000;
		if ($capped) {
			$results = array_slice($results, 0, 1000);
		}

		return new BulkAlbumIdsResource($results, $capped);
	}

	/**
	 * Partially update metadata and/or visibility fields on a set of albums.
	 *
	 * Only the fields present in the request body are updated.
	 * At least one optional field must be provided (enforced by the Request class).
	 */
	public function patch(PatchBulkAlbumRequest $request): void
	{
		$data = $request->bulkAlbumPatchData();

		DB::transaction(function () use ($data): void {
			(new BulkEditAlbumsAction())->do($data);
		});
	}

	/**
	 * Transfer ownership of the specified albums to a different user.
	 *
	 * Albums are moved to root level and their descendants' ownership is updated.
	 */
	public function setOwner(SetOwnerBulkAlbumRequest $request): void
	{
		$validated = $request->validated();
		$album_ids = $validated['album_ids'];
		$owner_id = (int) $validated['owner_id'];

		$transfer = new Transfer();

		DB::transaction(function () use ($album_ids, $owner_id, $transfer): void {
			/** @var Album[] $albums */
			$albums = Album::query()->whereIn('id', $album_ids)->get()->all();
			foreach ($albums as $album) {
				$transfer->do($album, $owner_id);
			}
		});
	}

	/**
	 * Permanently delete the specified albums and all their sub-albums and photos.
	 */
	public function destroy(DeleteBulkAlbumRequest $request): void
	{
		$album_ids = $request->validated('album_ids');
		(new Delete())->do($album_ids);
	}

	// ── Private helpers ───────────────────────────────────────────────────────

	/**
	 * Map a raw DB row object to a BulkAlbumResource.
	 *
	 * @param Album $row
	 */
	private function rowToResource(Album $row): BulkAlbumResource
	{
		return new BulkAlbumResource(
			id: $row->id,
			title: $row->title,
			owner_id: $row->owner_id,
			owner_name: $row->owner->name,
			description: $row->description,
			copyright: $row->copyright,
			license: $row->license ?? LicenseType::NONE,
			photo_layout: $row->photo_layout,
			photo_sorting_col: ColumnSortingPhotoType::tryFrom($row->sorting_col),
			photo_sorting_order: OrderSortingType::tryFrom($row->sorting_order),
			album_sorting_col: ColumnSortingAlbumType::tryFrom($row->album_sorting_col),
			album_sorting_order: OrderSortingType::tryFrom($row->album_sorting_order),
			album_thumb_aspect_ratio: $row->album_thumb_aspect_ratio,
			album_timeline: $row->album_timeline,
			photo_timeline: $row->photo_timeline,
			is_nsfw: $row->is_nsfw,
			_lft: $row->_lft,
			_rgt: $row->_rgt,
			is_public: $row->public_permissions() !== null,
			is_link_required: $row->public_permissions()?->is_link_required === true,
			grants_full_photo_access: $row->public_permissions()?->grants_full_photo_access === true,
			grants_download: $row->public_permissions()?->grants_download === true,
			grants_upload: $row->public_permissions()?->grants_upload === true,
			created_at: $row->created_at,
		);
	}
}
