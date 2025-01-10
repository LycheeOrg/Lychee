<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

trait HasPasswordTrait
{
	protected ?string $password = null;

	/**
	 * Returns the password.
	 *
	 * Note that the return values `null` and `''` (empty string) have two
	 * distinct meanings:
	 *
	 *  - `null`: The attribute `password` was not part of the request, i.e.
	 *    it had not been transmitted at all.
	 *    This typically means that the user does not want to change his/her
	 *    password.
	 *  - `''`: The attribute `password` was part of the request and either
	 *    was equal to `null` or the empty string.
	 *    The case of a present, but "empty" attribute is normalized to `''`.
	 *    This typically means that the user wants to have no password, i.e.
	 *    a password-less account.
	 *
	 * @return string|null
	 */
	public function password(): ?string
	{
		return $this->password;
	}
}
