<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Album;

use App\SmartAlbums\BaseSmartAlbum;

/**
 * Class SetSmartProtectionPolicy.
 */
class SetSmartProtectionPolicy
{
	/**
	 * @param BaseSmartAlbum $album
	 * @param bool           $is_public
	 *
	 * @return void
	 */
	public function do(BaseSmartAlbum $album, bool $is_public): void
	{
		if ($is_public) {
			$album->setPublic();
		} else {
			$album->setPrivate();
		}
	}
}