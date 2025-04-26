<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
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
