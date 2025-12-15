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
trait HasProStatusBooleanTrait
{
	protected ?bool $is_pro = null;

	public function is_pro(): bool
	{
		if ($this->is_pro === null) {
			$this->is_pro = $this->verify->validate() && $this->verify->is_pro();
		}

		return $this->is_pro;
	}
}
