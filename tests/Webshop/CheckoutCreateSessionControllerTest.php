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

namespace Tests\Webshop;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Enum\OmnipayProviderType;
use App\Enum\PaymentStatusType;
use Illuminate\Support\Facades\Session;

/**
 * Test class for CheckoutController CreateSession functionality.
 *
 * This class tests the checkout session creation functionality:
 * - Creating checkout sessions with various parameters
 * - Provider validation
 * - Email validation
 * - Authentication and authorization
 * - Session management
 */
class CheckoutCreateSessionControllerTest extends BaseCheckoutControllerTest
{
	/**
	 * Test creating a checkout session successfully.
	 *
	 * @return void
	 */
	public function testCreateSessionSuccess(): void
	{
		$response = $this->postJson('Shop/Checkout/Create-session', [
			'provider' => OmnipayProviderType::DUMMY->value,
			'email' => 'customer@example.com',
		]);

		$this->assertCreated($response);
		$response->assertJsonStructure([
			'id',
			'provider',
			'user_id',
			'email',
			'status',
			'amount',
			'paid_at',
			'created_at',
			'comment',
			'items',
		]);

		$response->assertJson([
			'provider' => OmnipayProviderType::DUMMY->value,
			'email' => 'customer@example.com',
			'status' => PaymentStatusType::PENDING->value,
		]);

		// Verify database was updated
		$this->assertDatabaseHas('orders', [
			'id' => $this->test_order->id,
			'provider' => OmnipayProviderType::DUMMY->value,
			'email' => 'customer@example.com',
		]);
	}

	/**
	 * Test creating a session with authenticated user.
	 *
	 * @return void
	 */
	public function testCreateSessionWithAuthenticatedUser(): void
	{
		// Update order to belong to authenticated user
		$this->test_order->user_id = $this->userMayUpload1->id;
		$this->test_order->save();

		$response = $this->actingAs($this->userMayUpload1)
			->postJson('Shop/Checkout/Create-session', [
				'provider' => OmnipayProviderType::DUMMY->value,
			]);

		$this->assertCreated($response);
		$response->assertJson([
			'provider' => OmnipayProviderType::DUMMY->value,
			'user_id' => $this->userMayUpload1->id,
		]);
	}

	/**
	 * Test creating session with invalid provider.
	 *
	 * @return void
	 */
	public function testCreateSessionInvalidProvider(): void
	{
		$response = $this->postJson('Shop/Checkout/Create-session', [
			'provider' => 'invalid-provider',
		]);

		$this->assertUnprocessable($response);
		$response->assertJsonValidationErrors(['provider']);
	}

	/**
	 * Test creating session with invalid email.
	 *
	 * @return void
	 */
	public function testCreateSessionInvalidEmail(): void
	{
		$response = $this->postJson('Shop/Checkout/Create-session', [
			'provider' => OmnipayProviderType::DUMMY->value,
			'email' => 'invalid-email',
		]);

		$this->assertUnprocessable($response);
		$response->assertJsonValidationErrors(['email']);
	}

	/**
	 * Test creating session without a basket.
	 *
	 * @return void
	 */
	public function testCreateSessionWithoutBasket(): void
	{
		// Remove basket from session
		Session::forget(RequestAttribute::BASKET_ID_ATTRIBUTE);

		$response = $this->postJson('Shop/Checkout/Create-session', [
			'provider' => OmnipayProviderType::DUMMY->value,
		]);

		$this->assertUnauthorized($response); // Should be unauthorized without a valid basket

		$response = $this->actingAs($this->userMayUpload1)->postJson('Shop/Checkout/Create-session', [
			'provider' => OmnipayProviderType::DUMMY->value,
		]);

		$this->assertForbidden($response); // Should be unauthorized without a valid basket
	}

	/**
	 * Test session creation without email for authenticated user.
	 *
	 * @return void
	 */
	public function testCreateSessionAuthenticatedUserNoEmail(): void
	{
		$this->test_order->user_id = $this->userMayUpload1->id;
		$this->test_order->save();

		$response = $this->actingAs($this->userMayUpload1)
			->postJson('Shop/Checkout/Create-session', [
				'provider' => OmnipayProviderType::DUMMY->value,
			]);

		$this->assertCreated($response);
		$response->assertJson([
			'provider' => OmnipayProviderType::DUMMY->value,
			'user_id' => $this->userMayUpload1->id,
			'email' => 'test@example.com',
		]);
	}

	/**
	 * Test session creation updates existing provider.
	 *
	 * @return void
	 */
	public function testCreateSessionUpdatesExistingProvider(): void
	{
		// Set initial provider
		$this->test_order->provider = OmnipayProviderType::STRIPE;
		$this->test_order->save();

		// Update to different provider
		$response = $this->postJson('Shop/Checkout/Create-session', [
			'provider' => OmnipayProviderType::DUMMY->value,
		]);

		$this->assertCreated($response);
		$response->assertJson([
			'provider' => OmnipayProviderType::DUMMY->value,
		]);

		// Verify database was updated
		$this->assertDatabaseHas('orders', [
			'id' => $this->test_order->id,
			'provider' => OmnipayProviderType::DUMMY->value,
		]);
	}

	/**
	 * Test session creation with minimum required data.
	 *
	 * @return void
	 */
	public function testCreateSessionMinimalData(): void
	{
		$response = $this->postJson('Shop/Checkout/Create-session', [
			'provider' => OmnipayProviderType::DUMMY->value,
		]);

		$this->assertCreated($response);
		$response->assertJson([
			'provider' => OmnipayProviderType::DUMMY->value,
			'email' => 'test@example.com',
		]);
	}

	/**
	 * Test creating session with all supported providers.
	 *
	 * @return void
	 */
	public function testCreateSessionWithAllProviders(): void
	{
		$providers = [
			OmnipayProviderType::DUMMY,
			OmnipayProviderType::STRIPE,
			OmnipayProviderType::PAYPAL_EXPRESS,
		];

		foreach ($providers as $provider) {
			// Reset order state
			$this->test_order->provider = null;
			$this->test_order->save();

			$response = $this->postJson('Shop/Checkout/Create-session', [
				'provider' => $provider->value,
			]);

			$this->assertCreated($response);
			$response->assertJson([
				'provider' => $provider->value,
			]);

			// Verify database was updated
			$this->assertDatabaseHas('orders', [
				'id' => $this->test_order->id,
				'provider' => $provider->value,
			]);
		}
	}

	/**
	 * Test creating session preserves existing order data.
	 *
	 * @return void
	 */
	public function testCreateSessionPreservesOrderData(): void
	{
		// Set some existing data on the order
		$this->test_order->comment = 'Test comment';
		$this->test_order->save();

		$response = $this->postJson('Shop/Checkout/Create-session', [
			'provider' => OmnipayProviderType::DUMMY->value,
			'email' => 'customer@example.com',
		]);

		$this->assertCreated($response);
		$response->assertJson([
			'provider' => OmnipayProviderType::DUMMY->value,
			'email' => 'customer@example.com',
			'comment' => 'Test comment', // Should preserve existing comment
		]);
	}

	/**
	 * Test creating session only updates specified fields.
	 *
	 * @return void
	 */
	public function testCreateSessionOnlyUpdatesSpecifiedFields(): void
	{
		// Set initial state
		$this->test_order->provider = OmnipayProviderType::STRIPE;
		$this->test_order->email = 'old@example.com';
		$this->test_order->save();

		// Update only provider, not email
		$response = $this->postJson('Shop/Checkout/Create-session', [
			'provider' => OmnipayProviderType::DUMMY->value,
		]);

		$this->assertCreated($response);
		$response->assertJson([
			'provider' => OmnipayProviderType::DUMMY->value,
			'email' => 'old@example.com', // Should keep existing email
		]);
	}
}
