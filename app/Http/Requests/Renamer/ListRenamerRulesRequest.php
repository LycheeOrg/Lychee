<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Renamer;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use Illuminate\Support\Facades\Auth;

/**
 * Request for listing renamer rules.
 */
class ListRenamerRulesRequest extends BaseApiRequest
{
	public bool $all = false;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		$user = Auth::user();

		return $user !== null && ($user->may_administrate || $user->may_upload);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::ALL_ATTRIBUTE => ['nullable', 'boolean'],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->all = static::toBoolean($values[RequestAttribute::ALL_ATTRIBUTE] ?? false);
	}
}
