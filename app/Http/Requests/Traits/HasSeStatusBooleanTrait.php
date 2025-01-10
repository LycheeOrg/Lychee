<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

use LycheeVerify\Verify;

/**
 * @property Verify $verify
 */
trait HasSeStatusBooleanTrait
{
	protected ?bool $is_se = null;

	public function is_se(): bool
	{
		if ($this->is_se === null) {
			$this->is_se = $this->verify->validate() && $this->verify->is_supporter();
		}

		return $this->is_se;
	}
}
