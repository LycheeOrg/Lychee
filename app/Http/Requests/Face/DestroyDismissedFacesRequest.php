<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Face;

use App\Assets\Features;
use App\Http\Requests\AbstractEmptyRequest;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use Illuminate\Support\Facades\Gate;

class DestroyDismissedFacesRequest extends AbstractEmptyRequest
{
	public function authorize(): bool
	{
		if (Features::inactive('ai-vision')) {
			return false;
		}

		return Gate::check(SettingsPolicy::CAN_EDIT, Configs::class);
	}
}
