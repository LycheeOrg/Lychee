<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Face;

use App\Http\Requests\BaseApiRequest;
use App\Models\Face;
use App\Policies\AiVisionPolicy;
use Illuminate\Support\Facades\Gate;

class ClusterAssignRequest extends BaseApiRequest
{
	public ?string $person_id = null;
	public string $new_person_name;

	public function authorize(): bool
	{
		return Gate::check(AiVisionPolicy::CAN_ASSIGN_FACE, Face::class);
	}

	public function rules(): array
	{
		return [
			'person_id' => ['nullable', 'string', 'exists:persons,id'],
			'new_person_name' => ['nullable', 'string', 'max:255'],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->person_id = $values['person_id'] ?? null;
		$this->new_person_name = $values['new_person_name'] ?? 'people.unknown';
	}
}
