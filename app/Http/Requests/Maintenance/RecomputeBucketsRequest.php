<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Maintenance;

/**
 * Bucket keys only feed the struct-of-arrays listings: the maintenance
 * action is gated by the `features.struct-of-array` flag on top of the
 * admin check.
 */
class RecomputeBucketsRequest extends MaintenanceRequest
{
	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return config('features.struct-of-array') === true && parent::authorize();
	}
}
