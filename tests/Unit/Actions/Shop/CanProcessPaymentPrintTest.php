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

namespace Tests\Unit\Actions\Shop;

use App\Enum\OmnipayProviderType;
use App\Models\Order;
use App\Models\OrderItem;
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\RequirePro;

/**
 * Unit tests for Order::canProcessPayment() shipping-address guard.
 *
 * Tests T-043-37: when the basket contains at least one print item,
 * canProcessPayment() must return false unless all required shipping
 * address fields are populated.
 */
class CanProcessPaymentPrintTest extends BaseApiWithDataTest
{
	use RequirePro;

	private Order $order;

	public function setUp(): void
	{
		parent::setUp();
		$this->requirePro();

		// Create a plain pending order with provider + email (ready-to-pay state without using
		// Order::factory()->canProcessPayment() which relies on the `has()` magic and guesses
		// the wrong relationship name — the Order model uses items() not orderItem()).
		$this->order = Order::factory()
			->pending()
			->withEmail('test@example.com')
			->withProvider(OmnipayProviderType::DUMMY)
			->create();

		// Attach one digital item so canCheckout() returns true
		OrderItem::factory()
			->forOrder($this->order)
			->originalSize()
			->withTitle('Test Photo')
			->create();

		$this->order->load('items');
	}

	public function tearDown(): void
	{
		$this->resetPro();
		parent::tearDown();
	}

	public function testCanProcessPaymentReturnsTrueWithNoShippingRequired(): void
	{
		// A standard digital order with no print items must not require an address
		$this->order->load('items');
		$this->assertTrue($this->order->canProcessPayment());
	}

	public function testCanProcessPaymentReturnsFalseWhenPrintItemMissingAddress(): void
	{
		// Add a print item but leave shipping address empty
		OrderItem::factory()->forOrder($this->order)->originalSize()->withTitle('Print Photo')->create([
			'is_print' => true,
		]);
		$this->order->unsetRelation('items');
		$this->order->load('items');

		$this->assertFalse($this->order->canProcessPayment());
	}

	public function testCanProcessPaymentReturnsTrueWhenPrintItemHasFullAddress(): void
	{
		// Add a print item and provide all required shipping fields
		OrderItem::factory()->forOrder($this->order)->originalSize()->withTitle('Print Photo')->create([
			'is_print' => true,
		]);

		$this->order->shipping_street_name = '123 Main St';
		$this->order->shipping_city = 'Springfield';
		$this->order->shipping_post_code = '12345';
		$this->order->shipping_country = 'US';
		$this->order->save();
		$this->order->unsetRelation('items');
		$this->order->load('items');

		$this->assertTrue($this->order->canProcessPayment());
	}

	public function testCanProcessPaymentReturnsFalseWhenCityMissing(): void
	{
		OrderItem::factory()->forOrder($this->order)->originalSize()->withTitle('Print Photo')->create([
			'is_print' => true,
		]);

		$this->order->shipping_street_name = '123 Main St';
		$this->order->shipping_city = null;
		$this->order->shipping_post_code = '12345';
		$this->order->shipping_country = 'US';
		$this->order->save();
		$this->order->unsetRelation('items');
		$this->order->load('items');

		$this->assertFalse($this->order->canProcessPayment());
	}

	public function testCanProcessPaymentReturnsFalseWhenPostCodeMissing(): void
	{
		OrderItem::factory()->forOrder($this->order)->originalSize()->withTitle('Print Photo')->create([
			'is_print' => true,
		]);

		$this->order->shipping_street_name = '123 Main St';
		$this->order->shipping_city = 'Springfield';
		$this->order->shipping_post_code = null;
		$this->order->shipping_country = 'US';
		$this->order->save();
		$this->order->unsetRelation('items');
		$this->order->load('items');

		$this->assertFalse($this->order->canProcessPayment());
	}

	public function testCanProcessPaymentReturnsFalseWhenCountryMissing(): void
	{
		OrderItem::factory()->forOrder($this->order)->originalSize()->withTitle('Print Photo')->create([
			'is_print' => true,
		]);

		$this->order->shipping_street_name = '123 Main St';
		$this->order->shipping_city = 'Springfield';
		$this->order->shipping_post_code = '12345';
		$this->order->shipping_country = null;
		$this->order->save();
		$this->order->unsetRelation('items');
		$this->order->load('items');

		$this->assertFalse($this->order->canProcessPayment());
	}
}
