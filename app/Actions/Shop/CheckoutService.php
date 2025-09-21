<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Shop;

use App\DTO\CheckoutDTO;
use App\Enum\OmnipayProviderType;
use App\Enum\PaymentStatusType;
use App\Exceptions\Internal\LycheeLogicException;
use App\Factories\OmnipayFactory;
use App\Models\Order;
use App\Services\MoneyService;
use Illuminate\Support\Facades\Log;
use Omnipay\Common\Exception\InvalidCreditCardException;
use Omnipay\Common\Message\RedirectResponseInterface;
use Omnipay\Common\Message\ResponseInterface;

/**
 * Service for handling checkout operations using Omnipay.
 */
class CheckoutService
{
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
		$gateway = $this->omnipay_factory->create_gateway($provider);

		// Prepare the purchase request parameters
		$params = $this->preparePurchaseParameters($order, $return_url, $cancel_url, $additional_data);

		try {
			// Update order status to processing
			$order->status = PaymentStatusType::PROCESSING;
			$order->save();

			// Create the purchase request
			$request = $gateway->purchase($params);

			// Send the purchase request
			/** @var ResponseInterface $response */
			$response = $request->send();

			// Handle the response
			if ($response->isRedirect()) {
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
				// Payment was successful
				$this->completePayment($order, $response);

				return new CheckoutDTO(
					is_success: true,
					is_redirect: false,
					redirect_url: $return_url,
				);
			} else {
				// Payment failed
				$order->status = PaymentStatusType::FAILED;
				$order->save();

				return new CheckoutDTO(
					is_success: false,
					message: $response->getMessage(),
				);
			}
		} catch (\Exception|InvalidCreditCardException $e) {
			// dd($e);
			// TODO: later do better error management
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
		$order->markAsPaid($transaction_id);

		return $order;
	}

	/**
	 * Handle the return from the payment gateway.
	 *
	 * @param Order               $order        The order being processed
	 * @param array               $request_data The request data from the payment gateway
	 * @param OmnipayProviderType $provider     The payment provider used
	 *
	 * @return Order|null The updated order if found, null otherwise
	 */
	public function handlePaymentReturn(Order $order, array $request_data, OmnipayProviderType $provider): ?Order
	{
		$gateway = $this->omnipay_factory->create_gateway($provider);

		try {
			if ($order->status !== PaymentStatusType::PROCESSING) {
				throw new LycheeLogicException('Order with invalid status.');
			}

			$response = $gateway->completePurchase($request_data)->send();
			if ($response->isSuccessful()) {
				return $this->completePayment($order, $response);
			} else {
				Log::warning('Payment was not successful for order ' . $order->transaction_id);
			}
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

		// Add customer details if available
		if ($order->email !== null) {
			$params['email'] = $order->email;
		}

		if ($order->user !== null) {
			$params['name'] = $order->user->name;
		}

		// Merge any additional data
		return array_merge($params, $additional_data);
	}
}
