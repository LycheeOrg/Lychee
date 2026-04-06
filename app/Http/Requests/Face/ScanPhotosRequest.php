<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Face;

use App\Http\Requests\BaseApiRequest;
use App\Models\Album;
use App\Models\Face;
use App\Policies\AiVisionPolicy;
use App\Rules\RandomIDRule;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Validator;

/**
 * Request for triggering face detection on specific photos or an album.
 *
 * POST /api/v2/FaceDetection/scan
 */
class ScanPhotosRequest extends BaseApiRequest
{
	private ?array $photo_ids = null;
	private ?Album $album = null;
	private bool $force = false;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(AiVisionPolicy::CAN_TRIGGER_SCAN, [Face::class, $this->album]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			'photo_ids' => 'nullable|array',
			'photo_ids.*' => ['string', new RandomIDRule(false)],
			'album_id' => ['nullable', new RandomIDRule(true)],
			'force' => 'sometimes|boolean',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	public function withValidator(Validator $validator): void
	{
		$validator->after(function (Validator $validator): void {
			$values = $validator->validated();
			if (($values['photo_ids'] ?? null) === null && ($values['album_id'] ?? null) === null) {
				$validator->errors()->add('photo_ids', 'Either photo_ids or album_id must be provided.');
			}
		});
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->photo_ids = $values['photo_ids'] ?? null;
		$album_id = $values['album_id'] ?? null;
		$this->album = $album_id !== null ? Album::findOrFail($album_id) : null;
		$this->force = isset($values['force']) && static::toBoolean($values['force']);
	}

	/**
	 * @return string[]|null
	 */
	public function photoIds(): ?array
	{
		return $this->photo_ids;
	}

	public function album(): ?Album
	{
		return $this->album;
	}

	public function force(): bool
	{
		return $this->force;
	}
}
