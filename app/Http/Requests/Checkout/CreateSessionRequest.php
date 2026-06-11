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
	public ?string $shipping_street_name;
	public ?string $shipping_street_number;
	public ?string $shipping_additional_info;
	public ?string $shipping_city;
	public ?string $shipping_post_code;
	public ?string $shipping_country;

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
			'shipping_street_name' => ['sometimes', 'nullable', 'string', 'max:255'],
			'shipping_street_number' => ['sometimes', 'nullable', 'string', 'max:50'],
			'shipping_additional_info' => ['sometimes', 'nullable', 'string', 'max:255'],
			'shipping_city' => ['sometimes', 'nullable', 'string', 'max:255'],
			'shipping_post_code' => ['sometimes', 'nullable', 'string', 'max:20'],
			'shipping_country' => ['sometimes', 'nullable', 'string', 'size:2'],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->prepareBasket();
		$this->email = $values[RequestAttribute::EMAIL_ATTRIBUTE] ?? null;
		$this->provider = isset($values[RequestAttribute::PROVIDER_ATTRIBUTE]) ? OmnipayProviderType::from($values[RequestAttribute::PROVIDER_ATTRIBUTE]) : null;
		$this->shipping_street_name = $values['shipping_street_name'] ?? null;
		$this->shipping_street_number = $values['shipping_street_number'] ?? null;
		$this->shipping_additional_info = $values['shipping_additional_info'] ?? null;
		$this->shipping_city = $values['shipping_city'] ?? null;
		$this->shipping_post_code = $values['shipping_post_code'] ?? null;
		$this->shipping_country = $values['shipping_country'] ?? null;
	}
}
