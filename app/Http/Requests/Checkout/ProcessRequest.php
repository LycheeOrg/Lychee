<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Checkout;

use App\Contracts\Http\Requests\HasBasket;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasBasketTrait;

class ProcessRequest extends BaseApiRequest implements HasBasket
{
	use HasBasketTrait;

	public array $additional_data = [];

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize(): bool
	{
		return $this->order?->canProcessPayment() ?? false;
	}

	public function rules(): array
	{
		return [
			'additional_data' => ['sometimes', 'array'],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->prepareBasket();

		$this->additional_data = $values['additional_data'] ?? [];
	}
}
