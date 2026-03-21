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

class UpdatePersonRequest extends BaseApiRequest
{
	private string $person_id;
	private ?string $name;
	private ?bool $is_searchable;

	public function authorize(): bool
	{
		return Gate::check(AiVisionPolicy::CAN_EDIT_PERSON, Person::class);
	}

	public function rules(): array
	{
		return [
			'person_id' => ['required', 'string', 'exists:persons,id'],
			'name' => ['sometimes', 'string', 'max:255'],
			'is_searchable' => ['sometimes', 'boolean'],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->person_id = $values['person_id'];
		$this->name = $values['name'] ?? null;
		$this->is_searchable = isset($values['is_searchable']) ? static::toBoolean($values['is_searchable']) : null;
	}

	public function personId(): string
	{
		return $this->person_id;
	}

	public function name(): ?string
	{
		return $this->name;
	}

	public function isSearchable(): ?bool
	{
		return $this->is_searchable;
	}
}
