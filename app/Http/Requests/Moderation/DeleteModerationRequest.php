<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Moderation;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Rules\RandomIDRule;
use Illuminate\Support\Facades\Auth;

/**
 * Request for DELETE /api/v2/Moderation (Feature 119, FR-119-10).
 *
 * Admin only, as {@see ApproveModerationRequest}. No album: the photos
 * themselves are deleted.
 */
class DeleteModerationRequest extends BaseApiRequest
{
	/** @var string[] */
	protected array $photo_ids = [];

	public function authorize(): bool
	{
		return Auth::user()?->may_administrate === true;
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
