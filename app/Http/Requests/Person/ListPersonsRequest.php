<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Person;

use App\Http\Requests\AbstractEmptyRequest;
use App\Models\Person;
use App\Policies\AiVisionPolicy;
use Illuminate\Support\Facades\Gate;

class ListPersonsRequest extends AbstractEmptyRequest
{
	public function authorize(): bool
	{
		return Gate::check(AiVisionPolicy::CAN_VIEW_PEOPLE, Person::class);
	}
}
