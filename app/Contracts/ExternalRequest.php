<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Contracts;

interface ExternalRequest
{
	/**
	 * Clear the cache of the Json Request.
	 */
	public function clear_cache(): void;

	/**
	 * Return the age of the last query in days/hours/minutes.
	 *
	 * @return string
	 */
	public function get_age_text(): string;

	/**
	 * Returns the content of the response.
	 *
	 * @param bool $use_cache if true, the JSON response is not fetched but
	 *                        served from cache if available
	 *
	 * @return string|null the type of the response depends on the content of the
	 *                     HTTP response and may be anything: a primitive type,
	 *                     an array or an object
	 */
	public function get_data(bool $use_cache = false): string|null;
}