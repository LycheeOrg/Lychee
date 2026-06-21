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
	public array $photo_ids = [];
	private ?Album $album = null;

	public function authorize(): bool
	{
		if ($this->album !== null) {
			return Gate::check(AlbumPolicy::CAN_BATCH_FACE_OPS, [AbstractAlbum::class, $this->album]);
		}

		$face_ids = $this->input('face_ids', []);
		$photo_ids = $this->input('photo_ids', []);

		if (count($face_ids) > 0) {
			$faces = Face::with('photo')->whereIn('id', $face_ids)->get();
			foreach ($faces as $face) {
				if (!Gate::check(PhotoPolicy::CAN_ASSIGN_FACE_ON_PHOTO, $face->photo)) {
					return false;
				}
			}

			return true;
		}

		if (count($photo_ids) > 0) {
			$photos = \App\Models\Photo::whereIn('id', $photo_ids)->get();
			foreach ($photos as $photo) {
				if (!Gate::check(PhotoPolicy::CAN_ASSIGN_FACE_ON_PHOTO, $photo)) {
					return false;
				}
			}

			return true;
		}

		return false;
	}

	public function rules(): array
	{
		return [
			'face_ids' => ['nullable', 'array'],
			'face_ids.*' => ['required', 'string'],
			'photo_ids' => ['nullable', 'array'],
			'photo_ids.*' => ['required', 'string'],
			'action' => ['required', 'string', 'in:unassign,assign'],
			'person_id' => ['nullable', 'string'],
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
			$has_face_ids = isset($values['face_ids']) && count($values['face_ids']) > 0;
			$has_photo_ids = isset($values['photo_ids']) && count($values['photo_ids']) > 0;

			if (!$has_face_ids && !$has_photo_ids) {
				$validator->errors()->add('face_ids', 'Either face_ids or photo_ids must be provided.');
			}

			if ($has_photo_ids && $values['action'] !== 'unassign') {
				$validator->errors()->add('photo_ids', 'photo_ids can only be used with unassign action.');
			}

			if ($has_photo_ids && (!isset($values['person_id']) || $values['person_id'] === null)) {
				$validator->errors()->add('person_id', 'person_id is required when using photo_ids.');
			}

			if ($values['action'] === 'assign' && $has_face_ids) {
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
		$this->face_ids = $values['face_ids'] ?? [];
		$this->action = $values['action'];
		$this->person_id = $values['person_id'] ?? null;
		$this->new_person_name = $values['new_person_name'] ?? null;
		$this->photo_ids = $values['photo_ids'] ?? [];
		$album_id = $values['album_id'] ?? null;
		$this->album = $album_id !== null ? Album::find($album_id) : null;
	}
}
