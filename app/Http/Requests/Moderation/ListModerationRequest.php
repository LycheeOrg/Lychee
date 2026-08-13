<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Moderation;

use App\Http\Requests\BaseApiRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Authorization guard for the moderation list endpoint.
 *
 * Only administrators may access the moderation queue.
 */
class ListModerationRequest extends BaseApiRequest
{
	/** @var int<1, 100> */
	public int $per_page = 100;

	public function authorize(): bool
	{
		/** @var User|null */
		$user = Auth::user();

		return $user?->may_administrate === true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			'per_page' => ['nullable', 'integer', 'min:1', 'max:500'],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->per_page = (int) ($values['per_page'] ?? 100);
	}
}
