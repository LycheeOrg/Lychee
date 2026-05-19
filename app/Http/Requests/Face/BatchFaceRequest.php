<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Face;

use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\BaseApiRequest;
use App\Models\Album;
use App\Models\Face;
use App\Policies\AlbumPolicy;
use App\Policies\PhotoPolicy;
use Illuminate\Support\Facades\Gate;

class BatchFaceRequest extends BaseApiRequest
{
	public array $face_ids = [];
	public string $action;
	public ?string $person_id = null;
	public ?string $new_person_name = null;
	private ?Album $album = null;

	public function authorize(): bool
	{
		if ($this->album !== null) {
			return Gate::check(AlbumPolicy::CAN_BATCH_FACE_OPS, [AbstractAlbum::class, $this->album]);
		}

		// Per-photo check: deny if any face's photo fails the gate.
		$face_ids = $this->input('face_ids', []);
		if (count($face_ids) === 0) {
			return false;
		}

		$faces = Face::with('photo')->whereIn('id', $face_ids)->get();
		foreach ($faces as $face) {
			if (!Gate::check(PhotoPolicy::CAN_ASSIGN_FACE_ON_PHOTO, $face->photo)) {
				return false;
			}
		}

		return true;
	}

	public function rules(): array
	{
		return [
			'face_ids' => ['required', 'array', 'min:1'],
			'face_ids.*' => ['required', 'string', 'exists:faces,id'],
			'action' => ['required', 'string', 'in:unassign,assign'],
			'person_id' => ['nullable', 'string', 'exists:persons,id'],
			'new_person_name' => ['nullable', 'string', 'max:255'],
			'album_id' => ['nullable', 'string'],
		];
	}

	public function withValidator(\Illuminate\Validation\Validator $validator): void
	{
		$validator->after(function (\Illuminate\Validation\Validator $validator): void {
			if ($validator->errors()->isNotEmpty()) {
				return;
			}

			$values = $validator->validated();
			if ($values['action'] === 'assign') {
				$has_person = isset($values['person_id']) && $values['person_id'] !== null;
				$has_name = isset($values['new_person_name']) && $values['new_person_name'] !== null && $values['new_person_name'] !== '';
				if (!$has_person && !$has_name) {
					$validator->errors()->add('person_id', 'Either person_id or new_person_name must be provided for assign action.');
				}
			}
		});
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->face_ids = $values['face_ids'];
		$this->action = $values['action'];
		$this->person_id = $values['person_id'] ?? null;
		$this->new_person_name = $values['new_person_name'] ?? null;
		$album_id = $values['album_id'] ?? null;
		$this->album = $album_id !== null ? Album::find($album_id) : null;
	}
}
