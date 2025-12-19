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
use App\Actions\Shop\Gateway\OrderCreatedResponse;
use Tests\AbstractTestCase;

/**
 * Test class for CapturedResponse.
 *
 * This class tests the response object returned when a payment is
 * successfully captured. It extends OrderCreatedResponse and inherits
 * all of its behavior, indicating successful payment completion.
 */
class CapturedResponseTest extends AbstractTestCase
{
	/**
	 * Test successful payment capture response initialization.
	 *
	 * @return void
	 */
	public function testConstructor(): void
	{
		$transaction_reference = 'CAP-123456789';
		$response = new CapturedResponse($transaction_reference);

		$this->assertInstanceOf(CapturedResponse::class, $response);
		$this->assertInstanceOf(OrderCreatedResponse::class, $response);
		$this->assertEquals($transaction_reference, $response->transaction_reference);
	}

	/**
	 * Test isSuccessful returns true for captured payment.
	 *
	 * @return void
	 */
	public function testIsSuccessful(): void
	{
		$response = new CapturedResponse('CAP-123456789');

		$this->assertTrue($response->isSuccessful());
	}

	/**
	 * Test isRedirect returns false since capture doesn't redirect.
	 *
	 * @return void
	 */
	public function testIsRedirect(): void
	{
		$response = new CapturedResponse('CAP-123456789');

		$this->assertFalse($response->isRedirect());
	}

	/**
	 * Test isCancelled returns false for successful capture.
	 *
	 * @return void
	 */
	public function testIsCancelled(): void
	{
		$response = new CapturedResponse('CAP-123456789');

		$this->assertFalse($response->isCancelled());
	}

	/**
	 * Test getTransactionReference returns the capture ID.
	 *
	 * @return void
	 */
	public function testGetTransactionReference(): void
	{
		$transaction_reference = 'CAP-987654321';
		$response = new CapturedResponse($transaction_reference);

		$this->assertEquals($transaction_reference, $response->getTransactionReference());
	}

	/**
	 * Test inheritance from OrderCreatedResponse.
	 *
	 * @return void
	 */
	public function testInheritanceFromOrderCreatedResponse(): void
	{
		$response = new CapturedResponse('CAP-123456789');

		// Should inherit all behavior from parent
		$this->assertTrue($response->isSuccessful());
		$this->assertFalse($response->isRedirect());
		$this->assertFalse($response->isCancelled());
	}

	/**
	 * Test send returns itself for method chaining.
	 *
	 * @return void
	 */
	public function testSend(): void
	{
		$response = new CapturedResponse('CAP-123456789');
		$result = $response->send();

		$this->assertSame($response, $result);
	}
}
