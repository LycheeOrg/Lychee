<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\LandingLink;

use App\Http\Requests\BaseApiRequest;
use App\Models\LandingLink;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class DestroyLandingLinkRequest extends BaseApiRequest
{
	public LandingLink $landing_link;

	public function authorize(): bool
	{
		/** @var User|null */
		$user = Auth::user();

		return $user?->may_administrate === true;
	}

	protected function prepareForValidation(): void
	{
		/** @disregard */
		$this->merge(['landing_link_id' => $this->route('landingLink')]);
	}

	public function rules(): array
	{
		return ['landing_link_id' => ['required', 'string']];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->landing_link = LandingLink::findOrFail($values['landing_link_id']);

		if ($this->landing_link->is_built_in) {
			throw ValidationException::withMessages(['landing_link_id' => 'This built-in link cannot be deleted.']);
		}
	}
}
