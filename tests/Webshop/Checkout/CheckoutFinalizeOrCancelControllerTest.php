<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Webshop\Checkout;

use App\Actions\Shop\CheckoutService;
use App\Enum\OmnipayProviderType;
use App\Enum\PaymentStatusType;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

/**
 * Test class for CheckoutController.
 *
 * This class tests the checkout functionality for the shop:
 * - Finalizing payments
 * - Cancelling payments
 *
 * Note: CreateSession tests are in CheckoutCreateSessionControllerTest.php
 * Note: ProcessPayment tests are in CheckoutProcessPaymentControllerTest.php
 * The checkout process manages the transition from basket to completed order.
 */
class CheckoutFinalizeOrCancelControllerTest extends BaseCheckoutControllerTest
{
	/**
	 * Test finalizing payment successfully.
	 *
	 * @return void
	 */
	public function testFinalizePaymentSuccess(): void
	{
		// Set up order in processing state
		$this->test_order->status = PaymentStatusType::PROCESSING;
		$this->test_order->provider = OmnipayProviderType::DUMMY;
		$this->test_order->save();

		$provider = OmnipayProviderType::DUMMY->value;
		$transaction_id = $this->test_order->transaction_id;

		$this->assertDatabaseHas('orders', ['transaction_id' => $transaction_id, 'status' => PaymentStatusType::PROCESSING->value]);

		Session::put('metadata.' . $this->test_order->id, [
			'payment_id' => 'dummy-payment-123',
			'status' => 'completed',
			'transactionReference' => $this->test_order->transaction_id,
			'card' => [
				'number' => self::VALID_CARD_NUMBER_SUCCESS,
				'expiryMonth' => '12',
				'expiryYear' => '2025',
				'cvv' => '123',
			],
		]);

		$response = $this->get('/api/v2/Shop/Checkout/Finalize/' . $provider . '/' . $transaction_id);

		$this->assertRedirect($response);
		$response->assertRedirect(route('shop.checkout.complete'));

		// Verify order status was updated to COMPLETED
		$this->assertDatabaseHas('orders', [
			'id' => $this->test_order->id,
			'status' => PaymentStatusType::COMPLETED->value,
		]);
	}

	/**
	 * Test finalizing payment with invalid transaction ID.
	 *
	 * @return void
	 */
	public function testFinalizePaymentInvalidTransactionId(): void
	{
		$provider = OmnipayProviderType::DUMMY->value;
		$invalidTransactionId = 'invalid-transaction-id';

		$response = $this->get('/api/v2/Shop/Checkout/Finalize/' . $provider . '/' . $invalidTransactionId);

		$this->assertNotFound($response);
	}

	/**
	 * Test finalizing payment with invalid provider.
	 *
	 * @return void
	 */
	public function testFinalizePaymentInvalidProvider(): void
	{
		$this->test_order->status = PaymentStatusType::PROCESSING;
		$this->test_order->save();

		$invalidProvider = 'invalid-provider';
		$transaction_id = $this->test_order->transaction_id;

		$response = $this->get('/api/v2/Shop/Checkout/Finalize/' . $invalidProvider . '/' . $transaction_id);

		// Invalid provider results in a redirect (validation failure)
		$this->assertRedirect($response);
	}

	/**
	 * Test cancelling payment.
	 *
	 * @return void
	 */
	public function testCancelPayment(): void
	{
		$this->test_order->status = PaymentStatusType::PROCESSING;
		$this->test_order->provider = OmnipayProviderType::DUMMY;
		$this->test_order->save();

		$transaction_id = $this->test_order->transaction_id;

		$response = $this->get('/api/v2/Shop/Checkout/Cancel/' . $transaction_id);

		$this->assertRedirect($response);
		$response->assertRedirect(route('shop.checkout.cancelled'));

		// Verify order was marked as cancelled
		$this->assertDatabaseHas('orders', [
			'id' => $this->test_order->id,
			'status' => PaymentStatusType::CANCELLED->value,
		]);
	}

	/**
	 * Test cancelling payment with invalid transaction ID.
	 *
	 * @return void
	 */
	public function testCancelPaymentInvalidTransactionId(): void
	{
		$invalid_transaction_id = 'invalid-transaction-id';

		$response = $this->get('/api/v2/Shop/Checkout/Cancel/' . $invalid_transaction_id);

		$this->assertNotFound($response); // Order not found
	}

	/**
	 * Test complete checkout flow.
	 *
	 * @return void
	 */
	public function testCompleteCheckoutFlow(): void
	{
		// Step 1: Create session
		$sessionResponse = $this->postJson('Shop/Checkout/Create-session', [
			'provider' => OmnipayProviderType::DUMMY->value,
			'email' => 'customer@example.com',
		]);

		$this->assertCreated($sessionResponse);

		// Step 2: Process payment
		$process_response = $this->postJson('Shop/Checkout/Process', [
			'additional_data' => [
				'card' => [
					'number' => self::VALID_CARD_NUMBER_SUCCESS,
					'expiryMonth' => '12',
					'expiryYear' => '2025',
					'cvv' => '123',
				],
			],
		]);

		$this->assertOk($process_response);
		$process_response->assertJson([
			'is_success' => true,
		]);

		// Verify order status was updated
		$this->test_order->refresh();
		$this->assertEquals(PaymentStatusType::PROCESSING, $this->test_order->status);

		$response = $this->get('/api/v2/Shop/Checkout/Finalize/Dummy/' . $this->test_order->transaction_id);
		$this->assertRedirect($response);
		$response->assertRedirect(route('shop.checkout.complete'));
	}

	/**
	 * Test that transaction ID is properly set during order creation.
	 *
	 * @return void
	 */
	public function testTransactionIdGeneration(): void
	{
		// Verify our test order has a transaction ID
		$this->assertNotNull($this->test_order->transaction_id);
		$this->assertTrue(Str::isUuid($this->test_order->transaction_id));

		// Finalize should work with this transaction ID
		$this->test_order->status = PaymentStatusType::PROCESSING;
		$this->test_order->provider = OmnipayProviderType::DUMMY;
		$this->test_order->save();

		$provider = OmnipayProviderType::DUMMY->value;
		$transaction_id = $this->test_order->transaction_id;

		Session::put('metadata.' . $this->test_order->id, [
			'payment_id' => 'dummy-payment-123',
			'status' => 'completed',
			'transactionReference' => $this->test_order->transaction_id,
			'card' => [
				'number' => self::VALID_CARD_NUMBER_SUCCESS,
				'expiryMonth' => '12',
				'expiryYear' => '2025',
				'cvv' => '123',
			],
		]);

		$response = $this->get('/api/v2/Shop/Checkout/Finalize/' . $provider . '/' . $transaction_id);

		$this->assertRedirect($response);
		$response->assertRedirect(route('shop.checkout.complete'));
	}

	/**
	 * Test finalizing PayPal payment successfully returns CheckoutResource.
	 *
	 * @return void
	 */
	public function testFinalizePaypalPaymentSuccess(): void
	{
		// Mock the CheckoutService
		$mockCheckoutService = \Mockery::mock(CheckoutService::class);

		// Set up order in processing state with PayPal provider
		$this->test_order->status = PaymentStatusType::PROCESSING;
		$this->test_order->provider = OmnipayProviderType::PAYPAL;
		$this->test_order->save();

		// Mock handlePaymentReturn to update order status to COMPLETED
		$mockCheckoutService->shouldReceive('handlePaymentReturn')
			->once()
			->andReturnUsing(function ($order, $provider) {
				$order->status = PaymentStatusType::COMPLETED;
				$order->save();

				return $order;
			});

		// Bind the mock to the service container
		$this->app->instance(CheckoutService::class, $mockCheckoutService);

		$provider = OmnipayProviderType::PAYPAL->value;
		$transaction_id = $this->test_order->transaction_id;

		$response = $this->get('/api/v2/Shop/Checkout/Finalize/' . $provider . '/' . $transaction_id);

		// For PayPal, it should return JSON (CheckoutResource), not a redirect
		$this->assertOk($response);
		$response->assertJson([
			'is_success' => true,
			'complete_url' => route('shop.checkout.complete'),
			'redirect_url' => null,
			'message' => 'Payment completed successfully.',
		]);

		// Verify order status was updated to COMPLETED
		$this->assertDatabaseHas('orders', [
			'id' => $this->test_order->id,
			'status' => PaymentStatusType::COMPLETED->value,
		]);
	}

	/**
	 * Test finalizing PayPal payment failure returns CheckoutResource.
	 *
	 * @return void
	 */
	public function testFinalizePaypalPaymentFailure(): void
	{
		// Mock the CheckoutService
		$mockCheckoutService = \Mockery::mock(CheckoutService::class);

		// Set up order in processing state with PayPal provider
		$this->test_order->status = PaymentStatusType::PROCESSING;
		$this->test_order->provider = OmnipayProviderType::PAYPAL;
		$this->test_order->save();

		// Mock handlePaymentReturn to return order with PROCESSING status (failed payment)
		$mockCheckoutService->shouldReceive('handlePaymentReturn')
			->once()
			->andReturnUsing(function ($order, $provider) {
				// Return a fresh instance to ensure attributes are loaded
				return $this->test_order;
			});

		// Bind the mock to the service container
		$this->app->instance(CheckoutService::class, $mockCheckoutService);

		$provider = OmnipayProviderType::PAYPAL->value;
		$transaction_id = $this->test_order->transaction_id;

		$response = $this->get('/api/v2/Shop/Checkout/Finalize/' . $provider . '/' . $transaction_id);

		// For PayPal, it should return JSON (CheckoutResource), not a redirect
		$response->assertStatus(400);
		$response->assertJson([
			'is_success' => false,
			'complete_url' => null,
			'redirect_url' => route('shop.checkout.failed'),
			'message' => 'Payment failed or was not completed.',
		]);

		// Verify order status was NOT updated to COMPLETED
		$this->assertDatabaseHas('orders', [
			'id' => $this->test_order->id,
			'status' => PaymentStatusType::PROCESSING->value,
		]);
	}

	/**
	 * Test finalizing PayPal payment with cancelled status.
	 *
	 * @return void
	 */
	public function testFinalizePaypalPaymentCancelled(): void
	{
		// Mock the CheckoutService
		$mockCheckoutService = \Mockery::mock(CheckoutService::class);

		// Set up order in processing state with PayPal provider
		$this->test_order->status = PaymentStatusType::PROCESSING;
		$this->test_order->provider = OmnipayProviderType::PAYPAL;
		$this->test_order->save();

		// Mock handlePaymentReturn to update order status to CANCELLED
		$mockCheckoutService->shouldReceive('handlePaymentReturn')
			->once()
			->andReturnUsing(function ($order, $provider) {
				$order->status = PaymentStatusType::CANCELLED;
				$order->save();

				// Return a fresh instance to ensure attributes are loaded properly
				return $order->fresh();
			});

		// Bind the mock to the service container
		$this->app->instance(CheckoutService::class, $mockCheckoutService);

		$provider = OmnipayProviderType::PAYPAL->value;
		$transaction_id = $this->test_order->transaction_id;

		$response = $this->get('/api/v2/Shop/Checkout/Finalize/' . $provider . '/' . $transaction_id);

		// For PayPal, it should return JSON (CheckoutResource), not a redirect
		$response->assertStatus(400);
		$response->assertJson([
			'is_success' => false,
			'complete_url' => null,
			'redirect_url' => route('shop.checkout.failed'),
			'message' => 'Payment failed or was not completed.',
		]);

		// Verify order status was updated to CANCELLED
		$this->assertDatabaseHas('orders', [
			'id' => $this->test_order->id,
			'status' => PaymentStatusType::CANCELLED->value,
		]);
	}

	/**
	 * Test that PayPal finalization returns CheckoutResource, not RedirectResponse.
	 *
	 * @return void
	 */
	public function testFinalizePaypalReturnsJsonNotRedirect(): void
	{
		// Mock the CheckoutService
		$mockCheckoutService = \Mockery::mock(CheckoutService::class);

		// Set up order in processing state with PayPal provider
		$this->test_order->status = PaymentStatusType::PROCESSING;
		$this->test_order->provider = OmnipayProviderType::PAYPAL;
		$this->test_order->save();

		// Mock handlePaymentReturn to update order status to COMPLETED
		$mockCheckoutService->shouldReceive('handlePaymentReturn')
			->once()
			->andReturnUsing(function ($order, $provider) {
				$order->status = PaymentStatusType::COMPLETED;
				$order->save();

				return $order;
			});

		// Bind the mock to the service container
		$this->app->instance(CheckoutService::class, $mockCheckoutService);

		$provider = OmnipayProviderType::PAYPAL->value;
		$transaction_id = $this->test_order->transaction_id;

		$response = $this->get('/api/v2/Shop/Checkout/Finalize/' . $provider . '/' . $transaction_id);

		// For PayPal, verify it returns JSON content type, not a redirect
		$this->assertOk($response);
		$response->assertHeader('Content-Type', 'application/json');

		// Verify the response structure matches CheckoutResource
		$response->assertJsonStructure([
			'is_success',
			'complete_url',
			'redirect_url',
			'message',
			'order' => [
				'id',
				'transaction_id',
				'status',
			],
		]);
	}

	/**
	 * Test that non-PayPal provider still returns redirect.
	 *
	 * @return void
	 */
	public function testFinalizeNonPayPalReturnsRedirect(): void
	{
		// Set up order in processing state with DUMMY provider (not PayPal)
		$this->test_order->status = PaymentStatusType::PROCESSING;
		$this->test_order->provider = OmnipayProviderType::DUMMY;
		$this->test_order->save();

		$provider = OmnipayProviderType::DUMMY->value;
		$transaction_id = $this->test_order->transaction_id;

		Session::put('metadata.' . $this->test_order->id, [
			'payment_id' => 'dummy-payment-123',
			'status' => 'completed',
			'transactionReference' => $this->test_order->transaction_id,
			'card' => [
				'number' => self::VALID_CARD_NUMBER_SUCCESS,
				'expiryMonth' => '12',
				'expiryYear' => '2025',
				'cvv' => '123',
			],
		]);

		$response = $this->get('/api/v2/Shop/Checkout/Finalize/' . $provider . '/' . $transaction_id);

		// For non-PayPal providers, it should still return a redirect
		$this->assertRedirect($response);
		$response->assertRedirect(route('shop.checkout.complete'));
	}

	public function tearDown(): void
	{
		\Mockery::close();
		parent::tearDown();
	}
}
