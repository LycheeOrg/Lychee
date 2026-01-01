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

use App\Actions\Shop\CheckoutService;
use App\DTO\CheckoutDTO;
use App\Enum\OmnipayProviderType;
use App\Enum\PaymentStatusType;
use App\Factories\OmnipayFactory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Services\MoneyService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Log;
use Mockery\MockInterface;
use Money\Currency;
use Money\Money;
use Omnipay\Common\GatewayInterface;
use Omnipay\Common\Message\RedirectResponseInterface;
use Omnipay\Common\Message\RequestInterface;
use Omnipay\Common\Message\ResponseInterface;
use Tests\AbstractTestCase;

/**
 * Unit tests for CheckoutService.
 *
 * This class tests the core business logic of the checkout service:
 * - processPayment method with various scenarios
 * - handlePaymentReturn method with different response types
 * - Error handling and logging
 * - Payment status transitions
 */
class CheckoutServiceTest extends AbstractTestCase
{
	use DatabaseTransactions;

	private CheckoutService $checkout_service;
	/** @var OmnipayFactory&MockInterface */
	private OmnipayFactory $omnipay_factory_mock;
	/** @var GatewayInterface&MockInterface */
	private GatewayInterface $gateway_mock;
	private Order $order;

	protected function setUp(): void
	{
		parent::setUp();

		// Create mocks
		$this->omnipay_factory_mock = \Mockery::mock(OmnipayFactory::class);
		$this->gateway_mock = \Mockery::mock(GatewayInterface::class);

		// Create the service with mocked dependencies
		$this->checkout_service = new CheckoutService(
			$this->omnipay_factory_mock,
			resolve(MoneyService::class)
		);

		$this->order = Order::factory()
			->withProvider(OmnipayProviderType::DUMMY)
			->withTransactionId('test-transaction-123')
			->withEmail('test@example.com')
			->pending()
			->withAmountCents(1999)
			->create();

		OrderItem::factory()->forOrder($this->order)->forPhoto()->fullSize()->count(1)->create();
	}

	protected function tearDown(): void
	{
		$this->order->delete();
		\Mockery::close();
		parent::tearDown();
	}

	/**
	 * Test processPayment with successful direct payment (no redirect).
	 *
	 * @return void
	 */
	public function testProcessPaymentSuccessfulDirect(): void
	{
		// Mock response
		$response_mock = \Mockery::mock(ResponseInterface::class);
		$response_mock->shouldReceive('isRedirect')->andReturn(false);
		$response_mock->shouldReceive('isSuccessful')->andReturn(true);
		$response_mock->shouldReceive('getTransactionReference')->andReturn('gateway-ref-123');

		// Mock request
		$request_mock = \Mockery::mock(RequestInterface::class);
		$request_mock->shouldReceive('send')->andReturn($response_mock);

		// Mock gateway
		$this->gateway_mock->shouldReceive('purchase')
			->with(\Mockery::type('array'))
			->andReturn($request_mock);

		// Mock factory
		$this->omnipay_factory_mock->shouldReceive('create_gateway')
			->with(OmnipayProviderType::DUMMY)
			->andReturn($this->gateway_mock);

		// Execute
		$result = $this->checkout_service->processPayment(
			$this->order,
			'https://example.com/return',
			'https://example.com/cancel',
			[]
		);

		// Assert
		$this->assertInstanceOf(CheckoutDTO::class, $result);
		$this->assertTrue($result->is_success);
		$this->assertFalse($result->is_redirect);
		$this->assertEquals('', $result->message);
		$this->assertNull($result->redirect_url);
	}

	/**
	 * Test processPayment with redirect response.
	 *
	 * @return void
	 */
	public function testProcessPaymentWithRedirect(): void
	{
		// Mock canProcessPayment to return true
		/** @var MockInterface&Order $order_mock */
		$order_mock = \Mockery::mock(Order::class)->makePartial();
		$order_mock->shouldReceive('items')->andReturn(collect([1])); // Simulate having items
		$order_mock->shouldReceive('canProcessPayment')->andReturn(true);
		$order_mock->shouldReceive('updateTotal')->once();
		$order_mock->shouldReceive('save')->once(); // Only for status update
		$order_mock->provider = OmnipayProviderType::STRIPE;
		$order_mock->amount_cents = new Money(1999, new Currency('USD'));
		$order_mock->transaction_id = 'test-transaction-123';
		$order_mock->id = 1;
		$order_mock->email = 'test@example.com';

		// Mock redirect response
		$response_mock = \Mockery::mock(RedirectResponseInterface::class);
		$response_mock->shouldReceive('isRedirect')->andReturn(true);
		$response_mock->shouldReceive('getRedirectUrl')->andReturn('https://stripe.com/checkout/123');

		// Mock request
		$request_mock = \Mockery::mock(RequestInterface::class);
		$request_mock->shouldReceive('send')->andReturn($response_mock);

		// Mock gateway
		$this->gateway_mock->shouldReceive('purchase')
			->with(\Mockery::type('array'))
			->andReturn($request_mock);

		// Mock factory
		$this->omnipay_factory_mock->shouldReceive('create_gateway')
			->with(OmnipayProviderType::STRIPE)
			->andReturn($this->gateway_mock);

		// Execute
		$result = $this->checkout_service->processPayment(
			$order_mock,
			'https://example.com/return',
			'https://example.com/cancel',
			[]
		);

		// Assert
		$this->assertInstanceOf(CheckoutDTO::class, $result);
		$this->assertTrue($result->is_success);
		$this->assertTrue($result->is_redirect);
		$this->assertEquals('https://stripe.com/checkout/123', $result->redirect_url);
	}

	/**
	 * Test processPayment with failed payment.
	 *
	 * @return void
	 */
	public function testProcessPaymentFailed(): void
	{
		// Mock failed response
		$response_mock = \Mockery::mock(ResponseInterface::class);
		$response_mock->shouldReceive('isRedirect')->andReturn(false);
		$response_mock->shouldReceive('isSuccessful')->andReturn(false);
		$response_mock->shouldReceive('getMessage')->andReturn('Payment declined');

		// Mock request
		$request_mock = \Mockery::mock(RequestInterface::class);
		$request_mock->shouldReceive('send')->andReturn($response_mock);

		// Mock gateway
		$this->gateway_mock->shouldReceive('purchase')
			->with(\Mockery::type('array'))
			->andReturn($request_mock);

		// Mock factory
		$this->omnipay_factory_mock->shouldReceive('create_gateway')
			->with(OmnipayProviderType::DUMMY)
			->andReturn($this->gateway_mock);

		// Execute
		$result = $this->checkout_service->processPayment(
			$this->order,
			'https://example.com/return',
			'https://example.com/cancel',
			[]
		);

		// Assert
		$this->assertInstanceOf(CheckoutDTO::class, $result);
		$this->assertFalse($result->is_success);
		$this->assertEquals('Payment declined', $result->message);
	}

	/**
	 * Test processPayment when order cannot be processed.
	 *
	 * @return void
	 */
	public function testProcessPaymentOrderCannotBeProcessed(): void
	{
		$this->order->status = PaymentStatusType::COMPLETED;

		// Execute
		$result = $this->checkout_service->processPayment(
			$this->order,
			'https://example.com/return',
			'https://example.com/cancel',
			[]
		);

		// Assert
		$this->assertInstanceOf(CheckoutDTO::class, $result);
		$this->assertFalse($result->is_success);
		$this->assertEquals('Order cannot be checked out.', $result->message);
	}

	/**
	 * Test processPayment with exception handling.
	 *
	 * @return void
	 */
	public function testProcessPaymentWithException(): void
	{
		Log::shouldReceive('error')->once();

		// Mock request to throw exception
		$request_mock = \Mockery::mock(RequestInterface::class);
		$request_mock->shouldReceive('send')->andThrow(new \Exception('Gateway error'));

		// Mock gateway
		$this->gateway_mock->shouldReceive('purchase')
			->with(\Mockery::type('array'))
			->andReturn($request_mock);

		// Mock factory
		$this->omnipay_factory_mock->shouldReceive('create_gateway')
			->with(OmnipayProviderType::DUMMY)
			->andReturn($this->gateway_mock);

		// Execute
		$result = $this->checkout_service->processPayment(
			$this->order,
			'https://example.com/return',
			'https://example.com/cancel',
			[]
		);

		// Assert
		$this->assertInstanceOf(CheckoutDTO::class, $result);
		$this->assertFalse($result->is_success);
		$this->assertEquals('An error occurred while processing the payment. Please try again later.', $result->message);
	}

	/**
	 * Test handlePaymentReturn with successful payment.
	 *
	 * @return void
	 */
	public function testHandlePaymentReturnSuccessful(): void
	{
		$this->order->status = PaymentStatusType::PROCESSING;

		// Mock successful response
		$response_mock = \Mockery::mock(ResponseInterface::class);
		$response_mock->shouldReceive('isSuccessful')->andReturn(true);
		$response_mock->shouldReceive('getTransactionReference')->andReturn('gateway-ref-456');

		// Mock request
		$request_mock = \Mockery::mock(RequestInterface::class);
		$request_mock->shouldReceive('send')->andReturn($response_mock);

		// Mock gateway
		$this->gateway_mock->shouldReceive('completePurchase')
			->with(['payment_id' => 'test-payment-id'])
			->andReturn($request_mock);

		// Mock factory
		$this->omnipay_factory_mock->shouldReceive('create_gateway')
			->with(OmnipayProviderType::DUMMY)
			->andReturn($this->gateway_mock);

		// Execute
		$result = $this->checkout_service->handlePaymentReturn(
			$this->order,
			OmnipayProviderType::DUMMY
		);

		// Assert
		$this->assertSame($this->order, $result);
	}

	/**
	 * Test handlePaymentReturn with failed payment.
	 *
	 * @return void
	 */
	public function testHandlePaymentReturnFailed(): void
	{
		$this->order->status = PaymentStatusType::PROCESSING;

		// Mock failed response
		$response_mock = \Mockery::mock(ResponseInterface::class);
		$response_mock->shouldReceive('isSuccessful')->andReturn(false);

		// Mock request
		$request_mock = \Mockery::mock(RequestInterface::class);
		$request_mock->shouldReceive('send')->andReturn($response_mock);

		// Mock gateway
		$this->gateway_mock->shouldReceive('completePurchase')
			->with(['payment_id' => 'test-payment-id'])
			->andReturn($request_mock);

		// Mock factory
		$this->omnipay_factory_mock->shouldReceive('create_gateway')
			->with(OmnipayProviderType::DUMMY)
			->andReturn($this->gateway_mock);

		// Execute
		$result = $this->checkout_service->handlePaymentReturn(
			$this->order,
			OmnipayProviderType::DUMMY
		);

		// Assert
		$this->assertSame($this->order, $result);
		$this->assertEquals(PaymentStatusType::FAILED, $result->status);
	}

	/**
	 * Test handlePaymentReturn with invalid order status.
	 *
	 * @return void
	 */
	public function testHandlePaymentReturnInvalidStatus(): void
	{
		Log::shouldReceive('info')->once();
		Log::shouldReceive('error')->once();

		$this->order->status = PaymentStatusType::COMPLETED;

		// Mock factory (should still be called)
		$this->omnipay_factory_mock->shouldReceive('create_gateway')
			->with(OmnipayProviderType::DUMMY)
			->andReturn($this->gateway_mock);

		// Execute
		$result = $this->checkout_service->handlePaymentReturn(
			$this->order,
			OmnipayProviderType::DUMMY
		);

		// Assert - should return the order even when exception occurs
		$this->assertSame($this->order, $result);
	}

	/**
	 * Test handlePaymentReturn with gateway exception.
	 *
	 * @return void
	 */
	public function testHandlePaymentReturnWithException(): void
	{
		Log::shouldReceive('info')->once();
		Log::shouldReceive('error')->once();

		$this->order->status = PaymentStatusType::PROCESSING;

		// Mock request to throw exception
		$request_mock = \Mockery::mock(RequestInterface::class);
		$request_mock->shouldReceive('send')->andThrow(new \Exception('Gateway connection error'));

		// Mock gateway
		$this->gateway_mock->shouldReceive('completePurchase')
			->with(['payment_id' => 'test-payment-id'])
			->andReturn($request_mock);

		// Mock factory
		$this->omnipay_factory_mock->shouldReceive('create_gateway')
			->with(OmnipayProviderType::DUMMY)
			->andReturn($this->gateway_mock);

		// Execute
		$result = $this->checkout_service->handlePaymentReturn(
			$this->order,
			OmnipayProviderType::DUMMY
		);

		// Assert - should return the order even when exception occurs
		$this->assertSame($this->order, $result);
	}

	/**
	 * Test processPayment with additional data.
	 *
	 * @return void
	 */
	public function testProcessPaymentWithAdditionalData(): void
	{
		$this->order->status = PaymentStatusType::PENDING;

		// Mock response
		$response_mock = \Mockery::mock(ResponseInterface::class);
		$response_mock->shouldReceive('isRedirect')->andReturn(false);
		$response_mock->shouldReceive('isSuccessful')->andReturn(true);
		$response_mock->shouldReceive('getTransactionReference')->andReturn('gateway-ref-123');

		// Mock request
		$request_mock = \Mockery::mock(RequestInterface::class);
		$request_mock->shouldReceive('send')->andReturn($response_mock);

		// Mock gateway with specific parameter expectations
		$this->gateway_mock->shouldReceive('purchase')
			->with(\Mockery::on(function ($params) {
				return isset($params['card']) &&
					   $params['card']['number'] === '4111111111111111' &&
					   $params['currency'] === 'EUR' &&
					   $params['transactionId'] === 'test-transaction-123' &&
					   $params['email'] === 'test@example.com';
			}))
			->andReturn($request_mock);

		// Mock factory
		$this->omnipay_factory_mock->shouldReceive('create_gateway')
			->with(OmnipayProviderType::DUMMY)
			->andReturn($this->gateway_mock);

		// Execute with additional card data
		$additional_data = [
			'card' => [
				'number' => '4111111111111111',
				'expiryMonth' => '12',
				'expiryYear' => date('Y'),
				'cvv' => '123',
			],
		];

		$result = $this->checkout_service->processPayment(
			$this->order,
			'https://example.com/return',
			'https://example.com/cancel',
			$additional_data
		);

		// Assert
		$this->assertInstanceOf(CheckoutDTO::class, $result);
		$this->assertEquals('', $result->message);
		$this->assertTrue($result->is_success);
	}

	/**
	 * Test that preparePurchaseParameters includes customer details when available.
	 *
	 * @return void
	 */
	public function testProcessPaymentIncludesUserDetails(): void
	{
		$user = new User();
		$user->username = 'John Doe';
		$user->save();

		// Mock canProcessPayment to return true
		$this->order->user_id = $user->id;
		$this->order->load('user');

		// Mock response
		$response_mock = \Mockery::mock(ResponseInterface::class);
		$response_mock->shouldReceive('isRedirect')->andReturn(false);
		$response_mock->shouldReceive('isSuccessful')->andReturn(true);
		$response_mock->shouldReceive('getTransactionReference')->andReturn('gateway-ref-123');

		// Mock request
		$request_mock = \Mockery::mock(RequestInterface::class);
		$request_mock->shouldReceive('send')->andReturn($response_mock);

		// Mock gateway with specific parameter expectations
		$this->gateway_mock->shouldReceive('purchase')
			->with(\Mockery::on(function ($params) {
				return $params['name'] === 'John Doe' &&
					   $params['email'] === 'test@example.com';
			}))
			->andReturn($request_mock);

		// Mock factory
		$this->omnipay_factory_mock->shouldReceive('create_gateway')
			->with(OmnipayProviderType::DUMMY)
			->andReturn($this->gateway_mock);

		// Execute
		$result = $this->checkout_service->processPayment(
			$this->order,
			'https://example.com/return',
			'https://example.com/cancel',
			[]
		);

		// Assert
		$this->assertTrue($result->is_success);
	}
}
