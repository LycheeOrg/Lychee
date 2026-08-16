<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\LandingFeaturedItem;

use App\Enum\LandingFeaturedItemType;
use App\Http\Requests\BaseApiRequest;
use App\Models\User;
use App\Rules\LandingFeaturedItemExistsRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;

class StoreLandingFeaturedItemRequest extends BaseApiRequest
{
	public function authorize(): bool
	{
		/** @var User|null */
		$user = Auth::user();

		return $user?->may_administrate === true;
	}

	public function rules(): array
	{
		return [
			'item_type' => ['required', 'string', new Enum(LandingFeaturedItemType::class)],
			'item_id' => ['required', 'string', new LandingFeaturedItemExistsRule()],
			'sort_order' => ['sometimes', 'integer'],
			'enabled' => ['sometimes', 'boolean'],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		// No pre-processing needed; validated values are used directly.
	}
}
