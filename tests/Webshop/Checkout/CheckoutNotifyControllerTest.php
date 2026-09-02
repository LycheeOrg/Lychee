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
use App\Enum\PaymentStatusType;
use App\Services\MoneyService;
use Illuminate\Testing\TestResponse;

/**
 * Test class for the Payzum payment notification endpoint.
 *
 * The notification (IPN) is a server-to-server POST signed with
 * HMAC-SHA-512 over the raw request body. It is the only place where an
 * asynchronous (crypto) payment completes an order — these tests cover the
 * happy path, forged signatures, status handling, amount verification and
 * redelivery idempotency.
 */
class CheckoutNotifyControllerTest extends BaseCheckoutControllerTest
{
	public const WEBHOOK_SECRET = 'test-webhook-secret';

	public function setUp(): void
	{
		parent::setUp();

		config(['omnipay.Payzum' => [
			'apiKey' => 'test-api-key',
			'webhookSecret' => self::WEBHOOK_SECRET,
			'testMode' => true,
		]]);

		$this->test_order->status = PaymentStatusType::PROCESSING;
		$this->test_order->provider = OmnipayProviderType::PAYZUM;
		$this->test_order->save();
	}

	/**
	 * Build a notification payload matching the test order.
	 *
	 * @param array $overrides Keys to override in the payload
	 *
	 * @return array
	 */
	protected function payload(array $overrides = []): array
	{
		$money_service = app(MoneyService::class);

		return array_merge([
			'payment_id' => 'pzi_test_0001',
			'order_id' => $this->test_order->transaction_id,
			'payment_status' => 'finished',
			'price_amount' => $money_service->toDecimal($this->test_order->amount_cents),
			'price_currency' => strtolower($this->test_order->amount_cents->getCurrency()->getCode()),
			'event_at' => time(),
		], $overrides);
	}

	/**
	 * POST a notification to the notify endpoint.
	 *
	 * @param array       $payload   The notification payload
	 * @param string|null $signature Signature override (null = valid signature)
	 *
	 * @return TestResponse
	 */
	protected function postNotification(array $payload, ?string $signature = null): TestResponse
	{
		$body = json_encode($payload);
		$signature ??= hash_hmac('sha512', $body, self::WEBHOOK_SECRET);

		return $this->call(
			'POST',
			'/api/v2/Shop/Checkout/Notify/Payzum/' . $this->test_order->id,
			[],
			[],
			[],
			[
				'HTTP_X_NOWPAYMENTS_SIG' => $signature,
				'HTTP_X_PAYZUM_EVENT_ID' => 'evt_test_0001',
				'CONTENT_TYPE' => 'application/json',
			],
			$body
		);
	}

	public function testNotifyCompletesOrder(): void
	{
		$response = $this->postNotification($this->payload());
		$response->assertStatus(204);

		$this->assertDatabaseHas('orders', [
			'id' => $this->test_order->id,
			'status' => PaymentStatusType::COMPLETED->value,
			'transaction_id' => 'pzi_test_0001',
		]);
	}

	public function testNotifyRejectsForgedSignature(): void
	{
		$response = $this->postNotification($this->payload(), hash_hmac('sha512', 'forged-body', 'wrong-secret'));
		$response->assertStatus(400);

		$this->assertDatabaseHas('orders', [
			'id' => $this->test_order->id,
			'status' => PaymentStatusType::PROCESSING->value,
		]);
	}

	public function testNotifyIgnoresPendingStatus(): void
	{
		$response = $this->postNotification($this->payload(['payment_status' => 'waiting']));
		$response->assertStatus(204);

		$this->assertDatabaseHas('orders', [
			'id' => $this->test_order->id,
			'status' => PaymentStatusType::PROCESSING->value,
		]);
	}

	public function testNotifyMarksExpiredAsFailed(): void
	{
		$response = $this->postNotification($this->payload(['payment_status' => 'expired']));
		$response->assertStatus(204);

		$this->assertDatabaseHas('orders', [
			'id' => $this->test_order->id,
			'status' => PaymentStatusType::FAILED->value,
		]);
	}

	public function testNotifyRejectsAmountMismatch(): void
	{
		$response = $this->postNotification($this->payload(['price_amount' => '0.01']));
		$response->assertStatus(400);

		$this->assertDatabaseHas('orders', [
			'id' => $this->test_order->id,
			'status' => PaymentStatusType::PROCESSING->value,
		]);
	}

	public function testNotifyIsIdempotentOnRedelivery(): void
	{
		$this->postNotification($this->payload())->assertStatus(204);
		$this->postNotification($this->payload())->assertStatus(204);

		$this->assertDatabaseHas('orders', [
			'id' => $this->test_order->id,
			'status' => PaymentStatusType::COMPLETED->value,
			'transaction_id' => 'pzi_test_0001',
		]);
	}

	public function testNotifyRejectsUnknownOrder(): void
	{
		$body = json_encode($this->payload());
		$signature = hash_hmac('sha512', $body, self::WEBHOOK_SECRET);

		$response = $this->call(
			'POST',
			'/api/v2/Shop/Checkout/Notify/Payzum/unknown-order-id',
			[],
			[],
			[],
			[
				'HTTP_X_NOWPAYMENTS_SIG' => $signature,
				'CONTENT_TYPE' => 'application/json',
			],
			$body
		);
		$response->assertStatus(404);
	}

	public function testFinalizeKeepsProcessingOrderPending(): void
	{
		// No session metadata: the return handler has nothing to poll and
		// must leave the order in PROCESSING (the notification completes it),
		// redirecting the buyer to the status page — never to "failed".
		$response = $this->get('/api/v2/Shop/Checkout/Finalize/Payzum/' . $this->test_order->transaction_id);

		$this->assertRedirect($response);
		$response->assertRedirect(route('shop.checkout.complete'));

		$this->assertDatabaseHas('orders', [
			'id' => $this->test_order->id,
			'status' => PaymentStatusType::PROCESSING->value,
		]);
	}
}
