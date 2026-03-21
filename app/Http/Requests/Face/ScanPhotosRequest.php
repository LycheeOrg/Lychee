<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Face;

use App\Http\Requests\BaseApiRequest;
use App\Models\Album;
use App\Policies\AiVisionPolicy;
use Illuminate\Support\Facades\Gate;

/**
 * Request for triggering face detection on specific photos or an album.
 *
 * POST /api/v2/FaceDetection/scan
 */
class ScanPhotosRequest extends BaseApiRequest
{
	private ?array $photo_ids = null;
	private ?string $album_id = null;
	private bool $force = false;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(AiVisionPolicy::CAN_TRIGGER_SCAN, Album::class);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			'photo_ids' => 'nullable|array',
			'photo_ids.*' => 'string|exists:photos,id',
			'album_id' => 'nullable|string|exists:albums,id',
			'force' => 'sometimes|boolean',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->photo_ids = $values['photo_ids'] ?? null;
		$this->album_id = $values['album_id'] ?? null;
		$this->force = isset($values['force']) && static::toBoolean($values['force']);
	}

	/**
	 * @return string[]|null
	 */
	public function photoIds(): ?array
	{
		return $this->photo_ids;
	}

	public function albumId(): ?string
	{
		return $this->album_id;
	}

	public function force(): bool
	{
		return $this->force;
	}
}
