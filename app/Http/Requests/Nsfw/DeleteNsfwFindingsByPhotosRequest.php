<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Nsfw;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Models\User;
use App\Rules\RandomIDRule;
use Illuminate\Support\Facades\Auth;

/**
 * Delete every NSFW finding of the given photos (Feature 089, FR-089-11). Administrators only.
 *
 * DELETE /api/v2/NsfwDetection/findings/photos
 */
class DeleteNsfwFindingsByPhotosRequest extends BaseApiRequest
{
	/** @var string[] */
	protected array $photo_ids = [];

	public function authorize(): bool
	{
		/** @var User|null $user */
		$user = Auth::user();

		return $user?->may_administrate === true;
	}

	public function rules(): array
	{
		return [
			RequestAttribute::PHOTO_IDS_ATTRIBUTE => 'required|array|min:1|max:500',
			RequestAttribute::PHOTO_IDS_ATTRIBUTE . '.*' => ['required', new RandomIDRule(false)],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->photo_ids = $values[RequestAttribute::PHOTO_IDS_ATTRIBUTE];
	}

	/**
	 * @return string[]
	 */
	public function photoIds(): array
	{
		return $this->photo_ids;
	}
}
