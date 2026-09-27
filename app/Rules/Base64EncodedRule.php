<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use function Safe\base64_decode;
use Safe\Exceptions\UrlException;

/**
 * Validates that a value is a decodable base64 string.
 *
 * Feature 069 (FR-069-11): the v3 search endpoints keep v2's base64 encoding of
 * `terms` — the token grammar embeds `:`, `>=`, `"`, `#` and `*`, which are
 * either reserved or routinely mangled in a raw query string. Unlike v2, which
 * hands the value straight to `base64_decode()` and would pass `false` into the
 * token parser, an undecodable value is rejected as a 422 here.
 */
final class Base64EncodedRule implements ValidationRule
{
	use ValidateTrait;

	/**
	 * {@inheritDoc}
	 */
	public function passes(string $attribute, mixed $value): bool
	{
		if (!is_string($value)) {
			return false;
		}

		try {
			base64_decode($value, true);

			return true;
		} catch (UrlException) {
			return false;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function message(): string
	{
		return ':attribute must be a valid base64-encoded string';
	}
}
