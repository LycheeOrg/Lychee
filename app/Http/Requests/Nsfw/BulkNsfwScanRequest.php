<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Nsfw;

use App\Http\Requests\BaseApiRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Request for triggering a bulk NSFW scan.
 * Admin-only authorization.
 *
 * POST /api/v2/NsfwDetection/bulk-scan
 */
class BulkNsfwScanRequest extends BaseApiRequest
{
	private ?string $album_id = null;
	private bool $force = false;

	public function authorize(): bool
	{
		/** @var User|null $user */
		$user = Auth::user();

		return $user?->may_administrate === true;
	}

	public function rules(): array
	{
		return [
			'album_id' => 'sometimes|nullable|string',
			'force' => 'sometimes|boolean',
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->album_id = $values['album_id'] ?? null;
		$this->force = ($values['force'] ?? false) === true;
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
