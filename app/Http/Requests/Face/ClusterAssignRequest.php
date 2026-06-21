<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Face;

use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\BaseApiRequest;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Gate;

class ClusterAssignRequest extends BaseApiRequest
{
	public ?string $person_id = null;
	public string $new_person_name;

	public function authorize(): bool
	{
		return Gate::check(AlbumPolicy::CAN_BATCH_FACE_OPS, [AbstractAlbum::class, null]);
	}

	public function rules(): array
	{
		return [
			'person_id' => ['nullable', 'string'],
			'new_person_name' => ['nullable', 'string', 'max:255'],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->person_id = $values['person_id'] ?? null;
		$this->new_person_name = $values['new_person_name'] ?? 'people.unknown';
	}
}
