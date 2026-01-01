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

namespace Tests\Webshop\Gateway;

use App\Actions\Shop\Gateway\OrderCreatedResponse;
use Tests\AbstractTestCase;

/**
 * Test class for OrderCreatedResponse.
 *
 * This class tests the response object returned when a payment provider
 * successfully creates an order. The response implements Omnipay's
 * ResponseInterface to provide consistent handling across providers.
 */
class OrderCreatedResponseTest extends AbstractTestCase
{
	/**
	 * Test successful order creation response initialization.
	 *
	 * @return void
	 */
	public function testConstructor(): void
	{
		$transaction_reference = 'PAY-123456789';
		$response = new OrderCreatedResponse($transaction_reference);

		$this->assertInstanceOf(OrderCreatedResponse::class, $response);
		$this->assertEquals($transaction_reference, $response->transaction_reference);
	}

	/**
	 * Test isSuccessful returns true for order creation.
	 *
	 * @return void
	 */
	public function testIsSuccessful(): void
	{
		$response = new OrderCreatedResponse('PAY-123456789');

		$this->assertTrue($response->isSuccessful());
	}

	/**
	 * Test isRedirect returns false since order creation doesn't redirect.
	 *
	 * @return void
	 */
	public function testIsRedirect(): void
	{
		$response = new OrderCreatedResponse('PAY-123456789');

		$this->assertFalse($response->isRedirect());
	}

	/**
	 * Test isCancelled returns false for successful order creation.
	 *
	 * @return void
	 */
	public function testIsCancelled(): void
	{
		$response = new OrderCreatedResponse('PAY-123456789');

		$this->assertFalse($response->isCancelled());
	}

	/**
	 * Test getTransactionReference returns the PayPal order ID.
	 *
	 * @return void
	 */
	public function testGetTransactionReference(): void
	{
		$transaction_reference = 'PAY-987654321';
		$response = new OrderCreatedResponse($transaction_reference);

		$this->assertEquals($transaction_reference, $response->getTransactionReference());
	}

	/**
	 * Test send returns itself for method chaining.
	 *
	 * @return void
	 */
	public function testSend(): void
	{
		$response = new OrderCreatedResponse('PAY-123456789');
		$result = $response->send();

		$this->assertSame($response, $result);
	}

	/**
	 * Test getRequest throws exception as it's not implemented.
	 *
	 * @return void
	 */
	public function testGetRequestThrowsException(): void
	{
		$response = new OrderCreatedResponse('PAY-123456789');

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Not implemented');
		$response->getRequest();
	}

	/**
	 * Test getMessage throws exception as it's not implemented.
	 *
	 * @return void
	 */
	public function testGetMessageThrowsException(): void
	{
		$response = new OrderCreatedResponse('PAY-123456789');

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Not implemented');
		$response->getMessage();
	}

	/**
	 * Test getCode throws exception as it's not implemented.
	 *
	 * @return void
	 */
	public function testGetCodeThrowsException(): void
	{
		$response = new OrderCreatedResponse('PAY-123456789');

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Not implemented');
		$response->getCode();
	}

	/**
	 * Test getData throws exception as it's not implemented.
	 *
	 * @return void
	 */
	public function testGetDataThrowsException(): void
	{
		$response = new OrderCreatedResponse('PAY-123456789');

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Not implemented');
		$response->getData();
	}
}
