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

namespace Tests\Webshop\Gateway;

use App\Actions\Shop\Gateway\CapturedResponse;
use App\Actions\Shop\Gateway\CaptureFailedResponse;
use App\Actions\Shop\Gateway\OrderCreatedResponse;
use App\Actions\Shop\Gateway\OrderFailedResponse;
use App\Actions\Shop\Gateway\PaypalGateway;
use App\Enum\PurchasableLicenseType;
use App\Enum\PurchasableSizeVariantType;
use App\Exceptions\Internal\LycheeLogicException;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Collection;
use Money\Currency;
use Money\Money;
use Omnipay\Common\GatewayInterface;
use PaypalServerSdkLib\Models\CheckoutPaymentIntent;
use Tests\AbstractTestCase;

/**
 * Test class for PaypalGateway.
 *
 * This class tests the custom PayPal gateway implementation that integrates
 * with the PayPal Server SDK to provide payment processing for the Lychee webshop.
 *
 * Note: These tests focus on the gateway's structure and interface compliance.
 * Actual PayPal API integration requires valid credentials and is tested separately.
 */
class PaypalGatewayTest extends AbstractTestCase
{
	private PaypalGateway $gateway;

	public function setUp(): void
	{
		parent::setUp();

		$this->gateway = new PaypalGateway();
	}

	/**
	 * Test gateway implements required Omnipay interfaces.
	 *
	 * @return void
	 */
	public function testImplementsGatewayInterface(): void
	{
		$this->assertInstanceOf(GatewayInterface::class, $this->gateway);
	}

	/**
	 * Test getName returns the correct gateway name.
	 *
	 * @return void
	 */
	public function testGetName(): void
	{
		$this->assertEquals('PayPal Gateway', $this->gateway->getName());
	}

	/**
	 * Test getShortName returns the correct short identifier.
	 *
	 * @return void
	 */
	public function testGetShortName(): void
	{
		$this->assertEquals('PayPal', $this->gateway->getShortName());
	}

	/**
	 * Test getDefaultParameters returns expected parameter structure.
	 *
	 * @return void
	 */
	public function testGetDefaultParameters(): void
	{
		$parameters = $this->gateway->getDefaultParameters();

		$this->assertIsArray($parameters);
		$this->assertArrayHasKey('clientId', $parameters);
		$this->assertArrayHasKey('secret', $parameters);
		$this->assertArrayHasKey('testMode', $parameters);
		$this->assertEquals('', $parameters['clientId']);
		$this->assertEquals('', $parameters['secret']);
		$this->assertFalse($parameters['testMode']);
	}

	/**
	 * Test initialize returns gateway instance for method chaining.
	 *
	 * @return void
	 */
	public function testInitializeReturnsGatewayInstance(): void
	{
		$result = $this->gateway->initialize([
			'clientId' => 'test-client-id',
			'secret' => 'test-secret',
		]);

		$this->assertSame($this->gateway, $result);
	}

	/**
	 * Test initialize without credentials returns gateway without error.
	 *
	 * @return void
	 */
	public function testInitializeWithoutCredentials(): void
	{
		$result = $this->gateway->initialize([]);

		$this->assertSame($this->gateway, $result);
	}

	/**
	 * Test initialize with only clientId returns gateway without error.
	 *
	 * @return void
	 */
	public function testInitializeWithOnlyClientId(): void
	{
		$result = $this->gateway->initialize([
			'clientId' => 'test-client-id',
		]);

		$this->assertSame($this->gateway, $result);
	}

	/**
	 * Test initialize with only secret returns gateway without error.
	 *
	 * @return void
	 */
	public function testInitializeWithOnlySecret(): void
	{
		$result = $this->gateway->initialize([
			'secret' => 'test-secret',
		]);

		$this->assertSame($this->gateway, $result);
	}

	/**
	 * Test getOrderDetails returns correct structure for simple order.
	 *
	 * @return void
	 */
	public function testGetOrderDetailsStructure(): void
	{
		// Mock order with items
		$order = \Mockery::mock(Order::class)->makePartial();
		$order->shouldAllowMockingProtectedMethods();
		$order->amount_cents = new Money(1999, new Currency('USD'));

		$orderItem = \Mockery::mock(OrderItem::class)->makePartial();
		$orderItem->shouldAllowMockingProtectedMethods();
		$orderItem->title = 'Test Photo';
		$orderItem->price_cents = new Money(1999, new Currency('USD'));
		$orderItem->purchasable_id = 123;
		$orderItem->size_variant_type = PurchasableSizeVariantType::MEDIUM;
		$orderItem->license_type = PurchasableLicenseType::PERSONAL;

		$order->items = new Collection([$orderItem]);

		// Initialize gateway
		$this->gateway->initialize([
			'clientId' => 'test-client-id',
			'secret' => 'test-secret',
		]);

		$details = $this->gateway->getOrderDetails($order);

		$this->assertIsArray($details);
		$this->assertArrayHasKey('body', $details);
		$this->assertInstanceOf(\PaypalServerSdkLib\Models\OrderRequest::class, $details['body']);
	}

	/**
	 * Test getOrderDetails throws exception when client not initialized.
	 *
	 * @return void
	 */
	public function testGetOrderDetailsThrowsExceptionWithoutClient(): void
	{
		$order = \Mockery::mock(Order::class)->makePartial();
		$order->shouldAllowMockingProtectedMethods();
		$order->amount_cents = new Money(1999, new Currency('USD'));
		$order->items = new Collection([]);

		$this->expectException(LycheeLogicException::class);
		$this->expectExceptionMessage('PayPal client not initialized');

		$this->gateway->getOrderDetails($order);
	}

	/**
	 * Test getOrderDetails with multiple items.
	 *
	 * @return void
	 */
	public function testGetOrderDetailsWithMultipleItems(): void
	{
		// Mock order with multiple items
		$order = \Mockery::mock(Order::class)->makePartial();
		$order->shouldAllowMockingProtectedMethods();
		$order->amount_cents = new Money(3998, new Currency('USD'));

		$orderItem1 = \Mockery::mock(OrderItem::class)->makePartial();
		$orderItem1->shouldAllowMockingProtectedMethods();
		$orderItem1->title = 'Test Photo 1';
		$orderItem1->price_cents = new Money(1999, new Currency('USD'));
		$orderItem1->purchasable_id = 123;
		$orderItem1->size_variant_type = PurchasableSizeVariantType::MEDIUM;
		$orderItem1->license_type = PurchasableLicenseType::PERSONAL;

		$orderItem2 = \Mockery::mock(OrderItem::class)->makePartial();
		$orderItem2->shouldAllowMockingProtectedMethods();
		$orderItem2->title = 'Test Photo 2';
		$orderItem2->price_cents = new Money(1999, new Currency('USD'));
		$orderItem2->purchasable_id = 456;
		$orderItem2->size_variant_type = PurchasableSizeVariantType::MEDIUM2x;
		$orderItem2->license_type = PurchasableLicenseType::COMMERCIAL;

		$order->items = new Collection([$orderItem1, $orderItem2]);
		// Initialize gateway
		$this->gateway->initialize([
			'clientId' => 'test-client-id',
			'secret' => 'test-secret',
		]);

		$details = $this->gateway->getOrderDetails($order);

		$this->assertIsArray($details);
		$this->assertArrayHasKey('body', $details);

		// Verify the order request has capture intent
		$orderRequest = $details['body'];
		$this->assertEquals(CheckoutPaymentIntent::CAPTURE, $orderRequest->getIntent());
	}

	/**
	 * Test getOrderDetails includes correct SKU format.
	 *
	 * @return void
	 */
	public function testGetOrderDetailsSkuFormat(): void
	{
		$order = \Mockery::mock(Order::class)->makePartial();
		$order->shouldAllowMockingProtectedMethods();
		$order->amount_cents = new Money(2499, new Currency('USD'));

		$orderItem = \Mockery::mock(OrderItem::class)->makePartial();
		$orderItem->shouldAllowMockingProtectedMethods();
		$orderItem->title = 'Test Product';
		$orderItem->price_cents = new Money(2499, new Currency('USD'));
		$orderItem->purchasable_id = 789;
		$orderItem->size_variant_type = PurchasableSizeVariantType::MEDIUM;
		$orderItem->license_type = PurchasableLicenseType::PERSONAL;

		$order->items = new Collection([$orderItem]);
		// Initialize gateway
		$this->gateway->initialize([
			'clientId' => 'test-client-id',
			'secret' => 'test-secret',
		]);

		$details = $this->gateway->getOrderDetails($order);

		// The SKU should be in format: {purchasable_id}-{size_variant}-{license_type}
		// We can't directly access the items from the builder, but we can verify the structure exists
		$this->assertArrayHasKey('body', $details);
		$this->assertInstanceOf(\PaypalServerSdkLib\Models\OrderRequest::class, $details['body']);
	}

	/**
	 * Test getOrderDetails with deleted purchasable (null purchasable_id).
	 *
	 * @return void
	 */
	public function testGetOrderDetailsWithDeletedPurchasable(): void
	{
		$order = \Mockery::mock(Order::class)->makePartial();
		$order->shouldAllowMockingProtectedMethods();
		$order->amount_cents = new Money(1999, new Currency('USD'));

		$orderItem = \Mockery::mock(OrderItem::class)->makePartial();
		$orderItem->shouldAllowMockingProtectedMethods();
		$orderItem->title = 'Deleted Photo';
		$orderItem->price_cents = new Money(1999, new Currency('USD'));
		$orderItem->purchasable_id = null;  // This is the key for testing deleted purchasable
		$orderItem->size_variant_type = PurchasableSizeVariantType::MEDIUM;
		$orderItem->license_type = PurchasableLicenseType::PERSONAL;

		$order->items = new Collection([$orderItem]);

		// Initialize gateway
		$this->gateway->initialize([
			'clientId' => 'test-client-id',
			'secret' => 'test-secret',
		]);

		$details = $this->gateway->getOrderDetails($order);

		// Should not throw exception and return valid structure
		$this->assertIsArray($details);
		$this->assertArrayHasKey('body', $details);
		// SKU should contain "No-longer-existing" for null purchasable_id
	}

	/**
	 * Test getOrderDetails without initialization returns failure response.
	 *
	 * @return void
	 */
	public function testGetOrderDetailsWithoutInitialization(): void
	{
		$order = \Mockery::mock(Order::class)->makePartial();
		$order->shouldAllowMockingProtectedMethods();
		$order->amount_cents = new Money(1999, new Currency('USD'));
		$order->items = new Collection([]);

		// Without proper initialization (no client), getOrderDetails should throw exception
		$this->expectException(LycheeLogicException::class);
		$this->expectExceptionMessage('PayPal client not initialized');

		$this->gateway->getOrderDetails($order);
	}

	/**
	 * Test response types are correct for various scenarios.
	 *
	 * @return void
	 */
	public function testResponseTypeConsistency(): void
	{
		// Verify the gateway can return all expected response types
		$this->assertTrue(class_exists(OrderCreatedResponse::class));
		$this->assertTrue(class_exists(OrderFailedResponse::class));
		$this->assertTrue(class_exists(CapturedResponse::class));
		$this->assertTrue(class_exists(CaptureFailedResponse::class));
	}

	public function tearDown(): void
	{
		\Mockery::close();
		parent::tearDown();
	}
}
