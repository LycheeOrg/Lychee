<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Face;

use App\Http\Requests\BaseApiRequest;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Validator;

/**
 * Request for batch-assigning multiple faces to a person from the maintenance page.
 *
 * Admin-only: requires the CAN_EDIT settings policy gate.
 */
class BatchAssignFacesRequest extends BaseApiRequest
{
	/** @var string[] */
	public array $face_ids = [];
	public ?string $person_id = null;
	public string $new_person_name;

	public function authorize(): bool
	{
		return Gate::check(SettingsPolicy::CAN_EDIT, Configs::class);
	}

	public function rules(): array
	{
		return [
			'face_ids' => ['required', 'array', 'min:1'],
			'face_ids.*' => ['required', 'string'],
			'person_id' => ['nullable', 'string'],
			'new_person_name' => ['nullable', 'string', 'max:255'],
		];
	}

	public function withValidator(Validator $validator): void
	{
		$validator->after(function (Validator $validator): void {
			if ($validator->errors()->isNotEmpty()) {
				return;
			}

			$values = $validator->validated();
			$has_person = isset($values['person_id']) && $values['person_id'] !== null;
			$has_name = isset($values['new_person_name']) && trim($values['new_person_name'] ?? '') !== '';

			if (!$has_person && !$has_name) {
				$validator->errors()->add('person_id', 'Either person_id or new_person_name must be provided.');
			}
		});
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->face_ids = $values['face_ids'];
		$this->person_id = $values['person_id'] ?? null;
		$this->new_person_name = $values['new_person_name'] ?? 'people.unknown';
	}
}
