<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
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

namespace Tests\Unit\Models;

use App\Enum\OmnipayProviderType;
use App\Enum\PaymentStatusType;
use App\Enum\PurchasableSizeVariantType;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\AbstractTestCase;

class OrderTest extends AbstractTestCase
{
	use DatabaseTransactions;

	public function testCalculateTotalSumsItemPrices(): void
	{
		$order = Order::factory()->create();
		OrderItem::factory()->forOrder($order)->forPhoto()->withPriceCents(500)->create();
		OrderItem::factory()->forOrder($order)->forPhoto()->withPriceCents(750)->create();

		$total = $order->fresh()->calculateTotal();

		self::assertEquals(1250, $total->getAmount());
	}

	public function testUpdateTotalPersistsCalculatedAmount(): void
	{
		$order = Order::factory()->withAmountCents(0)->create();
		OrderItem::factory()->forOrder($order)->forPhoto()->withPriceCents(999)->create();

		$fetched = $order->fresh();
		$result = $fetched->updateTotal();

		self::assertSame($fetched, $result);
		self::assertEquals(999, $order->fresh()->amount_cents->getAmount());
	}

	public function testMarkAsPaidSetsStatusAndTimestamp(): void
	{
		$order = Order::factory()->pending()->create();
		self::assertNull($order->paid_at);

		$order->markAsPaid('txn-12345');

		self::assertEquals('txn-12345', $order->transaction_id);
		self::assertEquals(PaymentStatusType::COMPLETED, $order->status);
		self::assertNotNull($order->paid_at);

		$order->refresh();
		self::assertEquals(PaymentStatusType::COMPLETED, $order->status);
	}

	public function testFindByTransactionId(): void
	{
		$order = Order::factory()->withTransactionId('find-me-txn')->create();

		self::assertEquals($order->id, Order::findByTransactionId('find-me-txn')->id);
		self::assertNull(Order::findByTransactionId('does-not-exist'));
	}

	public function testGetOrdersForUserReturnsOnlyThatUsersOrdersNewestFirst(): void
	{
		$user = User::factory()->create();
		$other_user = User::factory()->create();

		$older = Order::factory()->forUser($user)->create(['created_at' => now()->subDay()]);
		$newer = Order::factory()->forUser($user)->create(['created_at' => now()]);
		Order::factory()->forUser($other_user)->create();

		$orders = Order::getOrdersForUser($user);

		self::assertCount(2, $orders);
		self::assertEquals($newer->id, $orders->first()->id);
		self::assertEquals($older->id, $orders->last()->id);
	}

	public function testGetOrdersByEmailReturnsMatchingOrdersNewestFirst(): void
	{
		$older = Order::factory()->withEmail('buyer@example.com')->create(['created_at' => now()->subDay()]);
		$newer = Order::factory()->withEmail('buyer@example.com')->create(['created_at' => now()]);
		Order::factory()->withEmail('someone-else@example.com')->create();

		$orders = Order::getOrdersByEmail('buyer@example.com');

		self::assertCount(2, $orders);
		self::assertEquals($newer->id, $orders->first()->id);
		self::assertEquals($older->id, $orders->last()->id);
	}

	public function testCanCheckoutRequiresCheckoutableStatusAndAtLeastOneItem(): void
	{
		$empty_pending = Order::factory()->pending()->create();
		self::assertFalse($empty_pending->canCheckout());

		$with_item = Order::factory()->canCheckout()->create();
		self::assertTrue($with_item->canCheckout());

		$completed = Order::factory()->completed()->create();
		OrderItem::factory()->forOrder($completed)->forPhoto()->create();
		self::assertFalse($completed->fresh()->canCheckout());
	}

	public function testCanAddItemsMatchesStatusCapability(): void
	{
		self::assertTrue(Order::factory()->pending()->create()->canAddItems());
		self::assertFalse(Order::factory()->completed()->create()->canAddItems());
	}

	public function testCanProcessPaymentFalseWhenNotCheckoutable(): void
	{
		$order = Order::factory()->pending()->create();
		self::assertFalse($order->canProcessPayment());
	}

	public function testCanProcessPaymentFalseWithoutProvider(): void
	{
		$order = Order::factory()->has(OrderItem::factory()->forPhoto()->count(1), 'items')->pending()->withEmail()->create();
		self::assertNull($order->provider);
		self::assertFalse($order->canProcessPayment());
	}

	public function testCanProcessPaymentFalseWhenNoEmailAndFullVariantPresent(): void
	{
		$order = Order::factory()->withProvider(OmnipayProviderType::DUMMY)->pending()->create();
		OrderItem::factory()->forOrder($order)->forPhoto()->fullSize()->create();

		self::assertFalse($order->fresh()->canProcessPayment());
	}

	public function testCanProcessPaymentFalseWhenNoEmailAndNoUser(): void
	{
		$order = Order::factory()->withProvider(OmnipayProviderType::DUMMY)->pending()->create();
		OrderItem::factory()->forOrder($order)->forPhoto()->mediumSize()->create();

		self::assertNull($order->email);
		self::assertNull($order->user_id);
		self::assertFalse($order->fresh()->canProcessPayment());
	}

	public function testCanProcessPaymentTrueWhenLoggedInUserAndNoFullVariant(): void
	{
		$user = User::factory()->create();
		$order = Order::factory()->forUser($user)->withProvider(OmnipayProviderType::DUMMY)->pending()->create();
		OrderItem::factory()->forOrder($order)->forPhoto()->mediumSize()->create();

		self::assertTrue($order->fresh()->canProcessPayment());
	}

	public function testCanProcessPaymentTrueWithEmailRegardlessOfVariant(): void
	{
		$order = Order::factory()->withProvider(OmnipayProviderType::DUMMY)->pending()->withEmail()->create();
		OrderItem::factory()->forOrder($order)->forPhoto()->fullSize()->create();

		self::assertTrue($order->fresh()->canProcessPayment());
	}

	public function testCanProcessPaymentFalseWhenPrintItemMissingShippingFields(): void
	{
		$order = Order::factory()->withProvider(OmnipayProviderType::DUMMY)->pending()->withEmail()->create();
		OrderItem::factory()->forOrder($order)->forPhoto()->create([
			'size_variant_type' => PurchasableSizeVariantType::MEDIUM,
			'is_print' => true,
		]);

		self::assertFalse($order->fresh()->canProcessPayment());
	}

	public function testCanProcessPaymentTrueWhenPrintItemHasFullShippingAddress(): void
	{
		$order = Order::factory()->withProvider(OmnipayProviderType::DUMMY)->pending()->withEmail()->create([
			'shipping_street_name' => 'Main Street',
			'shipping_city' => 'Springfield',
			'shipping_post_code' => '12345',
			'shipping_country' => 'US',
		]);
		OrderItem::factory()->forOrder($order)->forPhoto()->create([
			'size_variant_type' => PurchasableSizeVariantType::MEDIUM,
			'is_print' => true,
		]);

		self::assertTrue($order->fresh()->canProcessPayment());
	}
}
