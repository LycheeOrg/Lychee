<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\WebAuthn;

use App\Http\Requests\AbstractEmptyRequest;
use Illuminate\Support\Facades\Auth;

class ListCredentialsRequest extends AbstractEmptyRequest
{
	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Auth::check();
	}
}
