<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Contracts;

interface JsonRequest
{
	/**
	 * Initialize a Json Request.
	 *
	 * @param string $url
	 * @param int    $ttl
	 *
	 * @return void
	 */
	public function init(string $url, int $ttl): void;

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
	 * Returns the decoded JSON response.
	 *
	 * @param bool $useCache if true, the JSON response is not fetched but
	 *                       served from cache if available
	 *
	 * @return mixed the type of the response depends on the content of the
	 *               HTTP response and may be anything: a primitive type,
	 *               an array or an object
	 */
	public function get_json(bool $useCache = false): mixed;
}