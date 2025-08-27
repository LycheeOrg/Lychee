<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Admin;

use App\Http\Requests\AbstractEmptyRequest;
use App\Models\Configs;
use Illuminate\Support\Facades\Auth;

class ImportFromServerOptionsRequest extends AbstractEmptyRequest
{
	public function authorize(): bool
	{
		// Only the owner of Lychee can use this functionality
		return Auth::user() !== null && Auth::user()->id === Configs::getValueAsInt('owner_id');
	}
}
