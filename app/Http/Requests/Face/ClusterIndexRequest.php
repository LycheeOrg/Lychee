<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Face;

use App\Http\Requests\AbstractEmptyRequest;
use App\Models\Face;
use App\Policies\AiVisionPolicy;
use Illuminate\Support\Facades\Gate;

// TODO: Make sure FacePermissionMode applies here
class ClusterIndexRequest extends AbstractEmptyRequest
{
	public function authorize(): bool
	{
		return Gate::check(AiVisionPolicy::CAN_ASSIGN_FACE, Face::class);
	}
}
