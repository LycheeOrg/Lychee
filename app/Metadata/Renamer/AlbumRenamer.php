<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Metadata\Renamer;

final class AlbumRenamer extends Renamer
{
	public function __construct(
		int $user_id,
	) {
		parent::__construct(
			user_id: $user_id,
			is_album: true,
		);
	}
}