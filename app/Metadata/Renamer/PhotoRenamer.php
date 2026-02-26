<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Metadata\Renamer;

final class PhotoRenamer extends Renamer
{
	/**
	 * @param int        $user_id  The ID of the user whose rules should be applied
	 * @param int[]|null $rule_ids When provided, only apply rules whose IDs are in this array
	 */
	public function __construct(
		int $user_id,
		?array $rule_ids = null,
	) {
		parent::__construct(
			user_id: $user_id,
			is_photo: true,
			rule_ids: $rule_ids,
		);
	}
}