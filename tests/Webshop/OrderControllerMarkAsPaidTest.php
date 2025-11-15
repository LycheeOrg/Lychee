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

use App\Enum\PaymentStatusType;
use App\Models\Order;
use App\Models\OrderItem;
use Str;

/**
 * Test class for OrderController markAsPaid functionality.
 *
 * This class tests the order mark as paid functionality:
 * - Marking offline orders as paid with proper authorization
 * - Handling different order statuses
 * - Authentication and authorization checks
 * - Invalid order handling
 */
class OrderControllerMarkAsPaidTest extends BaseCheckoutControllerTest
{
	/**
	 * Test marking an offline order as paid successfully as admin.
	 *
	 * @return void
	 */
	public function testMarkOfflineOrderAsPaidAsAdmin(): void
	{
		// Set order to offline status
		$this->test_order->status = PaymentStatusType::OFFLINE;
		$this->test_order->save();

		$response = $this->actingAs($this->admin)
			->postJson('Shop/Order/' . $this->test_order->id);

		$this->assertNoContent($response);

		// Verify database was updated
		$this->assertDatabaseHas('orders', [
			'id' => $this->test_order->id,
			'status' => PaymentStatusType::COMPLETED->value,
		]);
	}

	/**
	 * Test marking an offline order as paid from offline status.
	 *
	 * @return void
	 */
	public function testMarkOfflineOrderAsPaid(): void
	{
		$this->test_order->status = PaymentStatusType::OFFLINE;
		$this->test_order->save();

		$response = $this->actingAs($this->admin)
			->postJson('Shop/Order/' . $this->test_order->id);

		$this->assertNoContent($response);

		$this->assertDatabaseHas('orders', [
			'id' => $this->test_order->id,
			'status' => PaymentStatusType::COMPLETED->value,
		]);
	}

	/**
	 * Test marking order as paid without authentication.
	 *
	 * @return void
	 */
	public function testMarkOrderAsPaidWithoutAuth(): void
	{
		$this->test_order->status = PaymentStatusType::OFFLINE;
		$this->test_order->save();

		$response = $this->postJson('Shop/Order/'. $this->test_order->id);

		$this->assertUnauthorized($response);

		// Verify status wasn't changed
		$this->assertDatabaseHas('orders', [
			'id' => $this->test_order->id,
			'status' => PaymentStatusType::OFFLINE->value,
		]);
	}

	/**
	 * Test marking order as paid as non-admin user.
	 *
	 * @return void
	 */
	public function testMarkOrderAsPaidAsNonAdmin(): void
	{
		$this->test_order->status = PaymentStatusType::OFFLINE;
		$this->test_order->save();

		$response = $this->actingAs($this->userMayUpload1)
			->postJson('Shop/Order/' . $this->test_order->id);

		$this->assertForbidden($response);

		// Verify status wasn't changed
		$this->assertDatabaseHas('orders', [
			'id' => $this->test_order->id,
			'status' => PaymentStatusType::OFFLINE->value,
		]);
	}

	/**
	 * Test marking order as paid with non-existent order ID.
	 *
	 * @return void
	 */
	public function testMarkNonExistentOrderAsPaid(): void
	{
		$non_existent_id = 99999;

		$response = $this->actingAs($this->admin)
			->postJson('Shop/Order/' . $non_existent_id);

		$this->assertNotFound($response);
	}

	/**
	 * Test marking order as paid with invalid order ID format.
	 *
	 * @return void
	 */
	public function testMarkOrderAsPaidWithInvalidId(): void
	{
		$response = $this->actingAs($this->admin)
			->postJson('Shop/Order/invalid-id');

		$this->assertUnprocessable($response);
		$response->assertJsonValidationErrors(['order_id']);
	}

	/**
	 * Test marking a pending order as paid (should fail).
	 *
	 * @return void
	 */
	public function testMarkPendingOrderAsPaid(): void
	{
		$this->test_order->status = PaymentStatusType::PENDING;
		$this->test_order->save();

		$response = $this->actingAs($this->admin)
			->postJson('Shop/Order/' . $this->test_order->id);

		$this->assertNotFound($response);

		// Verify status wasn't changed
		$this->assertDatabaseHas('orders', [
			'id' => $this->test_order->id,
			'status' => PaymentStatusType::PENDING->value,
		]);
	}

	/**
	 * Test marking a completed order as paid (should fail).
	 *
	 * @return void
	 */
	public function testMarkCompletedOrderAsPaid(): void
	{
		$this->test_order->status = PaymentStatusType::COMPLETED;
		$this->test_order->save();

		$response = $this->actingAs($this->admin)
			->postJson('Shop/Order/' . $this->test_order->id);

		$this->assertNotFound($response);

		// Verify status wasn't changed
		$this->assertDatabaseHas('orders', [
			'id' => $this->test_order->id,
			'status' => PaymentStatusType::COMPLETED->value,
		]);
	}

	/**
	 * Test marking a cancelled order as paid (should fail).
	 *
	 * @return void
	 */
	public function testMarkCancelledOrderAsPaid(): void
	{
		$this->test_order->status = PaymentStatusType::CANCELLED;
		$this->test_order->save();

		$response = $this->actingAs($this->admin)
			->postJson('Shop/Order/' . $this->test_order->id);

		$this->assertNotFound($response);

		// Verify status wasn't changed
		$this->assertDatabaseHas('orders', [
			'id' => $this->test_order->id,
			'status' => PaymentStatusType::CANCELLED->value,
		]);
	}

	/**
	 * Test marking a processing order as paid (should fail).
	 *
	 * @return void
	 */
	public function testMarkProcessingOrderAsPaid(): void
	{
		$this->test_order->status = PaymentStatusType::PROCESSING;
		$this->test_order->save();

		$response = $this->actingAs($this->admin)
			->postJson('Shop/Order/' . $this->test_order->id);

		$this->assertNotFound($response);

		// Verify status wasn't changed
		$this->assertDatabaseHas('orders', [
			'id' => $this->test_order->id,
			'status' => PaymentStatusType::PROCESSING->value,
		]);
	}

	/**
	 * Test that marking order as paid preserves other order data.
	 *
	 * @return void
	 */
	public function testMarkOrderAsPaidPreservesOrderData(): void
	{
		// Set some existing data on the order
		$this->test_order->status = PaymentStatusType::OFFLINE;
		$this->test_order->comment = 'Test comment';
		$this->test_order->email = 'customer@example.com';
		$this->test_order->save();

		$response = $this->actingAs($this->admin)
			->postJson('Shop/Order/' . $this->test_order->id);

		$this->assertNoContent($response);

		// Verify other data is preserved
		$this->assertDatabaseHas('orders', [
			'id' => $this->test_order->id,
			'status' => PaymentStatusType::COMPLETED->value,
			'comment' => 'Test comment',
			'email' => 'customer@example.com',
		]);
	}

	/**
	 * Test marking order as paid updates only the status field.
	 *
	 * @return void
	 */
	public function testMarkOrderAsPaidUpdatesOnlyStatus(): void
	{
		// Set order to offline status
		$this->test_order->status = PaymentStatusType::OFFLINE;
		$this->test_order->save();

		$original_transaction_id = $this->test_order->transaction_id;
		$original_email = $this->test_order->email;
		$original_amount = $this->test_order->amount_cents;

		$response = $this->actingAs($this->admin)
			->postJson('Shop/Order/' . $this->test_order->id);

		$this->assertNoContent($response);

		// Refresh order from database
		$this->test_order->refresh();

		// Verify only status changed
		$this->assertEquals(PaymentStatusType::COMPLETED, $this->test_order->status);
		$this->assertEquals($original_transaction_id, $this->test_order->transaction_id);
		$this->assertEquals($original_email, $this->test_order->email);
		$this->assertEquals($original_amount, $this->test_order->amount_cents);
	}

	/**
	 * Test marking multiple offline orders as paid sequentially.
	 *
	 * @return void
	 */
	public function testMarkMultipleOfflineOrdersAsPaid(): void
	{

		// Create additional offline orders
		$order2 = Order::factory()
			->withTransactionId(Str::uuid()->toString())
			->withEmail('test@example.com')
			->offline()
			->withAmountCents(1999)
			->create();
		OrderItem::factory()->forOrder($order2)->forPurchasable($this->purchasable1)->forPhoto($this->photo1)->fullSize()->create();

		$order3 = Order::factory()
			->withTransactionId(Str::uuid()->toString())
			->withEmail('test@example.com')
			->offline()
			->withAmountCents(1999)
			->create();
		OrderItem::factory()->forOrder($order3)->forPurchasable($this->purchasable1)->forPhoto($this->photo1)->fullSize()->create();

		// Set original test order to offline
		$this->test_order->status = PaymentStatusType::OFFLINE;
		$this->test_order->save();

		// Mark all orders as paid
		$response1 = $this->actingAs($this->admin)
			->postJson('Shop/Order/' . $this->test_order->id);

		$response2 = $this->actingAs($this->admin)
			->postJson('Shop/Order/' . $order2->id);

		$response3 = $this->actingAs($this->admin)
			->postJson('Shop/Order/' . $order3->id);

		$this->assertNoContent($response1);
		$this->assertNoContent($response2);
		$this->assertNoContent($response3);

		// Verify all orders were updated
		$this->assertDatabaseHas('orders', [
			'id' => $this->test_order->id,
			'status' => PaymentStatusType::COMPLETED->value,
		]);

		$this->assertDatabaseHas('orders', [
			'id' => $order2->id,
			'status' => PaymentStatusType::COMPLETED->value,
		]);

		$this->assertDatabaseHas('orders', [
			'id' => $order3->id,
			'status' => PaymentStatusType::COMPLETED->value,
		]);
	}
}
