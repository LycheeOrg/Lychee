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

namespace Tests\Webshop\OrderManagement;

use App\Enum\OmnipayProviderType;
use App\Enum\PaymentStatusType;
use App\Models\Order;
use Illuminate\Support\Str;
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\RequirePro;

/**
 * Test class for shipping address fields in OrderResource.
 *
 * Tests T-043-36: OrderResource exposes all 6 shipping address fields
 * when retrieving a specific order.
 */
class OrderShippingAddressDisplayTest extends BaseApiWithDataTest
{
	use RequirePro;

	private Order $order_with_shipping;
	private Order $order_without_shipping;

	public function setUp(): void
	{
		parent::setUp();
		$this->requirePro();

		$this->order_with_shipping = Order::factory()
			->forUser($this->userMayUpload1)
			->withTransactionId(Str::uuid()->toString())
			->withProvider(OmnipayProviderType::DUMMY)
			->withStatus(PaymentStatusType::COMPLETED)
			->withEmail($this->userMayUpload1->email)
			->create([
				'shipping_street_name' => 'Rue de la Paix',
				'shipping_street_number' => '5',
				'shipping_additional_info' => null,
				'shipping_city' => 'Paris',
				'shipping_post_code' => '75001',
				'shipping_country' => 'FR',
			]);

		$this->order_without_shipping = Order::factory()
			->forUser($this->userMayUpload1)
			->withTransactionId(Str::uuid()->toString())
			->withProvider(OmnipayProviderType::DUMMY)
			->withStatus(PaymentStatusType::COMPLETED)
			->withEmail($this->userMayUpload1->email)
			->create();
	}

	public function tearDown(): void
	{
		$this->resetPro();
		parent::tearDown();
	}

	public function testOrderResponseIncludesShippingFields(): void
	{
		$response = $this->actingAs($this->userMayUpload1)
			->getJson("Shop/Order/{$this->order_with_shipping->id}");

		$this->assertOk($response);
		$response->assertJsonStructure([
			'shipping_street_name',
			'shipping_street_number',
			'shipping_additional_info',
			'shipping_city',
			'shipping_post_code',
			'shipping_country',
		]);
	}

	public function testOrderResponseReturnsCorrectShippingAddress(): void
	{
		$response = $this->actingAs($this->userMayUpload1)
			->getJson("Shop/Order/{$this->order_with_shipping->id}");

		$this->assertOk($response);
		$response->assertJson([
			'shipping_street_name' => 'Rue de la Paix',
			'shipping_street_number' => '5',
			'shipping_additional_info' => null,
			'shipping_city' => 'Paris',
			'shipping_post_code' => '75001',
			'shipping_country' => 'FR',
		]);
	}

	public function testOrderResponseReturnsNullShippingFieldsWhenNotSet(): void
	{
		$response = $this->actingAs($this->userMayUpload1)
			->getJson("Shop/Order/{$this->order_without_shipping->id}");

		$this->assertOk($response);
		$response->assertJson([
			'shipping_street_name' => null,
			'shipping_city' => null,
			'shipping_country' => null,
		]);
	}
}
