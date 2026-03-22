<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Face;

use App\Contracts\Http\Requests\HasFace;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasFaceTrait;
use App\Models\Face;
use App\Models\Person;
use App\Policies\AiVisionPolicy;
use App\Rules\RandomIDRule;
use Illuminate\Support\Facades\Gate;

class AssignFaceRequest extends BaseApiRequest implements HasFace
{
	use HasFaceTrait;

	private ?Person $person;
	private ?string $new_person_name;

	public function authorize(): bool
	{
		return Gate::check(AiVisionPolicy::CAN_ASSIGN_FACE, Face::class);
	}

	public function rules(): array
	{
		return [
			'id' => ['required', new RandomIDRule(false)],
			'person_id' => ['nullable', 'string', 'exists:persons,id'],
			'new_person_name' => ['nullable', 'string', 'max:255'],
		];
	}

	public function withValidator(\Illuminate\Validation\Validator $validator): void
	{
		$validator->after(function (\Illuminate\Validation\Validator $validator): void {
			if ($validator->errors()->isNotEmpty()) {
				return;
			}

			$values = $validator->validated();
			if (($values['person_id'] ?? null) === null && ($values['new_person_name'] ?? null) === null) {
				$validator->errors()->add('person_id', 'Either person_id or new_person_name must be provided.');
			}
		});
	}

	protected function prepareForValidation(): void
	{
		/** @disregard */
		$this->merge(['id' => $this->route('id')]);
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->face = Face::findOrFail($values['id']);
		$this->person = isset($values['person_id']) ? Person::find($values['person_id']) : null;
		$this->new_person_name = $values['new_person_name'] ?? null;
	}

	public function person(): ?Person
	{
		return $this->person;
	}

	public function newPersonName(): ?string
	{
		return $this->new_person_name;
	}
}
