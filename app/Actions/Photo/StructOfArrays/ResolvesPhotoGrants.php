<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo\StructOfArrays;

use App\Assets\DbBool;
use App\Constants\PhotoAlbum as PA;
use App\DTO\PhotoGrants;
use App\Models\User;
use App\Policies\AlbumQueryPolicy;
use Illuminate\Support\Facades\DB;

/**
 * Resolves `grants_full_photo_access`/`grants_download` for a set of photos in
 * **one** query, replacing a per-photo policy evaluation that was an N+1.
 *
 * `PhotoPolicy::canAccessFullPhoto()` reduces over `$photo->albums`, and each
 * album's `public_permissions()`/`current_user_permissions()` reads that album's
 * `access_permissions` relation. Eager-loading `albums` (as the `details` tier
 * did) does not eager-load *their* permissions, so every (photo, album) pair
 * cost a `select * from access_permissions where base_album_id in (?)` — measured
 * at 6 such queries for 4 photos before this change.
 *
 * The same answer is available in SQL via the `computed_access_permissions`
 * sub-query the album policies already use: join it to `photo_album`, then
 * `GROUP BY photo_id` + `MAX()` to OR the grants across every album a photo
 * belongs to. That reproduces `PhotoPolicy`'s own `reduction()` semantics
 * exactly — "granted by any containing album is enough" — as one grouped query
 * regardless of photo count, and lets the caller drop the `albums` eager load
 * entirely.
 *
 * Two parts of `PhotoPolicy::canAccessFullPhoto()` are deliberately **not**
 * reproduced here, because the caller already guarantees them:
 *  - the `isOwner()` short-circuit, which the caller applies from the
 *    `photos.owner_id` it has already selected (no extra query);
 *  - the `canSee()` gate, which is implied — every row reaching this point
 *    survived the tier's own visibility-filtered candidate query.
 */
class ResolvesPhotoGrants
{
	public function __construct(
		private readonly AlbumQueryPolicy $album_query_policy,
	) {
	}

	/**
	 * Full `PhotoPolicy::canAccessFullPhoto()` answer for a set of photos,
	 * expressed as the `should_downgrade` boolean the resources consume
	 * (Feature 070, FR-070-01/08).
	 *
	 * Folds in the two short-circuits {@see self::do()} deliberately leaves to
	 * the caller: ownership, read from the `owner_id` already on each row (no
	 * extra query), and the admin bypass.
	 *
	 * **Deny by default** (NFR-070-03): a photo id with no resolved grant maps
	 * to `true` (downgrade). A rights fix must not fail open, so an id that
	 * falls out of the join is treated as ungranted rather than granted.
	 *
	 * @param \Illuminate\Support\Collection<int,\App\Models\Photo>|\Illuminate\Database\Eloquent\Collection<int,\App\Models\Photo> $photos
	 *
	 * @return array<string,bool> keyed by photo id; `true` means downgrade
	 */
	public function downgradeMap(\Illuminate\Support\Collection $photos, ?User $user): array
	{
		$ids = $photos->pluck('id')->all();
		$grants = $this->do($ids, $user);

		$map = [];
		foreach ($photos as $photo) {
			$is_owner = $user !== null && $photo->owner_id === $user->id;
			$can_access_full = $is_owner || ($grants[$photo->id]?->grants_full_photo_access ?? false);
			$map[$photo->id] = !$can_access_full;
		}

		return $map;
	}

	/**
	 * @param string[] $photo_ids
	 *
	 * @return array<string,PhotoGrants> keyed by photo id; absent means no album grants anything
	 */
	public function do(array $photo_ids, ?User $user): array
	{
		if (count($photo_ids) === 0) {
			return [];
		}

		// An admin bypasses every album grant, mirroring each policy method's
		// own admin short-circuit — and skips the query entirely.
		if ($user?->may_administrate === true) {
			return array_fill_keys($photo_ids, new PhotoGrants(true, true));
		}

		// PostgreSQL has no `MAX()` over booleans (unlike MySQL/SQLite, where a
		// boolean is an int); `bool_or()` is its equivalent. Mirrors
		// `GrantsAlbumRights::grantsResource()`'s identical choice.
		$or_aggregate = match (DB::getDriverName()) {
			'pgsql' => 'bool_or',
			default => 'MAX',
		};

		$query = DB::table(PA::PHOTO_ALBUM)->whereIn(PA::PHOTO_ID, $photo_ids);
		$this->album_query_policy->joinSubComputedAccessPermissions($query, PA::ALBUM_ID, 'left', '', true, $user);

		$rows = $query
			->groupBy(PA::PHOTO_ID)
			->selectRaw(PA::PHOTO_ID . ' as photo_id')
			->selectRaw($or_aggregate . '(computed_access_permissions.grants_full_photo_access) as grants_full_photo_access')
			->selectRaw($or_aggregate . '(computed_access_permissions.grants_download) as grants_download')
			->get();

		$result = [];
		foreach ($rows as $row) {
			$result[$row->photo_id] = new PhotoGrants(
				grants_full_photo_access: DbBool::parse($row->grants_full_photo_access),
				grants_download: DbBool::parse($row->grants_download),
			);
		}

		return $result;
	}
}
