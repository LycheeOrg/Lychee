<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Metadata\Renamer;

use App\Repositories\ConfigManager;
use LycheeVerify\Contract\VerifyInterface;

final class AlbumRenamer extends Renamer
{
	public function __construct(
		VerifyInterface $verify,
		ConfigManager $config_manager,
		int $user_id,
	) {
		parent::__construct(
			verify: $verify,
			config_manager: $config_manager,
			user_id: $user_id,
			is_album: true,
		);
	}
}