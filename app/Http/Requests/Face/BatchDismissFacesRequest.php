<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Face;

use App\Http\Requests\BaseApiRequest;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use Illuminate\Support\Facades\Gate;

/**
 * Request for batch-dismissing multiple faces from the maintenance page.
 *
 * Admin-only: requires the CAN_EDIT settings policy gate.
 */
class BatchDismissFacesRequest extends BaseApiRequest
{
	/** @var string[] */
	public array $face_ids = [];

	public function authorize(): bool
	{
		return Gate::check(SettingsPolicy::CAN_EDIT, Configs::class);
	}

	public function rules(): array
	{
		return [
			'face_ids' => ['required', 'array', 'min:1'],
			'face_ids.*' => ['required', 'string', 'exists:faces,id'],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->face_ids = $values['face_ids'];
	}
}
