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

class StorePersonRequest extends BaseApiRequest
{
	private string $name;
	private ?int $user_id;

	public function authorize(): bool
	{
		return Gate::check(AiVisionPolicy::CAN_EDIT_PERSON, Person::class);
	}

	public function rules(): array
	{
		return [
			'name' => ['required', 'string', 'max:255'],
			'user_id' => ['nullable', 'integer', 'exists:users,id', 'unique:persons,user_id'],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->name = $values['name'];
		$this->user_id = isset($values['user_id']) ? (int) $values['user_id'] : null;
	}

	public function name(): string
	{
		return $this->name;
	}

	public function userId(): ?int
	{
		return $this->user_id;
	}
}
