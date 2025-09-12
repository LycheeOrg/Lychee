<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Checkout;

use App\Contracts\Http\Requests\HasBasket;
use App\Enum\OmnipayProviderType;
use App\Enum\PaymentStatusType;
use App\Exceptions\Internal\LycheeLogicException;
use App\Http\Requests\BaseApiRequest;
use App\Models\Order;

/**
 * Fetched from the url.
 *
 * @property string $transaction_id
 * @property string $provider
 */
class FinalizeRequest extends BaseApiRequest implements HasBasket
{
	protected ?OmnipayProviderType $provider_type = null;
	protected ?Order $order = null;

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize(): bool
	{
		return $this->order?->status === PaymentStatusType::PROCESSING && $this->order?->provider === $this->provider_type && $this->provider_type !== null;
	}

	/**
	 * Get the validation rules that apply to the request.
	 */
	public function rules(): array
	{
		return [];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		if (!isset($this->transaction_id)) {
			throw new LycheeLogicException('transaction_id is not set.');
		}

		if (!isset($this->provider)) {
			throw new LycheeLogicException('provider is not set.');
		}

		$this->order = Order::findByTransactionId($this->transaction_id);
		$this->provider_type = OmnipayProviderType::tryFrom($this->provider);
	}

	public function basket(): ?Order
	{
		return $this->order;
	}

	public function provider_type(): ?OmnipayProviderType
	{
		return $this->provider_type;
	}
}
