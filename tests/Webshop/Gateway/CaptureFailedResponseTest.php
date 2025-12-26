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

use App\Actions\Shop\Gateway\CaptureFailedResponse;
use App\Actions\Shop\Gateway\OrderFailedResponse;
use Tests\AbstractTestCase;

/**
 * Test class for CaptureFailedResponse.
 *
 * This class tests the response object returned when a payment capture
 * fails. It extends OrderFailedResponse and inherits all error handling
 * behavior, specifically for capture-related failures (declined cards,
 * insufficient funds, etc.).
 */
class CaptureFailedResponseTest extends AbstractTestCase
{
	/**
	 * Test failed capture response initialization.
	 *
	 * @return void
	 */
	public function testConstructor(): void
	{
		$details = [
			'error' => 'Payment capture failed',
		];

		$response = new CaptureFailedResponse($details);

		$this->assertInstanceOf(CaptureFailedResponse::class, $response);
		$this->assertInstanceOf(OrderFailedResponse::class, $response);
	}

	/**
	 * Test capture failure with instrument declined error.
	 *
	 * @return void
	 */
	public function testConstructorWithInstrumentDeclined(): void
	{
		$details = [
			'details' => [
				[
					'issue' => 'INSTRUMENT_DECLINED',
					'description' => 'The payment instrument was declined',
				],
			],
			'debug_id' => 'decline123',
		];

		$response = new CaptureFailedResponse($details);

		$this->assertEquals('INSTRUMENT_DECLINED The payment instrument was declined (decline123)', $response->getMessage());
	}

	/**
	 * Test isSuccessful returns false for failed capture.
	 *
	 * @return void
	 */
	public function testIsSuccessful(): void
	{
		$response = new CaptureFailedResponse(['error' => 'Capture failed']);

		$this->assertFalse($response->isSuccessful());
	}

	/**
	 * Test isRedirect returns false for failed capture.
	 *
	 * @return void
	 */
	public function testIsRedirect(): void
	{
		$response = new CaptureFailedResponse(['error' => 'Capture failed']);

		$this->assertFalse($response->isRedirect());
	}

	/**
	 * Test isCancelled returns false for failed capture.
	 *
	 * @return void
	 */
	public function testIsCancelled(): void
	{
		$response = new CaptureFailedResponse(['error' => 'Capture failed']);

		$this->assertFalse($response->isCancelled());
	}

	/**
	 * Test getMessage with insufficient funds error.
	 *
	 * @return void
	 */
	public function testGetMessageWithInsufficientFunds(): void
	{
		$details = [
			'details' => [
				[
					'issue' => 'INSUFFICIENT_FUNDS',
					'description' => 'Not enough money in account',
				],
			],
			'debug_id' => 'funds456',
		];

		$response = new CaptureFailedResponse($details);

		$this->assertEquals('INSUFFICIENT_FUNDS Not enough money in account (funds456)', $response->getMessage());
	}

	/**
	 * Test inheritance from OrderFailedResponse.
	 *
	 * @return void
	 */
	public function testInheritanceFromOrderFailedResponse(): void
	{
		$response = new CaptureFailedResponse(['error' => 'Test error']);

		// Should inherit all behavior from parent
		$this->assertFalse($response->isSuccessful());
		$this->assertFalse($response->isRedirect());
		$this->assertFalse($response->isCancelled());
		$this->assertEquals('Test error', $response->getMessage());
	}

	/**
	 * Test send returns itself for method chaining.
	 *
	 * @return void
	 */
	public function testSend(): void
	{
		$response = new CaptureFailedResponse(['error' => 'Capture failed']);
		$result = $response->send();

		$this->assertSame($response, $result);
	}

	/**
	 * Test capture failure with simple error message.
	 *
	 * @return void
	 */
	public function testConstructorWithSimpleError(): void
	{
		$details = [
			'error' => 'Capture timeout',
		];

		$response = new CaptureFailedResponse($details);

		$this->assertEquals('Capture timeout', $response->getMessage());
	}

	/**
	 * Test capture failure with unknown error.
	 *
	 * @return void
	 */
	public function testConstructorWithUnknownError(): void
	{
		$details = [];

		$response = new CaptureFailedResponse($details);

		$this->assertEquals('Unknown error', $response->getMessage());
	}
}
