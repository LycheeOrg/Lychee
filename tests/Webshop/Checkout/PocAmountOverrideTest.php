<?php

namespace Tests\Webshop\Checkout;

use App\Enum\OmnipayProviderType;
use App\Factories\OmnipayFactory;
use App\Services\MoneyService;
use Omnipay\Common\GatewayInterface;

class PocAmountOverrideTest extends BaseCheckoutControllerTest
{
	public function testClientCanOverrideChargedAmount(): void
	{
		// Spy gateway: a real Omnipay Dummy gateway that records the exact
		// parameter array CheckoutService hands to purchase().
		$spy = new class() extends \Omnipay\Dummy\Gateway {
			/** @var array<string,mixed> */
			public static array $captured = [];

			public function purchase(array $parameters = [])
			{
				self::$captured = $parameters;

				return parent::purchase($parameters);
			}
		};
		$spy->initialize((array) config('omnipay.Dummy'));

		// Bind a factory that always returns the spy gateway.
		$this->app->instance(OmnipayFactory::class, new class($spy) extends OmnipayFactory {
			public function __construct(private GatewayInterface $spy)
			{
			}

			public function create_gateway(OmnipayProviderType $provider): GatewayInterface
			{
				return $this->spy;
			}
		});

		$this->test_order->provider = OmnipayProviderType::DUMMY;
		$this->test_order->save();

		// The honest server-side total for this order.
		$this->test_order->updateTotal();
		$real_total = resolve(MoneyService::class)->toDecimal($this->test_order->amount_cents);
		// fwrite(STDERR, "\nServer-computed order total : {$real_total}\n");

		// Attacker request: legitimate card data PLUS an attacker-chosen amount.
		$response = $this->postJson('Shop/Checkout/Process', [
			'additional_data' => [
				'card' => [
					'number' => self::VALID_CARD_NUMBER_SUCCESS,
					'expiryMonth' => '12',
					'expiryYear' => date('Y'),
					'cvv' => '123',
				],
				'amount' => '0.01',
			],
		]);

		$sent_amount = $spy::$captured['amount'] ?? '(none)';
		// fwrite(STDERR, "Amount sent to gateway      : {$sent_amount}\n");
		// fwrite(STDERR, "HTTP status                 : {$response->getStatusCode()}\n");

		$this->assertOk($response);
		$response->assertJson(['is_success' => true]);

		// The vulnerability: the amount charged by the gateway is the
		// attacker-controlled value, NOT the server-computed order total.
		self::assertNotSame('0.01', $spy::$captured['amount'], 'Gateway was charged the attacker-controlled amount');
		self::assertSame($real_total, $spy::$captured['amount'], 'Charged amount differs from the real order total');

		// fwrite(STDERR, "\n*** PAYMENT AMOUNT TAMPERING CONFIRMED: order worth {$real_total} charged as {$sent_amount} ***\n");
	}
}