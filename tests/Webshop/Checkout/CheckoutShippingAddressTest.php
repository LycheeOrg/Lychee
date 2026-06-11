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

namespace Tests\Webshop\Checkout;

use App\Enum\OmnipayProviderType;

/**
 * Test class for checkout shipping address functionality.
 *
 * Tests T-043-34: CreateSessionRequest shipping address fields are persisted
 * on the order when a session is created.
 */
class CheckoutShippingAddressTest extends BaseCheckoutControllerTest
{
	public function testCreateSessionWithShippingAddress(): void
	{
		$response = $this->postJson('Shop/Checkout/Create-session', [
			'provider' => OmnipayProviderType::DUMMY->value,
			'email' => 'customer@example.com',
			'shipping_street_name' => 'Main Street',
			'shipping_street_number' => '42',
			'shipping_additional_info' => 'Apt 3B',
			'shipping_city' => 'Zurich',
			'shipping_post_code' => '8001',
			'shipping_country' => 'CH',
		]);

		$this->assertCreated($response);

		// Verify shipping address fields were persisted on the order
		$this->assertDatabaseHas('orders', [
			'id' => $this->test_order->id,
			'shipping_street_name' => 'Main Street',
			'shipping_street_number' => '42',
			'shipping_additional_info' => 'Apt 3B',
			'shipping_city' => 'Zurich',
			'shipping_post_code' => '8001',
			'shipping_country' => 'CH',
		]);
	}

	public function testCreateSessionShippingFieldsAreOptional(): void
	{
		// Shipping fields are optional — request without them should succeed
		$response = $this->postJson('Shop/Checkout/Create-session', [
			'provider' => OmnipayProviderType::DUMMY->value,
			'email' => 'customer@example.com',
		]);

		$this->assertCreated($response);

		$this->assertDatabaseHas('orders', [
			'id' => $this->test_order->id,
			'shipping_country' => null,
		]);
	}

	public function testCreateSessionShippingAddressIsReturnedInResponse(): void
	{
		$response = $this->postJson('Shop/Checkout/Create-session', [
			'provider' => OmnipayProviderType::DUMMY->value,
			'email' => 'customer@example.com',
			'shipping_street_name' => 'Bahnhofstrasse',
			'shipping_street_number' => '1',
			'shipping_city' => 'Basel',
			'shipping_post_code' => '4001',
			'shipping_country' => 'CH',
		]);

		$this->assertCreated($response);
		$response->assertJsonStructure([
			'shipping_street_name',
			'shipping_street_number',
			'shipping_additional_info',
			'shipping_city',
			'shipping_post_code',
			'shipping_country',
		]);
		$response->assertJson([
			'shipping_street_name' => 'Bahnhofstrasse',
			'shipping_city' => 'Basel',
			'shipping_country' => 'CH',
		]);
	}

	public function testCreateSessionShippingCountryMaxTwoChars(): void
	{
		$response = $this->postJson('Shop/Checkout/Create-session', [
			'provider' => OmnipayProviderType::DUMMY->value,
			'shipping_country' => 'CHE', // 3-char ISO — should be rejected
		]);

		$this->assertUnprocessable($response);
		$response->assertJsonValidationErrors(['shipping_country']);
	}
}
