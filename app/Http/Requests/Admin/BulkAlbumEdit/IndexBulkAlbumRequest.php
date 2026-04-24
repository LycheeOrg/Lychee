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
 * FormRequest for listing albums in the Bulk Album Edit admin page.
 *
 * Admin-only. Supports optional name search and pagination parameters.
 */
class IndexBulkAlbumRequest extends BaseApiRequest
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
			'page' => ['sometimes', 'integer', 'min:1'],
			'per_page' => ['sometimes', 'integer', 'in:25,50,100'],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
	}
}
