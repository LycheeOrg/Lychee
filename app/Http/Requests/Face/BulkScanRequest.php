<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Face;

use App\Http\Requests\BaseApiRequest;
use App\Models\Album;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use App\Rules\RandomIDRule;
use Illuminate\Support\Facades\Gate;

/**
 * Admin-only request for bulk-enqueue of unscanned photos.
 *
 * POST /api/v2/FaceDetection/bulk-scan
 */
class BulkScanRequest extends BaseApiRequest
{
	private ?Album $album = null;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(SettingsPolicy::CAN_EDIT, Configs::class);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			'album_id' => ['nullable', new RandomIDRule(true)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$album_id = $values['album_id'] ?? null;
		$this->album = $album_id !== null ? Album::findOrFail($album_id) : null;
	}

	public function album(): ?Album
	{
		return $this->album;
	}
}
