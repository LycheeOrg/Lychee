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

use App\Enum\OmnipayProviderType;
use App\Enum\PaymentStatusType;
use Illuminate\Support\Facades\Session;

/**
 * Test class for CheckoutController ProcessPayment functionality.
 *
 * This class tests the payment processing functionality:
 * - Processing payments with various providers
 * - Handling payment data and validation
 * - Error handling for invalid states
 * - Security and authorization checks
 */
class CheckoutProcessPaymentControllerTest extends BaseCheckoutControllerTest
{
	/**
	 * Test processing payment successfully.
	 *
	 * @return void
	 */
	public function testProcessPaymentSuccess(): void
	{
		// First create a session to set provider
		$this->test_order->provider = OmnipayProviderType::DUMMY;
		$this->test_order->save();

		$response = $this->postJson('Shop/Checkout/Process', [
			'additional_data' => [
				'card' => [
					'number' => self::VALID_CARD_NUMBER_SUCCESS, // Success on Even numbers.
					'expiryMonth' => '12',
					'expiryYear' => '2025',
					'cvv' => '123',
				],
			],
		]);

		$this->assertOk($response);
		$response->assertJsonStructure([
			'is_success',
			'is_redirect',
			'redirect_url',
			'message',
		]);

		// For DUMMY provider, it should be successful without redirect
		$response->assertJson([
			'is_success' => true,
			'is_redirect' => false,
		]);
	}

	/**
	 * Test processing payment without provider set.
	 *
	 * @return void
	 */
	public function testProcessPaymentWithoutProvider(): void
	{
		// Order doesn't have provider set
		$response = $this->postJson('Shop/Checkout/Process');

		$this->assertUnauthorized($response); // Should be unauthorized without a valid basket
	}

	/**
	 * Test processing payment without basket.
	 *
	 * @return void
	 */
	public function testProcessPaymentWithoutBasket(): void
	{
		Session::forget('basket_id');

		$response = $this->postJson('Shop/Checkout/Process');

		$this->assertUnauthorized($response); // Should be unauthorized without a valid basket
	}

	/**
	 * Test processing payment with additional data.
	 *
	 * @return void
	 */
	public function testProcessPaymentWithAdditionalData(): void
	{
		$this->test_order->provider = OmnipayProviderType::DUMMY;
		$this->test_order->save();

		$response = $this->postJson('Shop/Checkout/Process', [
			'additional_data' => [
				'card' => [
					'number' => self::VALID_CARD_NUMBER_SUCCESS,
					'expiryMonth' => '12',
					'expiryYear' => '2025',
					'cvv' => '123',
					'cardholder_name' => 'John Doe',
				],
			],
		]);

		$this->assertOk($response);
		$response->assertJson([
			'is_success' => true,
		]);
	}

	/**
	 * Test processing payment for order that cannot be processed.
	 *
	 * @return void
	 */
	public function testProcessPaymentCannotProcess(): void
	{
		// Set order to completed state (cannot be processed)
		$this->test_order->status = PaymentStatusType::COMPLETED;
		$this->test_order->provider = OmnipayProviderType::DUMMY;
		$this->test_order->save();

		$response = $this->postJson('Shop/Checkout/Process');

		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userMayUpload1)->postJson('Shop/Checkout/Process');

		$this->assertForbidden($response);
	}

	/**
	 * Test processing payment with different providers.
	 *
	 * @return void
	 */
	public function testProcessPaymentWithDifferentProviders(): void
	{
		$providers = [
			OmnipayProviderType::DUMMY,
			OmnipayProviderType::STRIPE,
			OmnipayProviderType::PAYPAL,
		];

		foreach ($providers as $provider) {
			// Reset order state
			$this->test_order->refresh();
			$this->test_order->status = PaymentStatusType::PENDING;
			$this->test_order->provider = $provider;
			$this->test_order->save();

			$response = $this->postJson('Shop/Checkout/Process', [
				'additional_data' => [
					'card' => [
						'number' => self::VALID_CARD_NUMBER_SUCCESS,
						'expiryMonth' => '12',
						'expiryYear' => '2025',
						'cvv' => '123',
					],
				],
			]);

			// Should respond appropriately based on provider
			if ($provider === OmnipayProviderType::DUMMY) {
				$this->assertOk($response);
			} else {
				// Real providers might require different handling
				// We keep 501 because this is not implemented (in other words, not initialized)
				self::assertEquals(501, $response->getStatusCode(), $provider->value);
			}
		}
	}

	/**
	 * Test processing payment with invalid card data.
	 *
	 * @return void
	 */
	public function testProcessPaymentWithInvalidCardData(): void
	{
		$this->test_order->provider = OmnipayProviderType::DUMMY;
		$this->test_order->save();

		$response = $this->postJson('Shop/Checkout/Process', [
			'additional_data' => [
				'card' => [
					'number' => self::VALID_CARD_NUMBER_FAIL, // Failure on odd numbers for DUMMY provider
					'expiryMonth' => '12',
					'expiryYear' => '2025',
					'cvv' => '123',
				],
			],
		]);

		// DUMMY provider should handle this gracefully
		$response->assertStatus(400);
		$response->assertJson([
			'is_success' => false,
		]);
	}

	/**
	 * Test processing payment with malformed card data.
	 *
	 * @return void
	 */
	public function testProcessPaymentWithMalformedCardData(): void
	{
		$this->test_order->provider = OmnipayProviderType::DUMMY;
		$this->test_order->save();

		$response = $this->postJson('Shop/Checkout/Process', [
			'additional_data' => [
				'card' => [
					'number' => 'invalid-card-number',
					'expiryMonth' => '13', // Invalid month
					'expiryYear' => '2020', // Expired year
					'cvv' => 'abc', // Invalid CVV
				],
			],
		]);

		$response->assertStatus(400);
	}

	/**
	 * Test processing payment with empty additional data.
	 *
	 * @return void
	 */
	public function testProcessPaymentWithEmptyAdditionalData(): void
	{
		$this->test_order->provider = OmnipayProviderType::DUMMY;
		$this->test_order->save();

		$response = $this->postJson('Shop/Checkout/Process', [
			'additional_data' => [],
		]);

		$response->assertStatus(400);
	}

	/**
	 * Test processing payment without additional data.
	 *
	 * @return void
	 */
	public function testProcessPaymentWithoutAdditionalData(): void
	{
		$this->test_order->provider = OmnipayProviderType::DUMMY;
		$this->test_order->save();

		$response = $this->postJson('Shop/Checkout/Process');

		$response->assertStatus(400);
	}

	/**
	 * Test processing payment preserves order data.
	 *
	 * @return void
	 */
	public function testProcessPaymentPreservesOrderData(): void
	{
		// Set some existing data on the order
		$this->test_order->provider = OmnipayProviderType::DUMMY;
		$this->test_order->email = 'customer@example.com';
		$this->test_order->comment = 'Test order';
		$this->test_order->save();

		$response = $this->postJson('Shop/Checkout/Process', [
			'additional_data' => [
				'card' => [
					'number' => self::VALID_CARD_NUMBER_SUCCESS,
					'expiryMonth' => '12',
					'expiryYear' => '2025',
					'cvv' => '123',
				],
			],
		]);

		$this->assertOk($response);

		// Verify order data is preserved
		$this->test_order->refresh();
		$this->assertEquals('customer@example.com', $this->test_order->email);
		$this->assertEquals('Test order', $this->test_order->comment);
	}

	/**
	 * Test processing payment updates order status appropriately.
	 *
	 * @return void
	 */
	public function testProcessPaymentUpdatesOrderStatus(): void
	{
		$this->test_order->provider = OmnipayProviderType::DUMMY;
		$this->test_order->status = PaymentStatusType::PENDING;
		$this->test_order->save();

		$response = $this->postJson('Shop/Checkout/Process', [
			'additional_data' => [
				'card' => [
					'number' => self::VALID_CARD_NUMBER_SUCCESS,
					'expiryMonth' => '12',
					'expiryYear' => '2025',
					'cvv' => '123',
				],
			],
		]);

		$this->assertOk($response);

		// Verify order status might be updated
		$this->test_order->refresh();
		$this->assertTrue(in_array($this->test_order->status, [
			PaymentStatusType::PENDING,
			PaymentStatusType::PROCESSING,
			PaymentStatusType::COMPLETED,
		], true));
	}
}
