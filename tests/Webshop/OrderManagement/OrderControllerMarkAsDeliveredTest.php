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

namespace Tests\Webshop\OrderManagement;

use App\Enum\PaymentStatusType;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Str;
use Tests\Webshop\Checkout\BaseCheckoutControllerTest;

/**
 * Test class for OrderController markAsDelivered functionality.
 *
 * This class tests the order mark as delivered (closed) functionality:
 * - Marking completed orders as delivered with proper authorization
 * - Handling different order statuses
 * - Authentication and authorization checks
 * - Invalid order handling
 */
class OrderControllerMarkAsDeliveredTest extends BaseCheckoutControllerTest
{
	/**
	 * Test marking a completed order as delivered from completed status.
	 *
	 * @return void
	 */
	public function testMarkCompletedOrderAsDelivered(): void
	{
		$this->test_order->status = PaymentStatusType::COMPLETED;
		$this->test_order->save();

		$response = $this->actingAs($this->admin)
			->putJson('Shop/Order/' . $this->test_order->id);

		$this->assertNoContent($response);

		$this->assertDatabaseHas('orders', [
			'id' => $this->test_order->id,
			'status' => PaymentStatusType::CLOSED->value,
		]);
	}

	/**
	 * Test marking order as delivered without authentication.
	 *
	 * @return void
	 */
	public function testMarkOrderAsDeliveredWithoutAuth(): void
	{
		$this->test_order->status = PaymentStatusType::COMPLETED;
		$this->test_order->save();

		$response = $this->putJson('Shop/Order/' . $this->test_order->id);

		$this->assertUnauthorized($response);

		// Verify status wasn't changed
		$this->assertDatabaseHas('orders', [
			'id' => $this->test_order->id,
			'status' => PaymentStatusType::COMPLETED->value,
		]);
	}

	/**
	 * Test marking order as delivered as non-admin user.
	 *
	 * @return void
	 */
	public function testMarkOrderAsDeliveredAsNonAdmin(): void
	{
		$this->test_order->status = PaymentStatusType::COMPLETED;
		$this->test_order->save();

		$response = $this->actingAs($this->userMayUpload1)
			->putJson('Shop/Order/' . $this->test_order->id);

		$this->assertForbidden($response);

		// Verify status wasn't changed
		$this->assertDatabaseHas('orders', [
			'id' => $this->test_order->id,
			'status' => PaymentStatusType::COMPLETED->value,
		]);
	}

	/**
	 * Test marking order as delivered with non-existent order ID.
	 *
	 * @return void
	 */
	public function testMarkNonExistentOrderAsDelivered(): void
	{
		$non_existent_id = 99999;

		$response = $this->actingAs($this->admin)
			->putJson('Shop/Order/' . $non_existent_id);

		$this->assertNotFound($response);
	}

	/**
	 * Test marking order as delivered with invalid order ID format.
	 *
	 * @return void
	 */
	public function testMarkOrderAsDeliveredWithInvalidId(): void
	{
		$response = $this->actingAs($this->admin)
			->putJson('Shop/Order/invalid-id');

		$this->assertUnprocessable($response);
		$response->assertJsonValidationErrors(['order_id']);
	}

	/**
	 * Test marking a pending order as delivered (should fail).
	 *
	 * @return void
	 */
	public function testMarkPendingOrderAsDelivered(): void
	{
		$this->test_order->status = PaymentStatusType::PENDING;
		$this->test_order->save();

		$response = $this->actingAs($this->admin)
			->putJson('Shop/Order/' . $this->test_order->id);

		$this->assertNotFound($response);

		// Verify status wasn't changed
		$this->assertDatabaseHas('orders', [
			'id' => $this->test_order->id,
			'status' => PaymentStatusType::PENDING->value,
		]);
	}

	/**
	 * Test marking an offline order as delivered (should fail).
	 *
	 * @return void
	 */
	public function testMarkOfflineOrderAsDelivered(): void
	{
		$this->test_order->status = PaymentStatusType::OFFLINE;
		$this->test_order->save();

		$response = $this->actingAs($this->admin)
			->putJson('Shop/Order/' . $this->test_order->id);

		$this->assertNotFound($response);

		// Verify status wasn't changed
		$this->assertDatabaseHas('orders', [
			'id' => $this->test_order->id,
			'status' => PaymentStatusType::OFFLINE->value,
		]);
	}

	/**
	 * Test marking a cancelled order as delivered (should fail).
	 *
	 * @return void
	 */
	public function testMarkCancelledOrderAsDelivered(): void
	{
		$this->test_order->status = PaymentStatusType::CANCELLED;
		$this->test_order->save();

		$response = $this->actingAs($this->admin)
			->putJson('Shop/Order/' . $this->test_order->id);

		$this->assertNotFound($response);

		// Verify status wasn't changed
		$this->assertDatabaseHas('orders', [
			'id' => $this->test_order->id,
			'status' => PaymentStatusType::CANCELLED->value,
		]);
	}

	/**
	 * Test marking a processing order as delivered (should fail).
	 *
	 * @return void
	 */
	public function testMarkProcessingOrderAsDelivered(): void
	{
		$this->test_order->status = PaymentStatusType::PROCESSING;
		$this->test_order->save();

		$response = $this->actingAs($this->admin)
			->putJson('Shop/Order/' . $this->test_order->id);

		$this->assertNotFound($response);

		// Verify status wasn't changed
		$this->assertDatabaseHas('orders', [
			'id' => $this->test_order->id,
			'status' => PaymentStatusType::PROCESSING->value,
		]);
	}

	/**
	 * Test marking an already closed order as delivered (should fail).
	 *
	 * @return void
	 */
	public function testMarkClosedOrderAsDelivered(): void
	{
		$this->test_order->status = PaymentStatusType::CLOSED;
		$this->test_order->save();

		$response = $this->actingAs($this->admin)
			->putJson('Shop/Order/' . $this->test_order->id);

		$this->assertNotFound($response);

		// Verify status wasn't changed
		$this->assertDatabaseHas('orders', [
			'id' => $this->test_order->id,
			'status' => PaymentStatusType::CLOSED->value,
		]);
	}

	/**
	 * Test that marking order as delivered preserves other order data.
	 *
	 * @return void
	 */
	public function testMarkOrderAsDeliveredPreservesOrderData(): void
	{
		// Set some existing data on the order
		$this->test_order->status = PaymentStatusType::COMPLETED;
		$this->test_order->comment = 'Test comment';
		$this->test_order->email = 'customer@example.com';
		$this->test_order->save();

		$response = $this->actingAs($this->admin)
			->putJson('Shop/Order/' . $this->test_order->id);

		$this->assertNoContent($response);

		// Verify other data is preserved
		$this->assertDatabaseHas('orders', [
			'id' => $this->test_order->id,
			'status' => PaymentStatusType::CLOSED->value,
			'comment' => 'Test comment',
			'email' => 'customer@example.com',
		]);
	}

	/**
	 * Test marking order as delivered updates only the status field.
	 *
	 * @return void
	 */
	public function testMarkOrderAsDeliveredUpdatesOnlyStatus(): void
	{
		// Set order to completed status
		$this->test_order->status = PaymentStatusType::COMPLETED;
		$this->test_order->save();

		$original_transaction_id = $this->test_order->transaction_id;
		$original_email = $this->test_order->email;
		$original_amount = $this->test_order->amount_cents;

		$response = $this->actingAs($this->admin)
			->putJson('Shop/Order/' . $this->test_order->id);

		$this->assertNoContent($response);

		// Refresh order from database
		$this->test_order->refresh();

		// Verify only status changed
		$this->assertEquals(PaymentStatusType::CLOSED, $this->test_order->status);
		$this->assertEquals($original_transaction_id, $this->test_order->transaction_id);
		$this->assertEquals($original_email, $this->test_order->email);
		$this->assertEquals($original_amount, $this->test_order->amount_cents);
	}

	/**
	 * Test marking multiple completed orders as delivered sequentially.
	 *
	 * @return void
	 */
	public function testMarkMultipleCompletedOrdersAsDelivered(): void
	{
		// Create additional completed orders
		$order2 = Order::factory()
			->withTransactionId(Str::uuid()->toString())
			->withEmail('test@example.com')
			->completed()
			->withAmountCents(1999)
			->create();
		OrderItem::factory()->forOrder($order2)->forPurchasable($this->purchasable1)->forPhoto($this->photo1)->fullSize()->create();

		$order3 = Order::factory()
			->withTransactionId(Str::uuid()->toString())
			->withEmail('test@example.com')
			->completed()
			->withAmountCents(1999)
			->create();
		OrderItem::factory()->forOrder($order3)->forPurchasable($this->purchasable1)->forPhoto($this->photo1)->fullSize()->create();

		// Set original test order to completed
		$this->test_order->status = PaymentStatusType::COMPLETED;
		$this->test_order->save();

		// Mark all orders as delivered
		$response1 = $this->actingAs($this->admin)
			->putJson('Shop/Order/' . $this->test_order->id);

		$response2 = $this->actingAs($this->admin)
			->putJson('Shop/Order/' . $order2->id);

		$response3 = $this->actingAs($this->admin)
			->putJson('Shop/Order/' . $order3->id);

		$this->assertNoContent($response1);
		$this->assertNoContent($response2);
		$this->assertNoContent($response3);

		// Verify all orders were updated
		$this->assertDatabaseHas('orders', [
			'id' => $this->test_order->id,
			'status' => PaymentStatusType::CLOSED->value,
		]);

		$this->assertDatabaseHas('orders', [
			'id' => $order2->id,
			'status' => PaymentStatusType::CLOSED->value,
		]);

		$this->assertDatabaseHas('orders', [
			'id' => $order3->id,
			'status' => PaymentStatusType::CLOSED->value,
		]);
	}

	/**
	 * Test the complete order lifecycle: offline -> paid -> delivered.
	 *
	 * @return void
	 */
	public function testCompleteOrderLifecycle(): void
	{
		// Start with offline order
		$this->test_order->status = PaymentStatusType::OFFLINE;
		$this->test_order->save();

		// Step 1: Mark as paid
		$response = $this->actingAs($this->admin)
			->postJson('Shop/Order/' . $this->test_order->id);

		$this->assertNoContent($response);
		$this->assertDatabaseHas('orders', [
			'id' => $this->test_order->id,
			'status' => PaymentStatusType::COMPLETED->value,
		]);

		// Step 2: Mark as delivered
		$response = $this->actingAs($this->admin)
			->putJson('Shop/Order/' . $this->test_order->id);

		$this->assertNoContent($response);
		$this->assertDatabaseHas('orders', [
			'id' => $this->test_order->id,
			'status' => PaymentStatusType::CLOSED->value,
		]);
	}
}
