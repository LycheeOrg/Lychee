<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Shop;

use App\Enum\OmnipayProviderType;
use App\Enum\PaymentStatusType;
use App\Enum\SizeVariantType;
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
		public ?string $shipping_street_name,
		public ?string $shipping_street_number,
		public ?string $shipping_additional_info,
		public ?string $shipping_city,
		public ?string $shipping_post_code,
		public ?string $shipping_country,
	) {
	}

	/**
	 * @return OrderResource
	 */
	public static function fromModel(Order $order): OrderResource
	{
		$money_service = resolve(MoneyService::class);

		// Load album and photo thumbnails for all orders (display purposes).
		// Load size_variant only for closed orders (download URL generation).
		$order->load([
			'items.album',
			'items.photo.size_variants' => fn ($q) => $q->whereIn('type', [
				SizeVariantType::SMALL,
				SizeVariantType::SMALL2X,
				SizeVariantType::THUMB,
				SizeVariantType::THUMB2X,
				SizeVariantType::PLACEHOLDER,
			]),
		]);
		if ($order->status === PaymentStatusType::CLOSED) {
			$order->load('items.size_variant');
		}

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
			shipping_street_name: $order->shipping_street_name,
			shipping_street_number: $order->shipping_street_number,
			shipping_additional_info: $order->shipping_additional_info,
			shipping_city: $order->shipping_city,
			shipping_post_code: $order->shipping_post_code,
			shipping_country: $order->shipping_country,
		);
	}
}
