<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Shop;

use App\Actions\Shop\Gateway\OrderCreatedResponse;
use App\Actions\Shop\Gateway\PaypalGateway;
use App\DTO\CheckoutDTO;
use App\Enum\OmnipayProviderType;
use App\Enum\PaymentStatusType;
use App\Exceptions\Internal\LycheeLogicException;
use App\Factories\OmnipayFactory;
use App\Models\Order;
use App\Services\MoneyService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Omnipay\Common\Exception\InvalidCreditCardException;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\GatewayInterface;
use Omnipay\Common\Message\NotificationInterface;
use Omnipay\Common\Message\RedirectResponseInterface;
use Omnipay\Common\Message\ResponseInterface;
use Omnipay\Dummy\Message\Response as DummyResponse;
use Omnipay\Mollie\Message\Response\FetchTransactionResponse;
use Omnipay\Payzum\Gateway as PayzumGateway;
use Omnipay\Payzum\Message\Response\FetchTransactionResponse as PayzumFetchTransactionResponse;
use Omnipay\Payzum\Message\Response\PurchaseResponse as PayzumPurchaseResponse;

/**
 * Service for handling checkout operations using Omnipay.
 */
class CheckoutService
{
	private GatewayInterface $gateway;

	/**
	 * CheckoutService constructor.
	 *
	 * @param OmnipayFactory $omnipay_factory
	 * @param MoneyService   $money_service
	 */
	public function __construct(
		private OmnipayFactory $omnipay_factory,
		private MoneyService $money_service,
	) {
	}

	/**
	 * Process an order payment.
	 *
	 * @param Order  $order           The order to processed
	 * @param string $return_url      The URL to return to after successful payment
	 * @param string $cancel_url      The URL to return to after canceled payment
	 * @param array  $additional_data Additional data for the payment gateway
	 *
	 * @return CheckoutDTO
	 */
	public function processPayment(Order $order, string $return_url, string $cancel_url, array $additional_data = []): CheckoutDTO
	{
		if (!$order->canProcessPayment()) {
			return new CheckoutDTO(
				is_success: false,
				message: 'Order cannot be checked out.',
			);
		}

		// Update the total amount before processing
		$order->updateTotal();

		/** @var OmnipayProviderType $provider : we can narrow it because we can process the payment */
		$provider = $order->provider;
		$this->gateway = $this->omnipay_factory->create_gateway($provider);

		// Prepare the purchase request parameters
		$params = $this->preparePurchaseParameters($order, $return_url, $cancel_url, $additional_data);

		try {
			// Update order status to processing
			$order->status = PaymentStatusType::PROCESSING;
			$order->save();

			// Create the purchase request
			$request = $this->gateway->purchase($params);

			// Send the purchase request
			/** @var ResponseInterface $response */
			$response = $request->send();

			// Handle the response
			if ($response->isRedirect()) {
				if ($response instanceof FetchTransactionResponse) {
					$metadata = $response->getMetadata();
					$reference = $response->getTransactionReference();
					$metadata['transactionReference'] = $reference;
					Session::put('metadata.' . $order->id, $metadata);
				}

				if ($response instanceof PayzumPurchaseResponse) {
					// Keep the gateway payment id so the return handler can
					// refresh the payment status while it confirms on-chain.
					Session::put('metadata.' . $order->id, ['transactionReference' => $response->getTransactionReference()]);
				}

				if (!$response instanceof RedirectResponseInterface) {
					throw new LycheeLogicException('Expected RedirectResponseInterface for redirect response.');
				}

				// Redirect to offsite payment gateway
				// Get the redirect URL using reflection
				$redirect_url = $response->getRedirectUrl();

				return new CheckoutDTO(
					is_success: true,
					is_redirect: true,
					redirect_url: $redirect_url,
				);
			} elseif ($response->isSuccessful()) {
				if ($response instanceof DummyResponse || $response instanceof OrderCreatedResponse) {
					// We need those metadata for later completion
					$metadata = [];
					$reference = $response->getTransactionReference();
					$metadata['transactionReference'] = $reference;
					Session::put('metadata.' . $order->id, $metadata);
				}

				// Payment was successful
				$order->transaction_id = $response->getTransactionReference();
				$order->save();

				return new CheckoutDTO(
					is_success: true,
					is_redirect: false,
				);
			}
			// Payment failed
			$order->status = PaymentStatusType::FAILED;
			$order->save();

			return new CheckoutDTO(
				is_success: false,
				message: $response->getMessage(),
			);
		} catch (\Exception|InvalidCreditCardException $e) {
			Log::error('Error processing payment: ' . $e->getMessage(), [
				'order_id' => $order->id,
				'transaction_id' => $order->transaction_id,
				'provider' => $provider->value,
				'exception' => $e,
			]);

			$order->status = PaymentStatusType::FAILED;
			$order->save();

			return new CheckoutDTO(
				is_success: false,
				message: 'An error occurred while processing the payment. Please try again later.',
			);
		}
	}

	/**
	 * Complete the payment process after a successful payment.
	 *
	 * @param Order             $order    The order being processed
	 * @param ResponseInterface $response The payment gateway response
	 *
	 * @return Order The updated order
	 */
	public function completePayment(Order $order, ResponseInterface $response): Order
	{
		$transaction_id = $response->getTransactionReference();
		$this->settle($order, $transaction_id);

		return $order;
	}

	/**
	 * Mark an order as paid, at most once.
	 *
	 * The browser return and an inbound payment notification can arrive at the
	 * same moment, and both would otherwise observe PROCESSING and settle the
	 * order — dispatching OrderCompleted (and thus fulfilling) twice. The row
	 * is locked and re-read inside a transaction, so exactly one caller
	 * performs the transition; the loser sees the fresh state and reports that
	 * it changed nothing.
	 *
	 * @param Order  $order          The order to settle
	 * @param string $transaction_id The reference to store on the order
	 *
	 * @return bool Whether THIS call completed the order
	 */
	private function settle(Order $order, string $transaction_id): bool
	{
		return DB::transaction(function () use ($order, $transaction_id): bool {
			$fresh = Order::query()->whereKey($order->getKey())->lockForUpdate()->first();

			if ($fresh === null) {
				return false;
			}

			if (in_array($fresh->status, [PaymentStatusType::COMPLETED, PaymentStatusType::CLOSED], true)) {
				// Someone else settled it first; adopt their state without
				// claiming the transition.
				$order->refresh();

				return false;
			}

			// Saved through the caller's instance, while this transaction holds
			// the row lock, so wasChanged('status') is true for the winner only.
			$order->markAsPaid($transaction_id);

			return true;
		});
	}

	/**
	 * Handle the return from the payment gateway.
	 *
	 * @param Order               $order    The order being processed
	 * @param OmnipayProviderType $provider The payment provider used
	 *
	 * @return Order|null The updated order if found, null otherwise
	 */
	public function handlePaymentReturn(Order $order, OmnipayProviderType $provider): ?Order
	{
		$metadata = Session::get('metadata.' . $order->id, []);
		Log::info('Payment return metadata', ['metadata' => $metadata]);

		$gateway = $this->omnipay_factory->create_gateway($provider);

		if ($provider === OmnipayProviderType::PAYZUM && $gateway instanceof PayzumGateway) {
			return $this->handleAsyncPaymentReturn($order, $gateway, $metadata);
		}

		try {
			if ($order->status !== PaymentStatusType::PROCESSING) {
				throw new LycheeLogicException('Order with invalid status.');
			}
			$response = $gateway->completePurchase($metadata)->send();
			if ($response->isSuccessful()) {
				return $this->completePayment($order, $response);
			}
			Log::warning('Payment was not successful for order ' . $order->transaction_id);
		} catch (\Exception $e) {
			Log::error('Error handling payment return: ' . $e->getMessage(), [
				'provider' => $provider,
				'exception' => $e,
			]);
		}
		$order->status = PaymentStatusType::FAILED;
		$order->save();

		return $order;
	}

	/**
	 * Handle the buyer's return for asynchronous providers (crypto settles
	 * on-chain, usually after the redirect back).
	 *
	 * The order is only ever completed from a verified source: either the
	 * signed payment notification (handlePaymentNotification) or the
	 * status refresh below. A payment that is still confirming stays in
	 * PROCESSING — it must never be marked FAILED just because the buyer
	 * returned before the chain did.
	 *
	 * @param Order         $order    The order being processed
	 * @param PayzumGateway $gateway  The initialized gateway
	 * @param array         $metadata Session metadata stored at purchase time
	 *
	 * @return Order The refreshed order
	 */
	private function handleAsyncPaymentReturn(Order $order, PayzumGateway $gateway, array $metadata): Order
	{
		if ($order->status !== PaymentStatusType::PROCESSING) {
			// Already settled, e.g. the notification landed before the redirect.
			return $order;
		}

		$transaction_reference = $metadata['transactionReference'] ?? null;
		if (!is_string($transaction_reference) || $transaction_reference === '') {
			// Nothing to poll (e.g. session lost): the signed notification
			// will complete the order server-side.
			return $order;
		}

		try {
			$response = $gateway->fetchTransaction(['transactionReference' => $transaction_reference])->send();

			if ($response->isSuccessful()) {
				// Same reasoning as in handlePaymentNotification(): the order's
				// own transaction id has to stay the stable lookup key.
				$this->settle($order, $order->transaction_id);

				return $order;
			}

			if ($response instanceof PayzumFetchTransactionResponse && ($response->isExpired() || $response->isCancelled())) {
				$order->status = PaymentStatusType::FAILED;
				$order->save();
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
	 * Complete an order from a signed Payzum payment notification.
	 *
	 * The Omnipay driver verifies the HMAC-SHA-512 signature over the raw
	 * request bytes (with a replay window) before any payload field is
	 * readable — a forged, stale, or malformed delivery aborts with 400
	 * without touching the order. Deliveries are retried by the gateway,
	 * so a redelivered notification for an already-completed order is a
	 * no-op.
	 *
	 * @param Order $order The order the notification URL points at
	 *
	 * @return Order The updated order
	 */
	public function handlePaymentNotification(Order $order): Order
	{
		// The current request is passed explicitly: the driver verifies the
		// notification signature against its raw body and headers.
		$gateway = $this->omnipay_factory->create_notification_gateway(OmnipayProviderType::PAYZUM, request());
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
			// Checked before the reference comparison below, because completing
			// the order replaced its transaction id with the provider reference.
			return $order;
		}

		if ($notification->getTransactionId() !== $order->transaction_id) {
			Log::warning('Payzum notification order mismatch.', ['order_id' => $order->id]);
			abort(400, 'Order mismatch');
		}

		if ($status === NotificationInterface::STATUS_FAILED) {
			// The invoice expired or failed before full payment arrived.
			$order->status = PaymentStatusType::FAILED;
			$order->save();

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
		// reference: that id is the lookup key of both the return and the
		// notification URLs, and replacing it would make every later request
		// for this order — including the buyer's browser return after an
		// early notification — fail to resolve. The Payzum invoice stays
		// reachable by it, since their API reads an invoice by payment id or
		// by order id.
		$this->settle($order, $order->transaction_id);

		return $order;
	}

	/**
	 * Whether a notification reports the exact amount and currency of the order.
	 *
	 * Compared as Money objects rather than floats, so the check is exact.
	 *
	 * @param array $payload The verified notification payload
	 * @param Order $order   The order the notification is about
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

	/**
	 * Prepare parameters for the purchase request.
	 *
	 * @param Order  $order           The order being processed
	 * @param string $return_url      The return URL after successful payment
	 * @param string $cancel_url      The cancel URL after failed payment
	 * @param array  $additional_data Additional data for the payment gateway
	 *
	 * @return array
	 */
	private function preparePurchaseParameters(Order $order, string $return_url, string $cancel_url, array $additional_data = []): array
	{
		// This is slightly different for Paypal
		if ($this->gateway instanceof PaypalGateway) {
			return $this->gateway->getOrderDetails($order);
		}

		$amount = $this->money_service->toDecimal($order->amount_cents);
		$currency = $order->amount_cents->getCurrency()->getCode();

		$params = [
			'amount' => $amount,
			'currency' => $currency,
			'returnUrl' => $return_url,
			'cancelUrl' => $cancel_url,
			'transactionId' => $order->transaction_id,
			'description' => 'Order #' . $order->id,
		];

		if ($this->gateway instanceof PayzumGateway) {
			// Crypto settles asynchronously: the signed notification posted to
			// this URL is what completes the order, not the browser return.
			$params['notifyUrl'] = route('shop.checkout.notify', ['order_id' => $order->id]);
		}

		// Add customer details if available
		if ($order->email !== null) {
			$params['email'] = $order->email;
		}

		if ($order->user !== null) {
			$params['name'] = $order->user->name;
		}

		// Remove any keys from $additional_data that are already in $params
		// This ensures that $additional_data does not overwrite existing keys in $params
		// Fixes that sneaky @5ud0er ;p
		$param_keys = array_keys($params);
		foreach ($param_keys as $key) {
			if (array_key_exists($key, $additional_data)) {
				unset($additional_data[$key]);
			}
		}

		// Merge any additional data
		// We could flip the order of the merge, but we want to make sure that the
		// additional data does not overwrite existing keys in $params
		// Better be clear than trying to be smart.
		return array_merge($params, $additional_data);
	}
}
