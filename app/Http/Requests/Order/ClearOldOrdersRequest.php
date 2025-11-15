<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Order;

use App\Http\Requests\AbstractEmptyRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ClearOldOrdersRequest extends AbstractEmptyRequest
{
	public function authorize(): bool
	{
		/** @var User $user */
		$user = Auth::user();

		return $user?->may_administrate === true;
	}
}
