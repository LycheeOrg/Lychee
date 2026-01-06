<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Checkout;

use App\Contracts\Http\Requests\HasBasket;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Enum\OmnipayProviderType;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasBasketTrait;
use Illuminate\Validation\Rules\Enum;

class CreateSessionRequest extends BaseApiRequest implements HasBasket
{
	use HasBasketTrait;

	public ?string $email;
	public ?OmnipayProviderType $provider;

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize(): bool
	{
		return $this->order !== null && $this->order->canCheckout();
	}

	public function rules(): array
	{
		return [
			RequestAttribute::PROVIDER_ATTRIBUTE => ['sometimes', new Enum(OmnipayProviderType::class)],
			RequestAttribute::EMAIL_ATTRIBUTE => ['sometimes', 'email'],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->prepareBasket();
		$this->email = $values[RequestAttribute::EMAIL_ATTRIBUTE] ?? null;
		$this->provider = isset($values[RequestAttribute::PROVIDER_ATTRIBUTE]) ? OmnipayProviderType::from($values[RequestAttribute::PROVIDER_ATTRIBUTE]) : null;
	}
}
