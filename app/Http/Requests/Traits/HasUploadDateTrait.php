<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

use Illuminate\Support\Carbon;

trait HasUploadDateTrait
{
	protected ?Carbon $upload_date = null;

	/**
	 * @return Carbon|null
	 */
	public function uploadDate(): ?Carbon
	{
		return $this->upload_date;
	}
}