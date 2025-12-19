<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Shop;

use App\Actions\Shop\CheckoutService;
use App\Enum\OmnipayProviderType;
use App\Enum\PaymentStatusType;
use App\Events\OrderCompleted;
use App\Http\Requests\Checkout\CancelRequest;
use App\Http\Requests\Checkout\CreateSessionRequest;
use App\Http\Requests\Checkout\FinalizeRequest;
use App\Http\Requests\Checkout\OfflineRequest;
use App\Http\Requests\Checkout\ProcessRequest;
use App\Http\Resources\Shop\CheckoutOptionResource;
use App\Http\Resources\Shop\CheckoutResource;
use App\Http\Resources\Shop\OrderResource;
use App\Models\Configs;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
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
		$cancel_url = URL::route('shop.checkout.cancel', ['transaction_id' => $order->transaction_id]);

		// Process the payment
		$result = $this->checkout_service->processPayment(
			$order,
			$return_url,
			$cancel_url,
			$request->additional_data ?? []
		);

		if ($result->is_success && !$result->is_redirect) {
			// This is a success we now need to complete the order.
			$order->refresh();

			return new CheckoutResource(
				is_success: true,
				is_redirect: false,
				complete_url: URL::route('shop.checkout.return', ['provider' => $order->provider->value, 'transaction_id' => $order->transaction_id]),
				message: '',
				order: OrderResource::fromModel($order),
			);
		}

		return new CheckoutResource(
			is_success: $result->is_success,
			is_redirect: $result->is_redirect,
			redirect_url: $result->redirect_url,
			message: $result->message ?? '',
			order: OrderResource::fromModel($order),
		);
	}

	/**
	 * Finalize the payment process after return from the payment provider.
	 *
	 * @param FinalizeRequest $request The request containing return data from the payment provider
	 *
	 * @return RedirectResponse|CheckoutResource The redirection response or checkout resource (PayPal)
	 */
	public function finalize(FinalizeRequest $request, string $provider, string $transaction_id): RedirectResponse|CheckoutResource
	{
		/** @disregard P1013 */
		$order = $this->checkout_service->handlePaymentReturn($request->basket(), $request->provider_type());

		$success = $order->status === PaymentStatusType::COMPLETED;
		$complete_url = null;
		$redirect_url = route('shop.checkout.failed');
		$message = 'Payment failed or was not completed.';

		if ($success) {
			OrderCompleted::dispatchIf(Configs::getValueAsBool('webshop_auto_fulfill_enabled'), $order->id);
			$complete_url = URL::route('shop.checkout.complete');
			$redirect_url = null;
			$message = 'Payment completed successfully.';
		}

		if ($order->provider === OmnipayProviderType::PAYPAL) {
			return new CheckoutResource(
				is_success: $success,
				complete_url: $complete_url,
				redirect_url: $redirect_url,
				message: $message,
				order: OrderResource::fromModel($order),
			);
		}

		if (!$success) {
			return redirect()->route('shop.checkout.failed');
		}

		return redirect()->route('shop.checkout.complete');
	}

	/**
	 * Handle cancellation of the payment process.
	 *
	 * @return RedirectResponse|CheckoutResource The cancellation response
	 */
	public function cancel(CancelRequest $request): RedirectResponse|CheckoutResource
	{
		$order = $request->basket();

		// Mark the order as cancelled
		$order->status = PaymentStatusType::CANCELLED;
		$order->save();

		if ($order->provider === OmnipayProviderType::PAYPAL) {
			return new CheckoutResource(
				is_success: true,
				is_redirect: false,
				redirect_url: route('shop.checkout.cancelled'),
				message: 'cancelled by user',
				order: OrderResource::fromModel($order),
			);
		}

		return redirect()->route('shop.checkout.cancelled');
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
