<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Users;

use App\Http\Requests\BaseApiRequest;
use Illuminate\Support\Facades\Auth;

class UsersRequest extends BaseApiRequest
{
	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Auth::check();
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
	}
}
