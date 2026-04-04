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
use App\Policies\AiVisionPolicy;
use App\Rules\RandomIDRule;
use Illuminate\Support\Facades\Gate;

class AssignFaceRequest extends BaseApiRequest implements HasFace
{
	use HasFaceTrait;

	public ?string $person_id = null;
	public string $new_person_name;

	public function authorize(): bool
	{
		return Gate::check(AiVisionPolicy::CAN_ASSIGN_FACE, Face::class);
	}

	public function rules(): array
	{
		return [
			'id' => ['required', new RandomIDRule(false)],
			'person_id' => ['nullable', 'string'],
			'new_person_name' => ['nullable', 'string', 'max:255'],
		];
	}

	public function withValidator(\Illuminate\Validation\Validator $validator): void
	{
		$validator->after(function (\Illuminate\Validation\Validator $validator): void {
			if ($validator->errors()->isNotEmpty()) {
				return;
			}

			// Allowing both to be null/absent is valid — it means unassign.
			// We only reject the case where both new_person_name and person_id are
			// provided simultaneously with non-null values (ambiguous intent).
			$values = $validator->validated();
			$hasPerson = isset($values['person_id']) && $values['person_id'] !== null;
			$hasName = isset($values['new_person_name']) && $values['new_person_name'] !== null;
			if ($hasPerson && $hasName) {
				$validator->errors()->add('person_id', 'Provide either person_id or new_person_name, not both.');
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
		$this->person_id = $values['person_id'] ?? null;
		$this->new_person_name = $values['new_person_name'] ?? 'people.unknown';
	}
}
