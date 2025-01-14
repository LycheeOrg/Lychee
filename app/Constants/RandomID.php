<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Constants;

class RandomID
{
	/**
	 * The length of the random, character-based ID.
	 *
	 * The ID is a random byte sequence encoded as Base64.
	 * 24 characters means 3/4 * 24 = 18 bytes, i.e. 144 bits of randomness.
	 * 144 bits (>128 bit) of randomness are considered sufficient to only
	 * allow for a small chance to guess an ID.
	 * The length must be divisible by 4 as otherwise the Base64 encoding
	 * uses the character `=` for padding which must not be used within a URL.
	 * We use Base64 encoding (instead of an encoding with hex digits),
	 * because Base64 encoding is more space efficient and also more time
	 * efficient when used as a primary ID in a database.
	 *
	 * @var int
	 */
	public const ID_LENGTH = 24;
	public const ID_TYPE = 'string';
	public const LEGACY_ID_NAME = 'legacy_id';
	public const LEGACY_ID_TYPE = 'integer';
}