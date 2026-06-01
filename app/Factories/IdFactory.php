<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Factories;

use App\Constants\RandomID;
use App\Exceptions\InsufficientEntropyException;

class IdFactory
{
	public function createRandomID(): string
	{
		// URl-compatible variant of base64 encoding
		// `+` and `/` are replaced by `-` and `_`, resp.
		// The other characters (a-z, A-Z, 0-9) are legal within an URL.
		// As the number of bytes is divisible by 3, no trailing `=` occurs.
		try {
			$id = strtr(base64_encode(random_bytes(3 * RandomID::ID_LENGTH / 4)), '+/', '-_');
			// Last character whould not be a - for some version of android.
			// this will reduce the entropy and induce a slight bias but we are still
			// above the birthday bounds.
			if ($id[23] === '-') {
				$id[23] = '0';
			}
			// @codeCoverageIgnoreStart
		} catch (\Exception $e) {
			throw new InsufficientEntropyException($e);
		}

		return $id;
	}
}