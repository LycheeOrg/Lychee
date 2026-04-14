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
 * FormRequest for transferring ownership of albums in bulk.
 *
 * Admin-only.
 */
class SetOwnerBulkAlbumRequest extends BaseApiRequest
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
			'album_ids' => ['required', 'array', 'min:1'],
			'album_ids.*' => ['required', 'string'],
			'owner_id' => ['required', 'integer', 'exists:users,id'],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
	}
}
