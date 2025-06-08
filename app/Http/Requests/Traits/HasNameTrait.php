<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

trait HasNameTrait
{
	protected string $name;

	/**
	 * @return string
	 */
	public function name(): string
	{
		return $this->name;
	}
}
