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

use App\Contracts\Http\Requests\RequestAttribute;
use App\Enum\PaymentStatusType;
use App\Models\Configs;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;

/**
 * Test class for CheckoutController Offline functionality.
 *
 * This class tests the offline order completion functionality:
 * - Completing orders with various parameters
 * - Email validation
 * - Authentication and authorization
 * - Session management
 */
class CheckoutOfflineControllerTest extends BaseCheckoutControllerTest
{
	public function setUp(): void
	{
		parent::setUp();

		Configs::set('webshop_offline', true);
	}

	public function tearDown(): void
	{
		Configs::set('webshop_offline', false);

		parent::tearDown();
	}

	/**
	 * Test completing an offline order successfully with provided email.
	 *
	 * @return void
	 */
	public function testOfflineOrderSuccessWithEmail(): void
	{
		$response = $this->postJson('Shop/Checkout/Offline', [
			'email' => 'customer@example.com',
		]);

		$this->assertOk($response);
		$response->assertJsonStructure([
			'is_success',
			'message',
			'order' => [
				'id',
				'status',
				'email',
				'amount',
				'created_at',
			],
		]);

		$response->assertJson([
			'is_success' => true,
			'message' => 'Order marked as completed (offline)',
			'order' => [
				'status' => PaymentStatusType::OFFLINE->value,
				'email' => 'customer@example.com',
			],
		]);

		// Verify database was updated
		$this->assertDatabaseHas('orders', [
			'id' => $this->test_order->id,
			'status' => PaymentStatusType::OFFLINE->value,
			'email' => 'customer@example.com',
		]);
	}

	/**
	 * Test completing an offline order with existing email.
	 *
	 * @return void
	 */
	public function testOfflineOrderWithExistingEmail(): void
	{
		// Order already has test@example.com from setup
		$response = $this->postJson('Shop/Checkout/Offline', []);

		$this->assertOk($response);
		$response->assertJson([
			'is_success' => true,
			'message' => 'Order marked as completed (offline)',
			'order' => [
				'status' => PaymentStatusType::OFFLINE->value,
				'email' => 'test@example.com',
			],
		]);

		// Verify database was updated
		$this->assertDatabaseHas('orders', [
			'id' => $this->test_order->id,
			'status' => PaymentStatusType::OFFLINE->value,
			'email' => 'test@example.com',
		]);
	}

	/**
	 * Test completing an offline order with authenticated user.
	 *
	 * @return void
	 */
	public function testOfflineOrderWithAuthenticatedUser(): void
	{
		// Update order to belong to authenticated user
		$this->test_order->user_id = $this->userMayUpload1->id;
		$this->test_order->save();

		$response = $this->actingAs($this->userMayUpload1)
			->postJson('Shop/Checkout/Offline', []);

		$this->assertOk($response);
		$response->assertJson([
			'is_success' => true,
			'order' => [
				'username' => $this->userMayUpload1->name,
				'email' => 'test@example.com',
			],
		]);
	}

	/**
	 * Test completing an offline order without an email.
	 *
	 * @return void
	 */
	public function testOfflineOrderWithoutEmail(): void
	{
		// Remove email from order
		$this->test_order->email = null;
		$this->test_order->save();

		$response = $this->postJson('Shop/Checkout/Offline', []);

		$response->assertStatus(400);
		$response->assertJson([
			'is_success' => false,
			'message' => 'Email is required for offline orders.',
		]);

		// Verify order status wasn't changed
		$this->assertDatabaseHas('orders', [
			'id' => $this->test_order->id,
			'status' => PaymentStatusType::PENDING->value, // Should remain pending
			'email' => null,
		]);
	}

	/**
	 * Test completing an offline order with invalid email format.
	 *
	 * @return void
	 */
	public function testOfflineOrderWithInvalidEmail(): void
	{
		$response = $this->postJson('Shop/Checkout/Offline', [
			'email' => 'invalid-email',
		]);

		$this->assertUnprocessable($response);
		$response->assertJsonValidationErrors(['email']);
	}

	/**
	 * Test completing an offline order without a basket.
	 *
	 * @return void
	 */
	public function testOfflineOrderWithoutBasket(): void
	{
		// Remove basket from cookie
		$this->withCookie(RequestAttribute::BASKET_ID_ATTRIBUTE, '');

		$response = $this->postJson('Shop/Checkout/Offline', [
			'email' => 'customer@example.com',
		]);

		$this->assertUnauthorized($response); // Should be unauthorized without a valid basket

		$response = $this->actingAs($this->userMayUpload1)->postJson('Shop/Checkout/Offline', [
			'email' => 'customer@example.com',
		]);

		$this->assertForbidden($response); // Should be forbidden without a valid basket
	}

	/**
	 * Test completing an offline order with empty string email.
	 *
	 * @return void
	 */
	public function testOfflineOrderWithEmptyEmail(): void
	{
		// Set empty email in the order
		$this->test_order->email = '';
		$this->test_order->save();

		$response = $this->postJson('Shop/Checkout/Offline', []);

		$response->assertStatus(400);
		$response->assertJson([
			'is_success' => false,
			'message' => 'Email is required for offline orders.',
		]);

		// Verify order status wasn't changed
		$this->assertDatabaseHas('orders', [
			'id' => $this->test_order->id,
			'status' => PaymentStatusType::PENDING->value, // Should remain pending
		]);
	}

	/**
	 * Test that order preserves its existing data when completing offline.
	 *
	 * @return void
	 */
	public function testOfflineOrderPreservesExistingData(): void
	{
		// Set some existing data on the order
		$this->test_order->comment = 'Test comment';
		$this->test_order->save();

		$response = $this->postJson('Shop/Checkout/Offline', [
			'email' => 'customer@example.com',
		]);

		$this->assertOk($response);
		$response->assertJson([
			'is_success' => true,
			'order' => [
				'email' => 'customer@example.com',
				'comment' => 'Test comment', // Should preserve existing comment
			],
		]);

		// Verify database was updated but preserved existing fields
		$this->assertDatabaseHas('orders', [
			'id' => $this->test_order->id,
			'status' => PaymentStatusType::OFFLINE->value,
			'email' => 'customer@example.com',
			'comment' => 'Test comment',
		]);
	}

	/**
	 * Test that an order with non-pending status can be completed offline.
	 *
	 * @return void
	 */
	public function testOfflineOrderFromDifferentStatus(): void
	{
		// Set order to cancelled status first
		$this->test_order->status = PaymentStatusType::CANCELLED;
		$this->test_order->save();

		$response = $this->postJson('Shop/Checkout/Offline', [
			'email' => 'customer@example.com',
		]);

		$this->assertOk($response);
		$response->assertJson([
			'is_success' => true,
			'order' => [
				'status' => PaymentStatusType::OFFLINE->value,
			],
		]);

		// Verify status was changed from cancelled to offline
		$this->assertDatabaseHas('orders', [
			'id' => $this->test_order->id,
			'status' => PaymentStatusType::OFFLINE->value,
		]);
	}
}
