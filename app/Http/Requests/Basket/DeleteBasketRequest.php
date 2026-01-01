<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Basket;

class DeleteBasketRequest extends BaseBasketRequest
{
	public function authorize(): bool
	{
		return true;
	}

	protected function processValidatedValues(array $values, array $files): void
	{
	}

	/**
	 * Get the validation rules that apply to the request.
	 */
	public function rules(): array
	{
		return [];
	}
}
