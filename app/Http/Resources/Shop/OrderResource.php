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
		public ?int $user_id,
		public ?string $email,
		public PaymentStatusType $status,
		public string $amount,
		// public string $currency,
		public ?string $payed_at,
		public ?string $created_at,
		public ?string $comment,
		#[LiteralTypeScriptType('App.Http.Resources.Shop.OrderItemResource[]|null')]
		public ?Collection $items,
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
			user_id: $order->user_id,
			email: $order->email,
			status: $order->status,
			amount: $money_service->format($order->amount_cents),
			payed_at: $order->payed_at?->toIso8601String(),
			created_at: $order->created_at?->toIso8601String(),
			comment: $order->comment,
			items: $order->items === null ? null : OrderItemResource::collect($order->items),
		);
	}
}
