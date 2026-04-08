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
use App\Rules\RandomIDRule;
use Illuminate\Support\Facades\Gate;

class UpdatePersonRequest extends BaseApiRequest implements HasPerson
{
	use HasPersonTrait;

	private ?string $name;
	private ?bool $is_searchable;
	private ?int $user_id;

	public function authorize(): bool
	{
		if (!Gate::check(AiVisionPolicy::CAN_EDIT_PERSON, Person::class)) {
			return false;
		}

		if ($this->is_searchable !== null &&
			!Gate::check(AiVisionPolicy::CAN_CHANGE_PERSON_SEARCHABILITY, [Person::class, $this->person])) {
			return false;
		}

		// Only admins may set user_id
		if ($this->user_id !== null && !request()->user()?->may_administrate) {
			return false;
		}

		return true;
	}

	public function rules(): array
	{
		return [
			'id' => ['required', new RandomIDRule(false)],
			'name' => ['sometimes', 'string', 'max:255'],
			'is_searchable' => ['sometimes', 'boolean'],
			'user_id' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
		];
	}

	protected function prepareForValidation(): void
	{
		/** @disregard */
		$this->merge(['id' => $this->route('id')]);
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->person = Person::findOrFail($values['id']);
		$this->name = $values['name'] ?? null;
		$this->is_searchable = isset($values['is_searchable']) ? static::toBoolean($values['is_searchable']) : null;
		$this->user_id = array_key_exists('user_id', $values) ? ($values['user_id'] === null ? null : (int) $values['user_id']) : -1;
	}

	public function name(): ?string
	{
		return $this->name;
	}

	public function isSearchable(): ?bool
	{
		return $this->is_searchable;
	}

	/**
	 * Returns the new user_id value, or -1 if user_id was not included in the request.
	 * -1 signals "not provided" since null is a valid value (unlink person from user).
	 */
	public function userId(): int|null
	{
		return $this->user_id;
	}

	/**
	 * Whether the request explicitly contains a user_id field.
	 */
	public function hasUserId(): bool
	{
		return $this->user_id !== -1;
	}
}
