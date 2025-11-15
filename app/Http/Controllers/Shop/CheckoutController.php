<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Shop;

use App\Actions\Shop\CheckoutService;
use App\Enum\PaymentStatusType;
use App\Http\Requests\Checkout\CreateSessionRequest;
use App\Http\Requests\Checkout\FinalizeRequest;
use App\Http\Requests\Checkout\OfflineRequest;
use App\Http\Requests\Checkout\ProcessRequest;
use App\Http\Resources\Shop\CheckoutOptionResource;
use App\Http\Resources\Shop\CheckoutResource;
use App\Http\Resources\Shop\OrderResource;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class CheckoutController extends Controller
{
	/**
	 * Constructor.
	 *
	 * @param CheckoutService $checkout_service The checkout service
	 */
	public function __construct(
		private CheckoutService $checkout_service,
	) {
	}

	/**
	 * Get checkout configuration options.
	 * This returns the configuration settings needed for the checkout process.
	 *
	 * @return CheckoutOptionResource The checkout configuration options
	 */
	public function options(): CheckoutOptionResource
	{
		return new CheckoutOptionResource();
	}

	/**
	 * Create a checkout session for the current order.
	 * This prepares the order for payment processing.
	 *
	 * @param CreateSessionRequest $request The request containing session information
	 *
	 * @return OrderResource The updated order
	 */
	public function createSession(CreateSessionRequest $request): OrderResource
	{
		$order = $request->basket();

		// Set payment provider from request
		$order->provider = $request->provider;

		// Add email if provided
		if ($request->email !== null) {
			$order->email = $request->email;
		}

		// Save changes to order
		$order->save();

		return OrderResource::fromModel($order);
	}

	/**
	 * Process payment for the order.
	 * This initiates the payment process with the selected provider.
	 *
	 * @param ProcessRequest $request The request containing payment details
	 *
	 * @return CheckoutResource The payment response with redirect URL if needed
	 */
	public function process(ProcessRequest $request): CheckoutResource
	{
		$order = $request->basket();

		// Generate return URLs for the payment provider
		$return_url = URL::route('shop.checkout.return', ['provider' => $order->provider->value, 'transaction_id' => $order->transaction_id]);
		$cancel_url = URL::route('shop.checkout.cancel', ['provider' => $order->provider->value, 'transaction_id' => $order->transaction_id]);

		// Process the payment
		$result = $this->checkout_service->processPayment(
			$order,
			$return_url,
			$cancel_url,
			$request->additional_data ?? []
		);

		return new CheckoutResource(
			is_success: $result->is_success,
			is_redirect: $result->is_redirect,
			redirect_url: $result->redirect_url,
			message: $result->message ?? '',
		);
	}

	/**
	 * Finalize the payment process after return from the payment provider.
	 *
	 * @param FinalizeRequest $request The request containing return data from the payment provider
	 *
	 * @return CheckoutResource The finalization response
	 */
	public function finalize(FinalizeRequest $request, string $provider, string $transaction_id): CheckoutResource
	{
		/** @disregard P1013 */
		Log::warning("Finalize payment for provider {$provider} and transaction ID {$transaction_id}", $request->all());
		/** @disregard P1013 */
		$order = $this->checkout_service->handlePaymentReturn($request->basket(), $request->provider_type());

		if ($order->status !== PaymentStatusType::COMPLETED) {
			return new CheckoutResource(
				is_success: false,
				message: 'Order failed.',
			);
		}

		return new CheckoutResource(
			is_success: true,
			message: 'Payment completed successfully',
			order: OrderResource::fromModel($order),
		);
	}

	/**
	 * Handle cancellation of the payment process.
	 *
	 * @return CheckoutResource The cancellation response
	 */
	public function cancel(FinalizeRequest $request): CheckoutResource
	{
		$order = $request->basket();

		// Mark the order as cancelled
		$order->status = PaymentStatusType::CANCELLED;
		$order->save();

		return new CheckoutResource(
			is_success: true,
			message: 'Payment was canceled by the user',
			order: OrderResource::fromModel($order),
		);
	}

	/**
	 * Handle offline order completion.
	 *
	 * @param OfflineRequest $request
	 *
	 * @return CheckoutResource
	 */
	public function offline(OfflineRequest $request): CheckoutResource
	{
		$order = $request->basket();

		// Add email if provided
		if ($request->email !== null) {
			$order->email = $request->email;
		}

		if ($order->email === null || $order->email === '') {
			return new CheckoutResource(
				is_success: false,
				message: 'Email is required for offline orders.',
				order: OrderResource::fromModel($order),
			);
		}

		// Mark the order as completed (offline)
		$order->status = PaymentStatusType::OFFLINE;
		$order->save();

		return new CheckoutResource(
			is_success: true,
			message: 'Order marked as completed (offline)',
			order: OrderResource::fromModel($order),
		);
	}
}
