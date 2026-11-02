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

class MergePersonRequest extends BaseApiRequest
{
	private Person $source_person;

	public function authorize(): bool
	{
		// The source is deleted by the merge, so it must be manageable; the target only receives faces.
		return Gate::check(AiVisionPolicy::CAN_MERGE_PERSONS, Person::class) &&
			Gate::check(AiVisionPolicy::CAN_MANAGE_PERSON, [Person::class, $this->source_person]);
	}

	public function rules(): array
	{
		return [
			'source_person_id' => ['required', 'string', 'exists:persons,id'],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->source_person = Person::findOrFail($values['source_person_id']);
	}

	public function sourcePerson(): Person
	{
		return $this->source_person;
	}
}
