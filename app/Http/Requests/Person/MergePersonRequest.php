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
	private string $source_person_id;

	public function authorize(): bool
	{
		return Gate::check(AiVisionPolicy::CAN_MERGE_PERSONS, Person::class);
	}

	public function rules(): array
	{
		return [
			'source_person_id' => ['required', 'string', 'exists:persons,id'],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->source_person_id = $values['source_person_id'];
	}

	public function sourcePersonId(): string
	{
		return $this->source_person_id;
	}
}
