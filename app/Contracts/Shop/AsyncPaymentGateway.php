<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Contracts\Shop;

use App\Enum\OmnipayProviderType;
use App\Models\Order;
use Omnipay\Common\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

/**
 * Strategy for payment providers that settle asynchronously.
 *
 * Card providers confirm while the buyer waits, so the browser return is
 * enough to complete the order. Asynchronous providers (crypto confirming
 * on-chain, bank transfers, ...) settle after the redirect: the order is
 * completed from a signed server-to-server notification, and the browser
 * return only reflects whatever state the order is in.
 *
 * Implementations own every provider-specific detail of that flow, so that
 * CheckoutService stays provider-agnostic. Register new implementations in
 * {@see \App\Actions\Shop\Gateway\Async\AsyncPaymentRegistry}.
 */
interface AsyncPaymentGateway
{
	/**
	 * The provider this strategy handles.
	 *
	 * @return OmnipayProviderType
	 */
	public function provider(): OmnipayProviderType;

	/**
	 * Extra parameters for the purchase request, e.g. the URL the provider
	 * must post its signed payment notification to.
	 *
	 * @param Order $order The order being purchased
	 *
	 * @return array<string,mixed>
	 */
	public function purchaseParameters(Order $order): array;

	/**
	 * Metadata to remember in the session for the buyer's return, or null
	 * when the response carries nothing this provider needs later.
	 *
	 * @param ResponseInterface $response The purchase response
	 *
	 * @return array<string,mixed>|null
	 */
	public function collectReturnMetadata(ResponseInterface $response): ?array;

	/**
	 * Handle the buyer's browser return from the hosted checkout.
	 *
	 * Must never complete the order from the return alone: completion comes
	 * from the signed notification or from a status read back from the
	 * provider. A payment that is still confirming stays in PROCESSING.
	 *
	 * @param Order               $order    The order being returned to
	 * @param array<string,mixed> $metadata Session metadata stored at purchase time
	 *
	 * @return Order The refreshed order
	 */
	public function handleReturn(Order $order, array $metadata): Order;

	/**
	 * Advance the order from a signed server-to-server payment notification.
	 *
	 * Implementations must verify the notification cryptographically before
	 * reading any of its fields, and must treat redeliveries as no-ops.
	 *
	 * @param Order       $order   The order the notification URL points at
	 * @param HttpRequest $request The request carrying the signed notification
	 *
	 * @return Order The updated order
	 */
	public function handleNotification(Order $order, HttpRequest $request): Order;
}
