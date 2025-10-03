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

use App\Enum\OmnipayProviderType;
use App\Enum\PaymentStatusType;
use App\Models\Order;
use Illuminate\Support\Str;
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\RequireSE;

/**
 * Test class for OrderController listing and access functionality.
 *
 * This class tests the order listing and retrieval functionality:
 * - Listing orders (authentication required)
 * - Getting specific order by ID (with proper authorization)
 * - Access control for different user roles
 * - Transaction ID based access for non-authenticated users
 */
class OrderListingAccessTest extends BaseApiWithDataTest
{
	use RequireSE;

	private Order $order1;
	private Order $order2;
	private Order $order3;
	private string $transaction_id1;
	private string $transaction_id2;

	public function setUp(): void
	{
		parent::setUp();
		$this->requireSe();

		// Create orders for different scenarios
		$this->transaction_id1 = Str::uuid()->toString();
		$this->transaction_id2 = Str::uuid()->toString();

		// Order 1: Belongs to userMayUpload1
		$this->order1 = Order::factory()
			->forUser($this->userMayUpload1)
			->withTransactionId($this->transaction_id1)
			->withProvider(OmnipayProviderType::STRIPE)
			->withStatus(PaymentStatusType::COMPLETED)
			->withEmail($this->userMayUpload1->email)
			->create();

		// Order 2: Belongs to userMayUpload2
		$this->order2 = Order::factory()
			->forUser($this->userMayUpload2)
			->withTransactionId($this->transaction_id2)
			->withProvider(OmnipayProviderType::PAYPAL_EXPRESS)
			->withStatus(PaymentStatusType::PENDING)
			->withEmail($this->userMayUpload2->email)
			->create();

		// Order 3: Anonymous order (no user)
		$this->order3 = Order::factory()
			->withTransactionId(Str::uuid()->toString())
			->withProvider(OmnipayProviderType::STRIPE)
			->withStatus(PaymentStatusType::COMPLETED)
			->withEmail('anonymous@example.com')
			->create();
	}

	public function tearDown(): void
	{
		$this->resetSe();
		parent::tearDown();
	}

	/**
	 * Test listing orders when not authenticated.
	 * Should return 401 Unauthorized.
	 */
	public function testListOrdersNotAuthenticated(): void
	{
		$response = $this->getJson('Shop/Order/List');

		$this->assertUnauthorized($response);
	}

	/**
	 * Test listing orders as regular user.
	 * Should return orders accessible to the user.
	 */
	public function testListOrdersAsRegularUser(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJson('Shop/Order/List');

		$this->assertOk($response);

		// Should return array of orders
		$response->assertJsonStructure([
			'*' => [
				'id',
				'provider',
				'username',
				'email',
				'status',
				'amount',
				'created_at',
				'paid_at',
				'comment',
			],
		]);

		$orders = $response->json();
		$this->assertIsArray($orders);

		// Find user's order in the response
		$userOrder = collect($orders)->firstWhere('id', $this->order1->id);
		$this->assertNotNull($userOrder);
		$this->assertEquals($this->order1->transaction_id, $userOrder['transaction_id']);
		$this->assertEquals($this->userMayUpload1->name, $userOrder['username']);
	}

	/**
	 * Test listing orders as admin user.
	 * Should return all orders in the system.
	 */
	public function testListOrdersAsAdmin(): void
	{
		$response = $this->actingAs($this->admin)->getJson('Shop/Order/List');

		$this->assertOk($response);

		$orders = $response->json();
		$this->assertIsArray($orders);

		// Admin should see all orders
		$orderIds = collect($orders)->pluck('id')->toArray();
		$this->assertContains($this->order1->id, $orderIds);
		$this->assertContains($this->order2->id, $orderIds);
		$this->assertContains($this->order3->id, $orderIds);
	}

	/**
	 * Test getting specific order when not authenticated and no transaction ID provided.
	 * Should return 401 Unauthorized.
	 */
	public function testGetOrderNotAuthenticatedNoTransactionId(): void
	{
		$response = $this->getJson("Shop/Order/{$this->order1->id}");

		$this->assertUnauthorized($response);
	}

	/**
	 * Test getting specific order when not authenticated but with valid transaction ID.
	 * Should return the order.
	 */
	public function testGetOrderNotAuthenticatedWithValidTransactionId(): void
	{
		$response = $this->getJson("Shop/Order/{$this->order1->id}?transaction_id={$this->transaction_id1}");

		$this->assertOk($response);
		$response->assertJson([
			'id' => $this->order1->id,
			'transaction_id' => $this->transaction_id1,
			'username' => $this->userMayUpload1->name,
			'status' => PaymentStatusType::COMPLETED->value,
		]);
	}

	/**
	 * Test getting specific order when not authenticated but with invalid transaction ID.
	 * Should return 403 Forbidden.
	 */
	public function testGetOrderNotAuthenticatedWithInvalidTransactionId(): void
	{
		$invalidTransactionId = Str::uuid()->toString();
		$response = $this->getJson("Shop/Order/{$this->order1->id}?transaction_id={$invalidTransactionId}");

		$this->assertUnauthorized($response);
	}

	/**
	 * Test getting own order as authenticated user.
	 * Should return the order.
	 */
	public function testGetOwnOrderAsAuthenticatedUser(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJson("Shop/Order/{$this->order1->id}");

		$this->assertOk($response);
		$response->assertJson([
			'id' => $this->order1->id,
			'transaction_id' => $this->transaction_id1,
			'username' => $this->userMayUpload1->name,
			'status' => PaymentStatusType::COMPLETED->value,
		]);
	}

	/**
	 * Test getting other user's order as authenticated user (non-admin).
	 * Should return 403 Forbidden when no transaction ID provided.
	 */
	public function testGetOtherUserOrderAsAuthenticatedUserNoTransactionId(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJson("Shop/Order/{$this->order2->id}");

		$this->assertForbidden($response);
	}

	/**
	 * Test getting other user's order as authenticated user with valid transaction ID.
	 * Should return the order.
	 */
	public function testGetOtherUserOrderAsAuthenticatedUserWithValidTransactionId(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJson("Shop/Order/{$this->order2->id}?transaction_id={$this->transaction_id2}");

		$this->assertOk($response);
		$response->assertJson([
			'id' => $this->order2->id,
			'transaction_id' => $this->transaction_id2,
			'username' => $this->userMayUpload2->name,
			'status' => PaymentStatusType::PENDING->value,
		]);
	}

	/**
	 * Test getting any order as admin user.
	 * Should return the order regardless of ownership.
	 */
	public function testGetOrderAsAdmin(): void
	{
		$response = $this->actingAs($this->admin)->getJson("Shop/Order/{$this->order2->id}");

		$this->assertOk($response);
		$response->assertJson([
			'id' => $this->order2->id,
			'transaction_id' => $this->transaction_id2,
			'username' => $this->userMayUpload2->name,
			'status' => PaymentStatusType::PENDING->value,
		]);
	}

	/**
	 * Test getting anonymous order (no user_id) with valid transaction ID.
	 * Should return the order.
	 */
	public function testGetAnonymousOrderWithValidTransactionId(): void
	{
		$response = $this->getJson("Shop/Order/{$this->order3->id}?transaction_id={$this->order3->transaction_id}");

		$this->assertOk($response);
		$response->assertJson([
			'id' => $this->order3->id,
			'transaction_id' => $this->order3->transaction_id,
			'username' => null,
			'status' => PaymentStatusType::COMPLETED->value,
			'email' => 'anonymous@example.com',
		]);
	}

	/**
	 * Test getting anonymous order with invalid transaction ID.
	 * Should return 403 Forbidden.
	 */
	public function testGetAnonymousOrderWithInvalidTransactionId(): void
	{
		$invalidTransactionId = Str::uuid()->toString();
		$response = $this->getJson("Shop/Order/{$this->order3->id}?transaction_id={$invalidTransactionId}");

		$this->assertUnauthorized($response);
	}

	/**
	 * Test getting non-existent order.
	 * Should return 404 Not Found.
	 */
	public function testGetNonExistentOrder(): void
	{
		$nonExistentId = 99999;
		$response = $this->actingAs($this->admin)->getJson("Shop/Order/{$nonExistentId}");

		$this->assertNotFound($response);
	}

	/**
	 * Test getting order with invalid ID format.
	 * Should return validation error.
	 */
	public function testGetOrderWithInvalidIdFormat(): void
	{
		$response = $this->actingAs($this->admin)->getJson('Shop/Order/invalid');

		// This should return a validation error or 404, depending on routing
		$this->assertStatus($response, [404, 422]);
	}

	/**
	 * Test the JSON structure of order response.
	 * Should include all expected fields.
	 */
	public function testOrderResponseStructure(): void
	{
		$response = $this->actingAs($this->admin)->getJson("Shop/Order/{$this->order1->id}");

		$this->assertOk($response);
		$response->assertJsonStructure([
			'id',
			'transaction_id',
			'provider',
			'username',
			'email',
			'status',
			'amount',
			'created_at',
			'paid_at',
			'comment',
		]);
	}
}
