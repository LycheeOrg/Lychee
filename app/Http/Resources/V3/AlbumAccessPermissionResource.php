<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\V3;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Response body of `GET /api/v3/Albums::accessPermissions`.
 *
 * Struct-of-Arrays: one entry per `(album, permission)` pair, index-aligned
 * across every array. An album with no user/group `access_permissions` row
 * still contributes exactly one row, with `permission_ids`/`user_*`/
 * `group_*`/`grants_*` all `null` at that index — this is how the caller
 * knows the album exists but has nothing shared yet. Public/link-only
 * permission rows (both `user_id` and `user_group_id` null) are never
 * surfaced here. `permission_ids` is what a client uses to target
 * `PATCH`/`DELETE /api/v2/Sharing`.
 */
#[TypeScript()]
class AlbumAccessPermissionResource extends Data
{
	/** @var string[] */
	public array $album_ids;
	/** @var string[] */
	public array $album_titles;
	/** @var int[] */
	public array $_lft;
	/** @var int[] */
	public array $_rgt;
	/** @var int[] */
	public array $owner_ids;
	/** @var string[] */
	public array $owner_names;
	/** @var (int|null)[] */
	public array $permission_ids;
	/** @var (int|null)[] */
	public array $user_ids;
	/** @var (string|null)[] */
	public array $user_names;
	/** @var (int|null)[] */
	public array $group_ids;
	/** @var (string|null)[] */
	public array $group_names;
	/** @var (bool|null)[] */
	public array $grants_full_photo_accesses;
	/** @var (bool|null)[] */
	public array $grants_downloads;
	/** @var (bool|null)[] */
	public array $grants_uploads;
	/** @var (bool|null)[] */
	public array $grants_edits;
	/** @var (bool|null)[] */
	public array $grants_deletes;

	/**
	 * @param string[]        $album_ids
	 * @param string[]        $album_titles
	 * @param int[]           $lft
	 * @param int[]           $rgt
	 * @param int[]           $owner_ids
	 * @param string[]        $owner_names
	 * @param (int|null)[]    $permission_ids
	 * @param (int|null)[]    $user_ids
	 * @param (string|null)[] $user_names
	 * @param (int|null)[]    $group_ids
	 * @param (string|null)[] $group_names
	 * @param (bool|null)[]   $grants_full_photo_accesses
	 * @param (bool|null)[]   $grants_downloads
	 * @param (bool|null)[]   $grants_uploads
	 * @param (bool|null)[]   $grants_edits
	 * @param (bool|null)[]   $grants_deletes
	 */
	public function __construct(
		array $album_ids,
		array $album_titles,
		array $lft,
		array $rgt,
		array $owner_ids,
		array $owner_names,
		array $permission_ids,
		array $user_ids,
		array $user_names,
		array $group_ids,
		array $group_names,
		array $grants_full_photo_accesses,
		array $grants_downloads,
		array $grants_uploads,
		array $grants_edits,
		array $grants_deletes,
	) {
		$this->album_ids = $album_ids;
		$this->album_titles = $album_titles;
		$this->_lft = $lft;
		$this->_rgt = $rgt;
		$this->owner_ids = $owner_ids;
		$this->owner_names = $owner_names;
		$this->permission_ids = $permission_ids;
		$this->user_ids = $user_ids;
		$this->user_names = $user_names;
		$this->group_ids = $group_ids;
		$this->group_names = $group_names;
		$this->grants_full_photo_accesses = $grants_full_photo_accesses;
		$this->grants_downloads = $grants_downloads;
		$this->grants_uploads = $grants_uploads;
		$this->grants_edits = $grants_edits;
		$this->grants_deletes = $grants_deletes;
	}
}
