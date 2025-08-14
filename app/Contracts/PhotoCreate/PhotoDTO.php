<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Contracts\PhotoCreate;

use App\Models\Photo;
use App\Models\Tag;
use Illuminate\Support\Collection;

interface PhotoDTO
{
	public function getPhoto(): Photo;

	/**
	 * @return Collection<int,Tag>
	 */
	public function getTags(): Collection;
}
