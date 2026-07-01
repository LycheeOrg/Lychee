<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\DTO;

use App\Models\AccessPermission;
use Illuminate\Support\Collection;

/**
 * Read-only, non-persistable capability snapshot for the current user on an
 * album: the union (logical OR) of every applicable AccessPermission row's
 * grant flags, independent of row order or source (direct-user vs. group).
 */
final readonly class EffectiveAccessPermission
{
	public function __construct(
		public bool $grants_full_photo_access = false,
		public bool $grants_download = false,
		public bool $grants_upload = false,
		public bool $grants_edit = false,
		public bool $grants_delete = false,
	) {
	}

	/**
	 * Merge every given AccessPermission row into a single snapshot: each
	 * grant flag is true if it is true on at least one row.
	 *
	 * @param Collection<int,AccessPermission> $permissions
	 */
	public static function merge(Collection $permissions): self
	{
		return new self(
			grants_full_photo_access: $permissions->contains(fn (AccessPermission $p) => $p->grants_full_photo_access),
			grants_download: $permissions->contains(fn (AccessPermission $p) => $p->grants_download),
			grants_upload: $permissions->contains(fn (AccessPermission $p) => $p->grants_upload),
			grants_edit: $permissions->contains(fn (AccessPermission $p) => $p->grants_edit),
			grants_delete: $permissions->contains(fn (AccessPermission $p) => $p->grants_delete),
		);
	}
}
