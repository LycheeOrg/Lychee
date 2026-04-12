<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Moderation;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Models\User;
use App\Rules\RandomIDRule;
use Illuminate\Support\Facades\Auth;

/**
 * Authorization guard for retrieving a single photo from the moderation queue.
 *
 * Only administrators may fetch unvalidated photos via this endpoint.
 */
class GetModerationPhotoRequest extends BaseApiRequest
{
	protected string $photo_id = '';

	public function authorize(): bool
	{
		/** @var User|null */
		$user = Auth::user();

		return $user?->may_administrate === true;
	}

	public function rules(): array
	{
		return [
			RequestAttribute::PHOTO_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->photo_id = $values[RequestAttribute::PHOTO_ID_ATTRIBUTE];
	}

	public function photoId(): string
	{
		return $this->photo_id;
	}
}
