<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Person;

use App\Contracts\Http\Requests\HasPerson;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasPersonTrait;
use App\Models\Person;
use App\Policies\AiVisionPolicy;
use App\Repositories\ConfigManager;
use App\Rules\RandomIDRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ClaimPersonRequest extends BaseApiRequest implements HasPerson
{
	use HasPersonTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		if (!Gate::check(AiVisionPolicy::CAN_CLAIM_PERSON, Person::class)) {
			return false;
		}

		/** @var \App\Models\User $user */
		$user = Auth::user();
		if ($user->may_administrate) {
			// Admin can always claim (force-claim)
			return true;
		}

		// Non-admin: check if user claims are allowed by the administrator
		return app(ConfigManager::class)->getValueAsBool('ai_vision_face_allow_user_claim');
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			'id' => ['required', new RandomIDRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function prepareForValidation(): void
	{
		/** @disregard */
		$this->merge(['id' => $this->route('id')]);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->person = Person::findOrFail($values['id']);
	}
}
