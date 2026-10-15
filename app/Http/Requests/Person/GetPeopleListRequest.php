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

/**
 * Request for `GET /api/v3/People` (Feature 093, API-093-04).
 *
 * Same gate as `GET /api/v2/People` ({@see ListPersonsRequest}), plus the
 * `features.struct-of-array` flag and the face recognition settings, which
 * the v2 route enforces through its middleware group instead.
 */
class GetPeopleListRequest extends AbstractEmptyRequest
{
	public function authorize(): bool
	{
		return config('features.struct-of-array') === true &&
			$this->configs()->getValueAsBool('ai_vision_enabled') &&
			$this->configs()->getValueAsBool('ai_vision_face_enabled') &&
			Gate::check(AiVisionPolicy::CAN_VIEW_PEOPLE, Person::class);
	}
}
