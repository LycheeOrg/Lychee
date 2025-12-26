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

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Purchasable;
use Illuminate\Support\Str;
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\RequirePro;

/**
 * Test class for FlushOldOrders maintenance functionality.
 *
 * This class tests the old orders cleanup functionality:
 * - Counting old orders that can be deleted
 * - Flushing/deleting old orders with proper authorization
 * - Preserving recent orders and orders with users
 * - Authentication and authorization checks
 */
class FlushOldOrdersTest extends BaseApiWithDataTest
{
	use RequirePro;

	protected Purchasable $purchasable1;

	public function setUp(): void
	{
		parent::setUp();
		$this->requirePro();

		// Create purchasable items for testing
		$this->purchasable1 = Purchasable::factory()
			->forPhoto($this->photo1->id, $this->album1->id)
			->withPrices()
			->create();
	}

	public function tearDown(): void
	{
		$this->resetPro();
		parent::tearDown();
	}

	/**
	 * Test checking old orders count without authentication.
	 * Should return 401 Unauthorized.
	 *
	 * @return void
	 */
	public function testCheckOldOrdersCountWithoutAuth(): void
	{
		$response = $this->getJson('Maintenance::oldOrders');

		$this->assertUnauthorized($response);
	}

	/**
	 * Test checking old orders count as non-admin user.
	 * Should return 403 Forbidden.
	 *
	 * @return void
	 */
	public function testCheckOldOrdersCountAsNonAdmin(): void
	{
		$response = $this->actingAs($this->userMayUpload1)
			->getJson('Maintenance::oldOrders');

		$this->assertForbidden($response);
	}

	/**
	 * Test checking old orders count as admin user with no old orders.
	 * Should return 0.
	 *
	 * @return void
	 */
	public function testCheckOldOrdersCountWithNoOldOrders(): void
	{
		$response = $this->actingAs($this->admin)
			->getJson('Maintenance::oldOrders');

		$this->assertOk($response);
		$this->assertEquals(0, $response->json());
	}

	/**
	 * Test checking old orders count as admin user with old orders.
	 * Should return the correct count.
	 *
	 * @return void
	 */
	public function testCheckOldOrdersCountWithOldOrders(): void
	{
		// Create old orders (older than 2 weeks, no user_id, pending status)
		$old_order1 = Order::factory()
			->withTransactionId(Str::uuid()->toString())
			->withEmail('old1@example.com')
			->pending()
			->withAmountCents(1999)
			->create(['created_at' => now()->subWeeks(3)]);
		OrderItem::factory()->forOrder($old_order1)->forPurchasable($this->purchasable1)->forPhoto($this->photo1)->fullSize()->create();

		$old_order2 = Order::factory()
			->withTransactionId(Str::uuid()->toString())
			->withEmail('old2@example.com')
			->pending()
			->withAmountCents(2999)
			->create(['created_at' => now()->subWeeks(4)]);
		OrderItem::factory()->forOrder($old_order2)->forPurchasable($this->purchasable1)->forPhoto($this->photo1)->fullSize()->create();

		$response = $this->actingAs($this->admin)
			->getJson('Maintenance::oldOrders');

		$this->assertOk($response);
		$this->assertEquals(2, $response->json());
	}

	/**
	 * Test checking old orders doesn't count recent orders.
	 * Recent orders should not be included in count.
	 *
	 * @return void
	 */
	public function testCheckOldOrdersDoesNotCountRecentOrders(): void
	{
		// Create recent order (less than 2 weeks old)
		$recent_order = Order::factory()
			->withTransactionId(Str::uuid()->toString())
			->withEmail('recent@example.com')
			->pending()
			->withAmountCents(1999)
			->create(['created_at' => now()->subWeek()]);
		OrderItem::factory()->forOrder($recent_order)->forPurchasable($this->purchasable1)->forPhoto($this->photo1)->fullSize()->create();

		$response = $this->actingAs($this->admin)
			->getJson('Maintenance::oldOrders');

		$this->assertOk($response);
		$this->assertEquals(0, $response->json());
	}

	/**
	 * Test checking old orders doesn't count orders with user_id.
	 * Orders associated with users should not be included.
	 *
	 * @return void
	 */
	public function testCheckOldOrdersDoesNotCountOrdersWithUser(): void
	{
		// Create old order but with user_id
		$order_with_user = Order::factory()
			->forUser($this->userMayUpload1)
			->withTransactionId(Str::uuid()->toString())
			->withEmail($this->userMayUpload1->email)
			->pending()
			->withAmountCents(1999)
			->create(['created_at' => now()->subWeeks(3)]);
		OrderItem::factory()->forOrder($order_with_user)->forPurchasable($this->purchasable1)->forPhoto($this->photo1)->fullSize()->create();

		$response = $this->actingAs($this->admin)
			->getJson('Maintenance::oldOrders');

		$this->assertOk($response);
		$this->assertEquals(0, $response->json());
	}

	/**
	 * Test checking old orders counts orders without items.
	 * Old orders with no items should be included.
	 *
	 * @return void
	 */
	public function testCheckOldOrdersCountsOrdersWithoutItems(): void
	{
		// Create old order without items
		Order::factory()
			->withTransactionId(Str::uuid()->toString())
			->withEmail('empty@example.com')
			->pending()
			->withAmountCents(0)
			->create(['created_at' => now()->subWeeks(3)]);

		$response = $this->actingAs($this->admin)
			->getJson('Maintenance::oldOrders');

		$this->assertOk($response);
		$this->assertEquals(1, $response->json());
	}

	/**
	 * Test checking old orders doesn't count completed orders with items.
	 * Completed orders should not be included even if old.
	 *
	 * @return void
	 */
	public function testCheckOldOrdersDoesNotCountCompletedOrders(): void
	{
		// Create old completed order
		$completed_order = Order::factory()
			->withTransactionId(Str::uuid()->toString())
			->withEmail('completed@example.com')
			->completed()
			->withAmountCents(1999)
			->create(['created_at' => now()->subWeeks(3)]);
		OrderItem::factory()->forOrder($completed_order)->forPurchasable($this->purchasable1)->forPhoto($this->photo1)->fullSize()->create();

		$response = $this->actingAs($this->admin)
			->getJson('Maintenance::oldOrders');

		$this->assertOk($response);
		$this->assertEquals(0, $response->json());
	}

	/**
	 * Test flushing old orders without authentication.
	 * Should return 401 Unauthorized.
	 *
	 * @return void
	 */
	public function testFlushOldOrdersWithoutAuth(): void
	{
		$response = $this->postJson('Maintenance::oldOrders');

		$this->assertUnauthorized($response);
	}

	/**
	 * Test flushing old orders as non-admin user.
	 * Should return 403 Forbidden.
	 *
	 * @return void
	 */
	public function testFlushOldOrdersAsNonAdmin(): void
	{
		$response = $this->actingAs($this->userMayUpload1)
			->postJson('Maintenance::oldOrders');

		$this->assertForbidden($response);
	}

	/**
	 * Test flushing old orders as admin user.
	 * Should delete old orders and return no content.
	 *
	 * @return void
	 */
	public function testFlushOldOrdersAsAdmin(): void
	{
		// Create old order
		$old_order = Order::factory()
			->withTransactionId(Str::uuid()->toString())
			->withEmail('old@example.com')
			->pending()
			->withAmountCents(1999)
			->create(['created_at' => now()->subWeeks(3)]);
		OrderItem::factory()->forOrder($old_order)->forPurchasable($this->purchasable1)->forPhoto($this->photo1)->fullSize()->create();

		$response = $this->actingAs($this->admin)
			->postJson('Maintenance::oldOrders');

		$this->assertNoContent($response);

		// Verify the order was deleted
		$this->assertDatabaseMissing('orders', [
			'id' => $old_order->id,
		]);
	}

	/**
	 * Test flushing old orders deletes associated order items.
	 * Should cascade delete order items when deleting orders.
	 *
	 * @return void
	 */
	public function testFlushOldOrdersDeletesOrderItems(): void
	{
		// Create old order with item
		$old_order = Order::factory()
			->withTransactionId(Str::uuid()->toString())
			->withEmail('old@example.com')
			->pending()
			->withAmountCents(1999)
			->create(['created_at' => now()->subWeeks(3)]);
		$order_item = OrderItem::factory()->forOrder($old_order)->forPurchasable($this->purchasable1)->forPhoto($this->photo1)->fullSize()->create();

		$response = $this->actingAs($this->admin)
			->postJson('Maintenance::oldOrders');

		$this->assertNoContent($response);

		// Verify both order and order item were deleted
		$this->assertDatabaseMissing('orders', [
			'id' => $old_order->id,
		]);

		$this->assertDatabaseMissing('order_items', [
			'id' => $order_item->id,
		]);
	}

	/**
	 * Test flushing old orders preserves recent orders.
	 * Recent orders should not be deleted.
	 *
	 * @return void
	 */
	public function testFlushOldOrdersPreservesRecentOrders(): void
	{
		// Create old order
		$old_order = Order::factory()
			->withTransactionId(Str::uuid()->toString())
			->withEmail('old@example.com')
			->pending()
			->withAmountCents(1999)
			->create(['created_at' => now()->subWeeks(3)]);
		OrderItem::factory()->forOrder($old_order)->forPurchasable($this->purchasable1)->forPhoto($this->photo1)->fullSize()->create();

		// Create recent order
		$recent_order = Order::factory()
			->withTransactionId(Str::uuid()->toString())
			->withEmail('recent@example.com')
			->pending()
			->withAmountCents(2999)
			->create(['created_at' => now()->subWeek()]);
		OrderItem::factory()->forOrder($recent_order)->forPurchasable($this->purchasable1)->forPhoto($this->photo1)->fullSize()->create();

		$response = $this->actingAs($this->admin)
			->postJson('Maintenance::oldOrders');

		$this->assertNoContent($response);

		// Verify old order was deleted
		$this->assertDatabaseMissing('orders', [
			'id' => $old_order->id,
		]);

		// Verify recent order was preserved
		$this->assertDatabaseHas('orders', [
			'id' => $recent_order->id,
		]);
	}

	/**
	 * Test flushing old orders preserves orders with user_id.
	 * Orders associated with users should not be deleted.
	 *
	 * @return void
	 */
	public function testFlushOldOrdersPreservesOrdersWithUser(): void
	{
		// Create old order without user
		$old_order = Order::factory()
			->withTransactionId(Str::uuid()->toString())
			->withEmail('old@example.com')
			->pending()
			->withAmountCents(1999)
			->create(['created_at' => now()->subWeeks(3)]);
		OrderItem::factory()->forOrder($old_order)->forPurchasable($this->purchasable1)->forPhoto($this->photo1)->fullSize()->create();

		// Create old order with user
		$order_with_user = Order::factory()
			->forUser($this->userMayUpload1)
			->withTransactionId(Str::uuid()->toString())
			->withEmail($this->userMayUpload1->email)
			->pending()
			->withAmountCents(2999)
			->create(['created_at' => now()->subWeeks(3)]);
		OrderItem::factory()->forOrder($order_with_user)->forPurchasable($this->purchasable1)->forPhoto($this->photo1)->fullSize()->create();

		$response = $this->actingAs($this->admin)
			->postJson('Maintenance::oldOrders');

		$this->assertNoContent($response);

		// Verify old order without user was deleted
		$this->assertDatabaseMissing('orders', [
			'id' => $old_order->id,
		]);

		// Verify order with user was preserved
		$this->assertDatabaseHas('orders', [
			'id' => $order_with_user->id,
		]);
	}

	/**
	 * Test flushing old orders deletes orders without items.
	 * Empty old orders should be deleted.
	 *
	 * @return void
	 */
	public function testFlushOldOrdersDeletesOrdersWithoutItems(): void
	{
		// Create old order without items
		$empty_order = Order::factory()
			->withTransactionId(Str::uuid()->toString())
			->withEmail('empty@example.com')
			->pending()
			->withAmountCents(0)
			->create(['created_at' => now()->subWeeks(3)]);

		$response = $this->actingAs($this->admin)
			->postJson('Maintenance::oldOrders');

		$this->assertNoContent($response);

		// Verify empty order was deleted
		$this->assertDatabaseMissing('orders', [
			'id' => $empty_order->id,
		]);
	}

	/**
	 * Test flushing old orders preserves completed orders.
	 * Completed orders should not be deleted even if old.
	 *
	 * @return void
	 */
	public function testFlushOldOrdersPreservesCompletedOrders(): void
	{
		// Create old pending order
		$pending_order = Order::factory()
			->withTransactionId(Str::uuid()->toString())
			->withEmail('pending@example.com')
			->pending()
			->withAmountCents(1999)
			->create(['created_at' => now()->subWeeks(3)]);
		OrderItem::factory()->forOrder($pending_order)->forPurchasable($this->purchasable1)->forPhoto($this->photo1)->fullSize()->create();

		// Create old completed order
		$completed_order = Order::factory()
			->withTransactionId(Str::uuid()->toString())
			->withEmail('completed@example.com')
			->completed()
			->withAmountCents(2999)
			->create(['created_at' => now()->subWeeks(3)]);
		OrderItem::factory()->forOrder($completed_order)->forPurchasable($this->purchasable1)->forPhoto($this->photo1)->fullSize()->create();

		$response = $this->actingAs($this->admin)
			->postJson('Maintenance::oldOrders');

		$this->assertNoContent($response);

		// Verify pending order was deleted
		$this->assertDatabaseMissing('orders', [
			'id' => $pending_order->id,
		]);

		// Verify completed order was preserved
		$this->assertDatabaseHas('orders', [
			'id' => $completed_order->id,
		]);
	}

	/**
	 * Test flushing old orders with multiple qualifying orders.
	 * Should delete all old orders that meet criteria.
	 *
	 * @return void
	 */
	public function testFlushOldOrdersWithMultipleOrders(): void
	{
		// Create multiple old orders
		$old_order1 = Order::factory()
			->withTransactionId(Str::uuid()->toString())
			->withEmail('old1@example.com')
			->pending()
			->withAmountCents(1999)
			->create(['created_at' => now()->subWeeks(3)]);
		OrderItem::factory()->forOrder($old_order1)->forPurchasable($this->purchasable1)->forPhoto($this->photo1)->fullSize()->create();

		$old_order2 = Order::factory()
			->withTransactionId(Str::uuid()->toString())
			->withEmail('old2@example.com')
			->pending()
			->withAmountCents(2999)
			->create(['created_at' => now()->subWeeks(4)]);
		OrderItem::factory()->forOrder($old_order2)->forPurchasable($this->purchasable1)->forPhoto($this->photo1)->fullSize()->create();

		$old_order3 = Order::factory()
			->withTransactionId(Str::uuid()->toString())
			->withEmail('old3@example.com')
			->pending()
			->withAmountCents(3999)
			->create(['created_at' => now()->subWeeks(5)]);
		OrderItem::factory()->forOrder($old_order3)->forPurchasable($this->purchasable1)->forPhoto($this->photo1)->fullSize()->create();

		$response = $this->actingAs($this->admin)
			->postJson('Maintenance::oldOrders');

		$this->assertNoContent($response);

		// Verify all old orders were deleted
		$this->assertDatabaseMissing('orders', ['id' => $old_order1->id]);
		$this->assertDatabaseMissing('orders', ['id' => $old_order2->id]);
		$this->assertDatabaseMissing('orders', ['id' => $old_order3->id]);
	}

	/**
	 * Test the complete workflow: check count, flush, verify count is zero.
	 * Should work end-to-end.
	 *
	 * @return void
	 */
	public function testCompleteFlushWorkflow(): void
	{
		// Create old orders
		$old_order1 = Order::factory()
			->withTransactionId(Str::uuid()->toString())
			->withEmail('old1@example.com')
			->pending()
			->withAmountCents(1999)
			->create(['created_at' => now()->subWeeks(3)]);
		OrderItem::factory()->forOrder($old_order1)->forPurchasable($this->purchasable1)->forPhoto($this->photo1)->fullSize()->create();

		$old_order2 = Order::factory()
			->withTransactionId(Str::uuid()->toString())
			->withEmail('old2@example.com')
			->pending()
			->withAmountCents(2999)
			->create(['created_at' => now()->subWeeks(4)]);
		OrderItem::factory()->forOrder($old_order2)->forPurchasable($this->purchasable1)->forPhoto($this->photo1)->fullSize()->create();

		// Step 1: Check count before flush
		$check_response = $this->actingAs($this->admin)
			->getJson('Maintenance::oldOrders');

		$this->assertOk($check_response);
		$this->assertEquals(2, $check_response->json());

		// Step 2: Flush old orders
		$flush_response = $this->actingAs($this->admin)
			->postJson('Maintenance::oldOrders');

		$this->assertNoContent($flush_response);

		// Step 3: Check count after flush
		$check_after_response = $this->actingAs($this->admin)
			->getJson('Maintenance::oldOrders');

		$this->assertOk($check_after_response);
		$this->assertEquals(0, $check_after_response->json());
	}

	/**
	 * Test flushing when no old orders exist.
	 * Should complete successfully with no errors.
	 *
	 * @return void
	 */
	public function testFlushOldOrdersWhenNoneExist(): void
	{
		$response = $this->actingAs($this->admin)
			->postJson('Maintenance::oldOrders');

		$this->assertNoContent($response);
	}

	/**
	 * Test flushing old orders preserves offline orders.
	 * Offline status orders should not be deleted.
	 *
	 * @return void
	 */
	public function testFlushOldOrdersPreservesOfflineOrders(): void
	{
		// Create old pending order
		$pending_order = Order::factory()
			->withTransactionId(Str::uuid()->toString())
			->withEmail('pending@example.com')
			->pending()
			->withAmountCents(1999)
			->create(['created_at' => now()->subWeeks(3)]);
		OrderItem::factory()->forOrder($pending_order)->forPurchasable($this->purchasable1)->forPhoto($this->photo1)->fullSize()->create();

		// Create old offline order
		$offline_order = Order::factory()
			->withTransactionId(Str::uuid()->toString())
			->withEmail('offline@example.com')
			->offline()
			->withAmountCents(2999)
			->create(['created_at' => now()->subWeeks(3)]);
		OrderItem::factory()->forOrder($offline_order)->forPurchasable($this->purchasable1)->forPhoto($this->photo1)->fullSize()->create();

		$response = $this->actingAs($this->admin)
			->postJson('Maintenance::oldOrders');

		$this->assertNoContent($response);

		// Verify pending order was deleted
		$this->assertDatabaseMissing('orders', [
			'id' => $pending_order->id,
		]);

		// Verify offline order was preserved
		$this->assertDatabaseHas('orders', [
			'id' => $offline_order->id,
		]);
	}
}
