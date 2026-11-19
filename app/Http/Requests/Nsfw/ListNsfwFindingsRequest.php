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
 * NSFW findings, one entry per photo (Feature 089, FR-089-09). Administrators only.
 *
 * GET /api/v2/NsfwDetection/findings
 */
class ListNsfwFindingsRequest extends BaseApiRequest
{
	/** @var int<1, 100> */
	public int $per_page = 30;

	public function authorize(): bool
	{
		/** @var User|null $user */
		$user = Auth::user();

		return $user?->may_administrate === true;
	}

	public function rules(): array
	{
		return [
			'page' => ['required', 'integer', 'min:1'],
			'per_page' => ['required', 'integer', 'min:1', 'max:100'],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->per_page = (int) $values['per_page'];
	}
}
