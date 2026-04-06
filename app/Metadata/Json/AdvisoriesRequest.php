<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Metadata\Json;

use Illuminate\Support\Facades\Config;

/**
 * HTTP request class for the GitHub Security Advisories API.
 *
 * Extends JsonRequestFunctions to send the required
 * "Accept: application/vnd.github+json" header.
 */
class AdvisoriesRequest extends JsonRequestFunctions
{
	/**
	 * We just override the constructor.
	 * The rest is handled directly by the parent class.
	 */
	public function __construct()
	{
		parent::__construct(
			Config::get('urls.advisories.api_url'),
			Config::get('urls.advisories.cache_ttl'),
			['Accept: application/vnd.github+json'],
		);
	}
}
