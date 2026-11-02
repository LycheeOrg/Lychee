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
use Illuminate\Support\Facades\Gate;

class DestroyPersonRequest extends BaseApiRequest implements HasPerson
{
	use HasPersonTrait;

	public function authorize(): bool
	{
		return Gate::check(AiVisionPolicy::CAN_MANAGE_PERSON, [Person::class, $this->person]);
	}

	protected function prepareForValidation(): void
	{
		/** @disregard */
		$this->merge(['person_id' => $this->route('id')]);
	}

	public function rules(): array
	{
		return [
			'person_id' => ['required', 'string', 'exists:persons,id'],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->person = Person::findOrFail($values['person_id']);
	}
}
