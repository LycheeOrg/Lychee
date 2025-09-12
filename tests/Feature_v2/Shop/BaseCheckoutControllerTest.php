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

namespace Tests\Feature_v2\Shop;

use App\Enum\PaymentStatusType;
use App\Models\Order;
use App\Models\Purchasable;
use App\Services\MoneyService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\RequireSE;

/**
 * Test class for CheckoutController.
 *
 * This class tests the checkout functionality for the shop:
 * - Finalizing payments
 * - Cancelling payments
 *
 * Note: CreateSession tests are in CheckoutCreateSessionControllerTest.php
 * Note: ProcessPayment tests are in CheckoutProcessPaymentControllerTest.php
 * The checkout process manages the transition from basket to completed order.
 */
class BaseCheckoutControllerTest extends BaseApiWithDataTest
{
	use RequireSE;

	protected Purchasable $purchasable1;
	protected Order $test_order;

	public const VALID_CARD_NUMBER_SUCCESS = '4111111111111152'; // Visa test card number that passes Luhn check
	public const VALID_CARD_NUMBER_FAIL = '4111111111111145'; // Visa test card number that fails Luhn check

	public function setUp(): void
	{
		parent::setUp();

		// Create purchasable items for testing
		$this->purchasable1 = Purchasable::factory()
			->forPhoto($this->photo1->id, $this->album1->id)
			->withPrices()
			->create();

		// Create a test order with items
		$this->test_order = new Order([
			'transaction_id' => Str::uuid()->toString(),
			'provider' => null, // Will be set during checkout
			'user_id' => null,
			'email' => '',
			'status' => PaymentStatusType::PENDING,
			'amount_cents' => resolve(MoneyService::class)->createFromCents(1999),
			'comment' => null,
		]);
		$this->test_order->save();

		// Put the order in session to simulate a basket
		Session::put('basket_id', $this->test_order->id);

		$this->requireSe();
	}

	public function tearDown(): void
	{
		$this->resetSe();
		parent::tearDown();
	}
}
