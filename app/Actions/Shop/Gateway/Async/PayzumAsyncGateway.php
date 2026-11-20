<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Shop\Gateway\Async;

use App\Actions\Shop\OrderSettlement;
use App\Contracts\Shop\AsyncPaymentGateway;
use App\Enum\OmnipayProviderType;
use App\Enum\PaymentStatusType;
use App\Exceptions\Internal\LycheeLogicException;
use App\Factories\OmnipayFactory;
use App\Models\Order;
use App\Services\MoneyService;
use Illuminate\Support\Facades\Log;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\NotificationInterface;
use Omnipay\Common\Message\ResponseInterface;
use Omnipay\Payzum\Gateway as PayzumGateway;
use Omnipay\Payzum\Message\Response\FetchTransactionResponse as PayzumFetchTransactionResponse;
use Omnipay\Payzum\Message\Response\PurchaseResponse as PayzumPurchaseResponse;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

/**
 * Asynchronous payment flow for Payzum (crypto / stablecoins).
 *
 * Crypto settles on-chain, usually after the buyer's redirect back, so the
 * order is only ever completed from a verified source: the signed payment
 * notification, or a status read back from the gateway. Everything
 * Payzum-specific lives here; CheckoutService only knows the
 * {@see AsyncPaymentGateway} contract.
 */
class PayzumAsyncGateway implements AsyncPaymentGateway
{
	/**
	 * @param OmnipayFactory  $omnipay_factory
	 * @param MoneyService    $money_service
	 * @param OrderSettlement $settlement
	 */
	public function __construct(
		private OmnipayFactory $omnipay_factory,
		private MoneyService $money_service,
		private OrderSettlement $settlement,
	) {
	}

	/**
	 * {@inheritDoc}
	 */
	public function provider(): OmnipayProviderType
	{
		return OmnipayProviderType::PAYZUM;
	}

	/**
	 * {@inheritDoc}
	 */
	public function purchaseParameters(Order $order): array
	{
		// The signed notification posted to this URL is what completes the
		// order, not the browser return. Keyed by order id: that key never
		// changes for the lifetime of the order.
		return [
			'notifyUrl' => route('shop.checkout.notify', [
				'provider' => OmnipayProviderType::PAYZUM->value,
				'order_id' => $order->id,
			]),
		];
	}

	/**
	 * {@inheritDoc}
	 */
	public function collectReturnMetadata(ResponseInterface $response): ?array
	{
		if ($response instanceof PayzumPurchaseResponse) {
			// Keep the gateway payment id so the return handler can refresh
			// the payment status while it confirms on-chain.
			return ['transactionReference' => $response->getTransactionReference()];
		}

		return null;
	}

	/**
	 * {@inheritDoc}
	 *
	 * A payment that is still confirming stays in PROCESSING — it must never
	 * be marked FAILED just because the buyer returned before the chain did.
	 */
	public function handleReturn(Order $order, array $metadata): Order
	{
		return match ($order->status) {
			PaymentStatusType::PROCESSING => $this->refreshFromGateway($order, $metadata),
			// The signed notification settled the order before the browser
			// returned; the return page shows the completed order.
			PaymentStatusType::COMPLETED, PaymentStatusType::CLOSED => $order,
			// FinalizeRequest only admits PROCESSING or COMPLETED for
			// asynchronous providers, so anything else is a logic error.
			default => throw new LycheeLogicException('Order with invalid status.'),
		};
	}

	/**
	 * {@inheritDoc}
	 *
	 * The Omnipay driver verifies the HMAC-SHA-512 signature over the raw
	 * request bytes (with a replay window) before any payload field is
	 * readable — a forged, stale, or malformed delivery aborts with 400
	 * without touching the order. Deliveries are retried by the gateway,
	 * so a redelivered notification for an already-completed order is a
	 * no-op.
	 */
	public function handleNotification(Order $order, HttpRequest $request): Order
	{
		// The current request is passed explicitly: the driver verifies the
		// notification signature against its raw body and headers.
		$gateway = $this->omnipay_factory->create_notification_gateway(OmnipayProviderType::PAYZUM, $request);
		if (!$gateway instanceof PayzumGateway) {
			throw new LycheeLogicException('Expected Payzum gateway.');
		}

		$notification = $gateway->acceptNotification();

		try {
			// Accessors verify the signature on first use.
			$status = $notification->getTransactionStatus();
		} catch (InvalidRequestException $e) {
			Log::warning('Rejected Payzum notification: ' . $e->getMessage(), ['order_id' => $order->id]);
			abort(400, 'Invalid notification');
		}

		if ($order->status === PaymentStatusType::COMPLETED || $order->status === PaymentStatusType::CLOSED) {
			// Redelivered notification: acknowledge without a second fulfilment.
			return $order;
		}

		if ($notification->getTransactionId() !== $order->transaction_id) {
			Log::warning('Payzum notification order mismatch.', ['order_id' => $order->id]);
			abort(400, 'Order mismatch');
		}

		if ($status === NotificationInterface::STATUS_FAILED) {
			// The invoice expired or failed before full payment arrived.
			// Locked transition: must never downgrade a concurrent settlement.
			$this->settlement->fail($order);

			return $order;
		}

		if ($status !== NotificationInterface::STATUS_COMPLETED) {
			// Pending or confirming: acknowledge without touching the order.
			return $order;
		}

		$payload = $notification->getData();

		if (!$this->isNotifiedAmountExpected($payload, $order)) {
			Log::warning('Payzum notification amount/currency mismatch.', ['order_id' => $order->id]);
			abort(400, 'Amount mismatch');
		}

		if ($notification->getTransactionReference() === null) {
			abort(400, 'Missing payment reference');
		}

		// Settled with the order's own transaction id rather than the gateway
		// reference: that id is the lookup key of the return URL for the
		// order's whole life, and replacing it would make the buyer's browser
		// return after an early notification fail to resolve. The Payzum
		// invoice stays reachable by it, since their API reads an invoice by
		// payment id or by order id.
		$this->settlement->settle($order, $order->transaction_id);

		return $order;
	}

	/**
	 * Read the payment status back from the gateway while it confirms.
	 *
	 * @param Order               $order    The order being processed
	 * @param array<string,mixed> $metadata Session metadata stored at purchase time
	 *
	 * @return Order The refreshed order
	 */
	private function refreshFromGateway(Order $order, array $metadata): Order
	{
		$transaction_reference = $metadata['transactionReference'] ?? null;
		if (!is_string($transaction_reference) || $transaction_reference === '') {
			// Nothing to poll (e.g. session lost): the signed notification
			// will complete the order server-side.
			return $order;
		}

		$gateway = $this->omnipay_factory->create_gateway(OmnipayProviderType::PAYZUM);
		if (!$gateway instanceof PayzumGateway) {
			throw new LycheeLogicException('Expected Payzum gateway.');
		}

		try {
			$response = $gateway->fetchTransaction(['transactionReference' => $transaction_reference])->send();

			if ($response->isSuccessful()) {
				// Same reasoning as in handleNotification(): the order's own
				// transaction id has to stay the stable lookup key.
				$this->settlement->settle($order, $order->transaction_id);

				return $order;
			}

			if ($response instanceof PayzumFetchTransactionResponse && ($response->isExpired() || $response->isCancelled())) {
				// Locked transition: a notification settling the order at this
				// exact moment must win over the stale poll result.
				$this->settlement->fail($order);
			}
			// Still pending or confirming: leave the order in PROCESSING.
		} catch (\Exception $e) {
			Log::error('Error refreshing async payment status: ' . $e->getMessage(), [
				'order_id' => $order->id,
				'exception' => $e,
			]);
			// Leave the order in PROCESSING; the notification stays authoritative.
		}

		return $order;
	}

	/**
	 * Whether a notification reports the exact amount and currency of the order.
	 *
	 * Compared as Money objects rather than floats, so the check is exact.
	 *
	 * @param array<string,mixed> $payload The verified notification payload
	 * @param Order               $order   The order the notification is about
	 *
	 * @return bool
	 */
	private function isNotifiedAmountExpected(array $payload, Order $order): bool
	{
		$notified_amount = $payload['price_amount'] ?? null;
		$notified_currency = $payload['price_currency'] ?? null;

		if (!is_string($notified_amount) && !is_numeric($notified_amount)) {
			return false;
		}

		if (!is_string($notified_currency)) {
			return false;
		}

		$expected_currency = $order->amount_cents->getCurrency()->getCode();
		if (strtoupper($notified_currency) !== strtoupper($expected_currency)) {
			return false;
		}

		try {
			$notified = $this->money_service->createFromDecimal((string) $notified_amount, $expected_currency);
		} catch (\Exception) {
			return false;
		}

		return $notified->equals($order->amount_cents);
	}
}
