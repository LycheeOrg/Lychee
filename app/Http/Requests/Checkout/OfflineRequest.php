<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Checkout;

use App\Contracts\Http\Requests\HasBasket;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasBasketTrait;

class OfflineRequest extends BaseApiRequest implements HasBasket
{
	use HasBasketTrait;

	public ?string $email;

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize(): bool
	{
		return $this->order !== null && $this->order->canCheckout() && $this->configs()->getValueAsBool('webshop_offline');
	}

	public function rules(): array
	{
		return [
			RequestAttribute::EMAIL_ATTRIBUTE => ['sometimes', 'nullable', 'email'],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->prepareBasket();

		$this->email = $values[RequestAttribute::EMAIL_ATTRIBUTE] ?? null;
	}
}
