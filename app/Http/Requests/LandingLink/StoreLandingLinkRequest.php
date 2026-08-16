<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\LandingLink;

use App\Enum\LandingLinkPlacement;
use App\Http\Requests\BaseApiRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;

class StoreLandingLinkRequest extends BaseApiRequest
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
			'label' => ['required', 'string', 'max:255'],
			'url' => ['required', 'string', 'url', 'max:2048'],
			'placement' => ['required', 'string', new Enum(LandingLinkPlacement::class)],
			'open_in_new_tab' => ['sometimes', 'boolean'],
			'sort_order' => ['sometimes', 'integer'],
			'enabled' => ['sometimes', 'boolean'],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		// No pre-processing needed; validated values are used directly.
	}
}
