<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Face;

use App\Http\Requests\BaseApiRequest;
use App\Models\Face;
use App\Policies\AiVisionPolicy;
use Illuminate\Support\Facades\Gate;

// TODO: Make sure FacePermissionMode applies here
class UnclusterFacesRequest extends BaseApiRequest
{
	public array $face_ids = [];

	public function authorize(): bool
	{
		return Gate::check(AiVisionPolicy::CAN_ASSIGN_FACE, Face::class);
	}

	public function rules(): array
	{
		return [
			'face_ids' => ['required', 'array', 'min:1'],
			// TODO remove exist check
			'face_ids.*' => ['required', 'string', 'exists:faces,id'],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->face_ids = $values['face_ids'];
	}
}
