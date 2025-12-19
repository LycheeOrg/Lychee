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

use App\Actions\Shop\Gateway\OrderFailedResponse;
use Tests\AbstractTestCase;

/**
 * Test class for OrderFailedResponse.
 *
 * This class tests the response object returned when an order creation
 * or processing fails. It extracts error details from provider-specific
 * error formats and provides user-friendly error messages.
 */
class OrderFailedResponseTest extends AbstractTestCase
{
	/**
	 * Test failed response with detailed error information.
	 *
	 * @return void
	 */
	public function testConstructorWithDetailedError(): void
	{
		$details = [
			'details' => [
				[
					'issue' => 'INVALID_REQUEST',
					'description' => 'The request is invalid',
				],
			],
			'debug_id' => 'abc123',
		];

		$response = new OrderFailedResponse($details);

		$this->assertInstanceOf(OrderFailedResponse::class, $response);
		$this->assertEquals('INVALID_REQUEST The request is invalid (abc123)', $response->getMessage());
	}

	/**
	 * Test failed response with simple error message.
	 *
	 * @return void
	 */
	public function testConstructorWithSimpleError(): void
	{
		$details = [
			'error' => 'Connection timeout',
		];

		$response = new OrderFailedResponse($details);

		$this->assertEquals('Connection timeout', $response->getMessage());
	}

	/**
	 * Test failed response with unknown error.
	 *
	 * @return void
	 */
	public function testConstructorWithUnknownError(): void
	{
		$details = [];

		$response = new OrderFailedResponse($details);

		$this->assertEquals('Unknown error', $response->getMessage());
	}

	/**
	 * Test isSuccessful returns false for failed order.
	 *
	 * @return void
	 */
	public function testIsSuccessful(): void
	{
		$response = new OrderFailedResponse(['error' => 'Test error']);

		$this->assertFalse($response->isSuccessful());
	}

	/**
	 * Test isRedirect returns false for failed order.
	 *
	 * @return void
	 */
	public function testIsRedirect(): void
	{
		$response = new OrderFailedResponse(['error' => 'Test error']);

		$this->assertFalse($response->isRedirect());
	}

	/**
	 * Test isCancelled returns false for failed order.
	 *
	 * @return void
	 */
	public function testIsCancelled(): void
	{
		$response = new OrderFailedResponse(['error' => 'Test error']);

		$this->assertFalse($response->isCancelled());
	}

	/**
	 * Test getMessage returns formatted error message.
	 *
	 * @return void
	 */
	public function testGetMessage(): void
	{
		$details = [
			'details' => [
				[
					'issue' => 'PAYMENT_DECLINED',
					'description' => 'Payment was declined by processor',
				],
			],
			'debug_id' => 'xyz789',
		];

		$response = new OrderFailedResponse($details);

		$this->assertEquals('PAYMENT_DECLINED Payment was declined by processor (xyz789)', $response->getMessage());
	}

	/**
	 * Test getMessage with missing debug_id.
	 *
	 * @return void
	 */
	public function testGetMessageWithoutDebugId(): void
	{
		$details = [
			'details' => [
				[
					'issue' => 'VALIDATION_ERROR',
					'description' => 'Invalid data provided',
				],
			],
		];

		$response = new OrderFailedResponse($details);

		$this->assertEquals('VALIDATION_ERROR Invalid data provided ()', $response->getMessage());
	}

	/**
	 * Test send returns itself for method chaining.
	 *
	 * @return void
	 */
	public function testSend(): void
	{
		$response = new OrderFailedResponse(['error' => 'Test error']);
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
		$response = new OrderFailedResponse(['error' => 'Test error']);

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Not implemented');
		$response->getRequest();
	}

	/**
	 * Test getCode throws exception as it's not implemented.
	 *
	 * @return void
	 */
	public function testGetCodeThrowsException(): void
	{
		$response = new OrderFailedResponse(['error' => 'Test error']);

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Not implemented');
		$response->getCode();
	}

	/**
	 * Test getTransactionReference throws exception as it's not implemented.
	 *
	 * @return void
	 */
	public function testGetTransactionReferenceThrowsException(): void
	{
		$response = new OrderFailedResponse(['error' => 'Test error']);

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Not implemented');
		$response->getTransactionReference();
	}

	/**
	 * Test getData throws exception as it's not implemented.
	 *
	 * @return void
	 */
	public function testGetDataThrowsException(): void
	{
		$response = new OrderFailedResponse(['error' => 'Test error']);

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Not implemented');
		$response->getData();
	}
}
