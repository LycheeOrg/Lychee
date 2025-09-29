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

namespace Tests\Webshop;

use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\RequireSE;

/**
 * Test cases for the CheckoutController.
 */
class CheckoutControllerTest extends BaseApiWithDataTest
{
	use RequireSE;

	public function setUp(): void
	{
		parent::setUp();
		$this->requireSE();
	}

	public function tearDown(): void
	{
		$this->resetSe();
		parent::tearDown();
	}

	/**
	 * Test the options endpoint returns configuration settings for checkout.
	 */
	public function testOptions(): void
	{
		// Call the options endpoint with authentication
		$response = $this->actingAs($this->admin)->getJson('Shop/Checkout/Options');

		// Assert successful response
		$this->assertOk($response);

		// Assert response structure for checkout options
		$response->assertJsonStructure([
			'currency',
			'allow_guest_checkout',
			'terms_url',
			'privacy_url',
		]);
	}

	/**
	 * Test the options endpoint requires authentication.
	 */
	public function testOptionsRequiresAuth(): void
	{
		// Call the options endpoint without authentication
		$response = $this->getJson('Shop/Checkout/Options');

		// Assert successful response
		$this->assertOk($response);

		// Assert response structure for checkout options
		$response->assertJsonStructure([
			'currency',
			'allow_guest_checkout',
			'terms_url',
			'privacy_url',
		]);
	}
}