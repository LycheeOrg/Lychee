<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Person;

use App\Http\Requests\BaseApiRequest;
use App\Models\Person;
use App\Policies\AiVisionPolicy;
use Illuminate\Support\Facades\Gate;

class DestroyPersonRequest extends BaseApiRequest
{
	private string $person_id;

	public function authorize(): bool
	{
		return Gate::check(AiVisionPolicy::CAN_EDIT_PERSON, Person::class);
	}

	public function rules(): array
	{
		return [
			'person_id' => ['required', 'string', 'exists:persons,id'],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->person_id = $values['person_id'];
	}

	public function personId(): string
	{
		return $this->person_id;
	}
}
