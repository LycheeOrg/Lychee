<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\LandingLink;

use App\Enum\LandingLinkPlacement;
use App\Http\Requests\BaseApiRequest;
use App\Models\LandingLink;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;

class UpdateLandingLinkRequest extends BaseApiRequest
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
		return [
			'landing_link_id' => ['required', 'string'],
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
		$this->landing_link = LandingLink::findOrFail($values['landing_link_id']);

		if ($this->landing_link->is_built_in && $values['url'] !== $this->landing_link->url) {
			throw ValidationException::withMessages(['url' => 'The URL of a built-in link cannot be changed.']);
		}
	}
}
