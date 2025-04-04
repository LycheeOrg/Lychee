<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Contracts;

interface JsonRequest extends ExternalRequest
{
	/**
	 * Returns the decoded JSON response.
	 *
	 * @param bool $use_cache if true, the JSON response is not fetched but
	 *                        served from cache if available
	 *
	 * @return mixed the type of the response depends on the content of the
	 *               HTTP response and may be anything: a primitive type,
	 *               an array or an object
	 */
	public function get_json(bool $use_cache = false): mixed;
}