<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\Traits;

use App\Models\Photo;
use Illuminate\Database\Eloquent\Collection;

trait HasPhotosTrait
{
	/**
	 * @var Collection<int,Photo>
	 */
	protected Collection $photos;

	/**
	 * @return Collection<int,Photo>
	 */
	public function photos(): Collection
	{
		return $this->photos;
	}
}
