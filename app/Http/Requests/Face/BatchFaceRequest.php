<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Face;

use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\Authorize\AuthorizePhotosBelongToAlbumTrait;
use App\Models\Album;
use App\Models\Face;
use App\Models\Photo;
use App\Policies\AlbumPolicy;
use App\Policies\PhotoPolicy;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

class BatchFaceRequest extends BaseApiRequest
{
	use AuthorizePhotosBelongToAlbumTrait;

	public array $face_ids = [];
	public string $action;
	public ?string $person_id = null;
	public ?string $new_person_name = null;
	public array $photo_ids = [];
	private ?Album $album = null;

	/**
	 * The batch endpoint always operates on an explicit list of faces or photos.
	 *
	 * Every selected object is therefore authorized individually; a supplied
	 * `album_id` is an additional constraint, never a substitute for those
	 * per-object checks (see GHSA-x6f7-qp5q-w37f).
	 */
	public function authorize(): bool
	{
		$photos = $this->selectedPhotos();
		if ($photos->count() === 0) {
			return false;
		}

		foreach ($photos as $photo) {
			if (!Gate::check(PhotoPolicy::CAN_ASSIGN_FACE_ON_PHOTO, $photo)) {
				return false;
			}
		}

		if ($this->album === null) {
			return true;
		}

		return Gate::check(AlbumPolicy::CAN_BATCH_FACE_OPS, [AbstractAlbum::class, $this->album]) &&
			$this->allPhotosBelongToAlbum($photos, $this->album->id);
	}

	/**
	 * Resolve the photos carrying the explicitly selected objects.
	 *
	 * @return Collection<int,Photo>
	 */
	private function selectedPhotos(): Collection
	{
		if (count($this->face_ids) > 0) {
			/** @var Collection<int,Photo> */
			return Face::with('photo.albums')
				->whereIn('id', $this->face_ids)
				->get()
				->pluck('photo')
				->filter(fn (?Photo $photo): bool => $photo !== null)
				->unique('id')
				->values();
		}

		return Photo::with('albums')->whereIn('id', $this->photo_ids)->get();
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
