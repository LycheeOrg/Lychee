<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\DTO;

/**
 * The per-photo access grants {@see \App\Actions\Photo\StructOfArrays\ResolvesPhotoGrants}
 * resolves in bulk: the OR of every containing album's own grant.
 *
 * Neither field accounts for ownership — the caller ORs that in from the
 * `photos.owner_id` it has already selected, so no extra query is needed.
 */
readonly class PhotoGrants
{
	public function __construct(
		public bool $grants_full_photo_access,
		public bool $grants_download,
	) {
	}
}
