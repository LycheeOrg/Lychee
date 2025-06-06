<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

trait HasEmailTrait
{
	/**
	 * The email address.
	 */
	protected string $email;

	/**
	 * Get the email address.
	 */
	public function email(): string
	{
		return $this->email;
	}
}
