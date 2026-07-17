<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Order;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use Illuminate\Support\Facades\Auth;

/**
 * Retrieve all the Orders for a user.
 *
 * Only usable by logged in users.
 */
class ListOrderRequest extends BaseApiRequest
{
	public bool $include_pending = false;

	public function authorize(): bool
	{
		return Auth::check();
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::INCLUDE_PENDING_ATTRIBUTE => ['nullable', 'boolean'],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->include_pending = static::toBoolean($values[RequestAttribute::INCLUDE_PENDING_ATTRIBUTE] ?? false);
	}
}
