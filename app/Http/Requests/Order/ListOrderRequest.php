<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Order;

use App\Http\Requests\AbstractEmptyRequest;
use Illuminate\Support\Facades\Auth;

/**
 * Retrieve all the Orders for a user.
 *
 * Only usable by logged in users.
 */
class ListOrderRequest extends AbstractEmptyRequest
{
	public function authorize(): bool
	{
		return Auth::check();
	}
}