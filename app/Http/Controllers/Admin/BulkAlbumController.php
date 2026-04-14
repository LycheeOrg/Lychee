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
use App\Enum\AspectRatioType;
use App\Enum\ColumnSortingAlbumType;
use App\Enum\ColumnSortingPhotoType;
use App\Enum\LicenseType;
use App\Enum\OrderSortingType;
use App\Enum\PhotoLayoutType;
use App\Enum\TimelineAlbumGranularity;
use App\Enum\TimelinePhotoGranularity;
use App\Http\Requests\Admin\BulkAlbumEdit\DeleteBulkAlbumRequest;
use App\Http\Requests\Admin\BulkAlbumEdit\IdsBulkAlbumRequest;
use App\Http\Requests\Admin\BulkAlbumEdit\IndexBulkAlbumRequest;
use App\Http\Requests\Admin\BulkAlbumEdit\PatchBulkAlbumRequest;
use App\Http\Requests\Admin\BulkAlbumEdit\SetOwnerBulkAlbumRequest;
use App\Http\Resources\Admin\BulkAlbumIdsResource;
use App\Http\Resources\Admin\BulkAlbumResource;
use App\Http\Resources\Admin\PaginatedBulkAlbumResource;
use App\Models\Album;
use Carbon\Carbon;
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

		$query = DB::table('albums')
			->join('base_albums', 'base_albums.id', '=', 'albums.id')
			->join('users', 'users.id', '=', 'base_albums.owner_id')
			->leftJoin('access_permissions as ap', function ($join): void {
				$join->on('ap.base_album_id', '=', 'base_albums.id')
					->whereNull('ap.user_id')
					->whereNull('ap.user_group_id');
			})
			->select([
				'albums.id',
				'base_albums.title',
				'base_albums.owner_id',
				DB::raw('COALESCE(users.display_name, users.username) as owner_name'),
				'base_albums.description',
				'base_albums.copyright',
				'albums.license',
				'base_albums.photo_layout',
				'base_albums.sorting_col as photo_sorting_col',
				'base_albums.sorting_order as photo_sorting_order',
				'albums.album_sorting_col',
				'albums.album_sorting_order',
				'albums.album_thumb_aspect_ratio',
				'albums.album_timeline',
				'base_albums.photo_timeline',
				'base_albums.is_nsfw',
				'albums._lft',
				'albums._rgt',
				'base_albums.created_at',
				// Visibility from public access_permissions row
				DB::raw('CASE WHEN ap.id IS NOT NULL THEN 1 ELSE 0 END as is_public'),
				DB::raw('COALESCE(ap.is_link_required, 0) as is_link_required'),
				DB::raw('COALESCE(ap.grants_full_photo_access, 0) as grants_full_photo_access'),
				DB::raw('COALESCE(ap.grants_download, 0) as grants_download'),
				DB::raw('COALESCE(ap.grants_upload, 0) as grants_upload'),
			])
			->orderBy('albums._lft', 'asc');

		if ($search !== null && $search !== '') {
			$query->where('base_albums.title', 'like', '%' . $search . '%');
		}

		/** @var \Illuminate\Pagination\LengthAwarePaginator<int,object> $paginated */
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
			$query->where('base_albums.title', 'like', '%' . $search . '%');
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
		$validated = $request->validated();
		$album_ids = $validated['album_ids'];

		// Build the payload with only the fields that were actually sent.
		$optional_fields = [
			'description', 'copyright', 'photo_layout',
			'photo_sorting_col', 'photo_sorting_order',
			'album_sorting_col', 'album_sorting_order',
			'album_thumb_aspect_ratio', 'album_timeline', 'photo_timeline',
			'is_nsfw', 'is_public', 'is_link_required',
			'grants_full_photo_access', 'grants_download', 'grants_upload',
		];

		$payload = [];
		foreach ($optional_fields as $field) {
			if ($request->has($field)) {
				$payload[$field] = $validated[$field] ?? null;
			}
		}

		// Handle license separately (may be null to clear)
		if ($request->has('license')) {
			$license_raw = $validated['license'] ?? null;
			$payload['license'] = $license_raw !== null
				? LicenseType::from($license_raw instanceof LicenseType ? $license_raw->value : $license_raw)
				: null;
		}

		DB::transaction(function () use ($album_ids, $payload): void {
			(new BulkEditAlbumsAction())->do($album_ids, $payload);
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
	 * @param object $row
	 */
	private function rowToResource(object $row): BulkAlbumResource
	{
		return new BulkAlbumResource(
			id: $row->id,
			title: $row->title,
			owner_id: (int) $row->owner_id,
			owner_name: $row->owner_name,
			description: $row->description,
			copyright: $row->copyright,
			license: LicenseType::tryFrom($row->license ?? '') ?? LicenseType::NONE,
			photo_layout: PhotoLayoutType::tryFrom($row->photo_layout ?? ''),
			photo_sorting_col: ColumnSortingPhotoType::tryFrom($row->photo_sorting_col ?? ''),
			photo_sorting_order: OrderSortingType::tryFrom($row->photo_sorting_order ?? ''),
			album_sorting_col: ColumnSortingAlbumType::tryFrom($row->album_sorting_col ?? ''),
			album_sorting_order: OrderSortingType::tryFrom($row->album_sorting_order ?? ''),
			album_thumb_aspect_ratio: AspectRatioType::tryFrom($row->album_thumb_aspect_ratio ?? ''),
			album_timeline: TimelineAlbumGranularity::tryFrom($row->album_timeline ?? ''),
			photo_timeline: TimelinePhotoGranularity::tryFrom($row->photo_timeline ?? ''),
			is_nsfw: boolval($row->is_nsfw),
			_lft: (int) $row->_lft,
			_rgt: (int) $row->_rgt,
			is_public: boolval($row->is_public),
			is_link_required: boolval($row->is_link_required),
			grants_full_photo_access: boolval($row->grants_full_photo_access),
			grants_download: boolval($row->grants_download),
			grants_upload: boolval($row->grants_upload),
			created_at: Carbon::parse($row->created_at),
		);
	}
}
