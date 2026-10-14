<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Nsfw;

use App\Http\Requests\BaseApiRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Delete NSFW findings by detection id (Feature 089, FR-089-10). Administrators only.
 *
 * DELETE /api/v2/NsfwDetection/findings
 */
class DeleteNsfwFindingsRequest extends BaseApiRequest
{
	/** @var int[] */
	protected array $detection_ids = [];

	public function authorize(): bool
	{
		/** @var User|null $user */
		$user = Auth::user();

		return $user?->may_administrate === true;
	}

	public function rules(): array
	{
		return [
			'detection_ids' => 'required|array|min:1|max:500',
			'detection_ids.*' => 'required|integer|min:1',
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->detection_ids = array_map(fn ($id) => (int) $id, $values['detection_ids']);
	}

	/**
	 * @return int[]
	 */
	public function detectionIds(): array
	{
		return $this->detection_ids;
	}
}
