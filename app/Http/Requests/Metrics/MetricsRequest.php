<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Metrics;

use App\Http\Requests\AbstractEmptyRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class MetricsRequest extends AbstractEmptyRequest
{
	public function authorize(): bool
	{
		/** @var User $user */
		$user = Auth::user();

		// TODO: For now we do not use the policies, refactor later.
		return $user?->may_administrate === true;
	}
}
