<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Admin\BulkAlbumEdit;

use App\Http\Requests\BaseApiRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * FormRequest for the "select all matching IDs" endpoint.
 *
 * Admin-only. Returns up to 1 000 album IDs matching the optional search filter.
 */
class IdsBulkAlbumRequest extends BaseApiRequest
{
	public function authorize(): bool
	{
		/** @var User|null */
		$user = Auth::user();

		return $user?->may_administrate === true;
	}

	public function rules(): array
	{
		return [
			'search' => ['sometimes', 'nullable', 'string', 'max:255'],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
	}
}
