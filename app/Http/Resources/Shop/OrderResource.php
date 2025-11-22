<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Shop;

use App\Enum\OmnipayProviderType;
use App\Enum\PaymentStatusType;
use App\Models\Order;
use App\Services\MoneyService;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class OrderResource extends Data
{
	/**
	 * @param Collection<int,OrderItemResource>|null $items
	 */
	public function __construct(
		public int $id,
		public ?OmnipayProviderType $provider,
		public string $transaction_id,
		public ?string $username,
		public ?string $email,
		public PaymentStatusType $status,
		public string $amount,
		// public string $currency,
		public ?string $paid_at,
		public ?string $created_at,
		public ?string $updated_at,
		public ?string $comment,
		#[LiteralTypeScriptType('App.Http.Resources.Shop.OrderItemResource[]|null')]
		public ?Collection $items,
		public bool $can_process_payment,
	) {
	}

	/**
	 * @return OrderResource
	 */
	public static function fromModel(Order $order): OrderResource
	{
		$money_service = resolve(MoneyService::class);

		return new self(
			id: $order->id,
			provider: $order->provider,
			transaction_id: $order->transaction_id,
			username: $order->user?->name,
			email: $order->email,
			status: $order->status,
			amount: $money_service->format($order->amount_cents),
			paid_at: $order->paid_at?->toIso8601String(),
			created_at: $order->created_at?->toIso8601String(),
			updated_at: $order->updated_at?->toIso8601String(),
			comment: $order->comment,
			items: $order->relationLoaded('items') ? OrderItemResource::collect($order->items) : null,
			can_process_payment: $order->relationLoaded('items') ? $order->canProcessPayment() : false, // only if items are loaded we are able to check this.
		);
	}
}
