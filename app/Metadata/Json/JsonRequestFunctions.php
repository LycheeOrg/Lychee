<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Metadata\Json;

use App\Contracts\JsonRequest;
use Illuminate\Support\Facades\Log;

class JsonRequestFunctions extends ExternalRequestFunctions implements JsonRequest
{
	/**
	 * {@inheritDoc}
	 */
	public function get_json(bool $use_cache = false): mixed
	{
		$data = $this->get_data($use_cache);
		if ($data === null) {
			// @codeCoverageIgnoreStart
			return null;
			// @codeCoverageIgnoreEnd
		}

		try {
			return json_decode($data, false, 512, JSON_THROW_ON_ERROR);
			// @codeCoverageIgnoreStart
		} catch (\JsonException $e) {
			Log::error(__METHOD__ . ':' . __LINE__ . ' ' . $e->getMessage());
		}

		return null;
		// @codeCoverageIgnoreEnd
	}
}
