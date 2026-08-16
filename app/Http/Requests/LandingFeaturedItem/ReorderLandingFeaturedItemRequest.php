<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\LandingFeaturedItem;

use App\Http\Requests\BaseApiRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ReorderLandingFeaturedItemRequest extends BaseApiRequest
{
	/** @var string[] */
	public array $ids;

	public function authorize(): bool
	{
		/** @var User|null */
		$user = Auth::user();

		return $user?->may_administrate === true;
	}

	public function rules(): array
	{
		return [
			'ids' => ['required', 'array'],
			'ids.*' => ['required', 'string', 'distinct'],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->ids = $values['ids'];
	}
}
