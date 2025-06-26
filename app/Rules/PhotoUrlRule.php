<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Rules;

use App\Models\Configs;
use Illuminate\Contracts\Validation\ValidationRule;
use Safe\Exceptions\UrlException;
use function Safe\parse_url;

final class PhotoUrlRule implements ValidationRule
{
	/**
	 * {@inheritDoc}
	 */
	public function validate(string $attribute, mixed $value, \Closure $fail): void
	{
		// Validate we are dealing with a string.
		if (is_string($value) === false) {
			$fail($attribute . ' is not a string');

			return;
		}

		// Validate we are dealing with a valid URL.
		// Note: This does not check whether the URL is reachable, only that it is syntactically correct.
		if (!filter_var($value, FILTER_VALIDATE_URL)) {
			$fail($attribute . ' is not a valid URL');

			return;
		}

		try {
			// Get the URL components.
			/** @var array{scheme:string|null,host:string,port:string|int|null} $url */
			$url = parse_url($value);
		// @codeCoverageIgnoreStart
		// This is already filtered by the previous filter_var check, but we catch it here
		// to ensure we handle any unexpected exceptions gracefully.
		} catch (UrlException) {
			$fail($attribute . ' is not a valid URL');

			return;
		}
		// @codeCoverageIgnoreEnd

		$scheme = $url['scheme'] ?? '';
		$host = $url['host'] ?? '';
		$port = $url['port'] ?? null;

		if (
			Configs::getValueAsBool('import_via_url_require_https') &&
			$scheme !== 'https'
		) {
			$fail($attribute . ' must be a valid HTTPS URL.');

			return;
		}

		if (!in_array($scheme, ['https', 'http', ''], true)) {
			$fail($attribute . ' must be a valid HTTP or HTTPS URL.');

			return;
		}

		if (
			Configs::getValueAsBool('import_via_url_forbidden_ports') &&
			$port !== null &&
			!in_array($port, [80, 443], true)
		) {
			$fail($attribute . ' must use a valid port such as 80 or 443.');

			return;
		}

		if (
			Configs::getValueAsBool('import_via_url_forbidden_local_ip') &&
			filter_var($host, FILTER_VALIDATE_IP) !== false &&
			filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE) === false
		) {
			$fail($attribute . ' must not be a private IP address.');

			return;
		}

		if (
			Configs::getValueAsBool('import_via_url_forbidden_localhost') &&
			$host === 'localhost'
		) {
			$fail($attribute . ' must not be localhost.');

			return;
		}
	}
}
