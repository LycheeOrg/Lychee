<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

trait HasUsernameTrait
{
	protected string $username;

	/**
	 * @return string
	 */
	public function username(): string
	{
		return $this->username;
	}
}
