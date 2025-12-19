<?php

namespace App\Actions\Shop\Gateway;

use App\Models\Order;
use App\Models\OrderItem;
use App\Services\MoneyService;
use Illuminate\Support\Facades\Log;
use Omnipay\Common\AbstractGateway;
use Omnipay\Common\GatewayInterface;
use Omnipay\Common\Message\ResponseInterface;
use PaypalServerSdkLib\Authentication\ClientCredentialsAuthCredentialsBuilder;
use PaypalServerSdkLib\Environment;
use PaypalServerSdkLib\Exceptions\ErrorException;
use PaypalServerSdkLib\Models\Builders\AmountBreakdownBuilder;
use PaypalServerSdkLib\Models\Builders\AmountWithBreakdownBuilder;
use PaypalServerSdkLib\Models\Builders\ItemBuilder;
use PaypalServerSdkLib\Models\Builders\MoneyBuilder;
use PaypalServerSdkLib\Models\Builders\OrderRequestBuilder;
use PaypalServerSdkLib\Models\Builders\PurchaseUnitRequestBuilder;
use PaypalServerSdkLib\Models\CheckoutPaymentIntent;
use PaypalServerSdkLib\Models\Order as PaypalOrder;
use PaypalServerSdkLib\PaypalServerSdkClient;
use PaypalServerSdkLib\PaypalServerSdkClientBuilder;

use function Safe\json_encode;

class PaypalGateway extends AbstractGateway implements GatewayInterface
{
	private PaypalServerSdkClient $client;

	public function getName()
	{
		return 'PayPal Gateway';
	}

	public function getShortName()
	{
		return 'PayPal';
	}

	public function getDefaultParameters()
	{
		return [
			'clientId' => '',
			'secret' => '',
			'testMode' => false,
		];
	}

	public function initialize(array $parameters = [])
	{
		if (!isset($parameters['clientId']) || !isset($parameters['secret'])) {
			return $this;
		}

		$this->client = PaypalServerSdkClientBuilder::init()
			->clientCredentialsAuthCredentials(
				ClientCredentialsAuthCredentialsBuilder::init(
					$parameters['clientId'],
					$parameters['secret']
				)
			)
			->environment(Environment::SANDBOX)
			->build();

		return $this;
	}

	/**
	 * Prepare order details for PayPal order creation.
	 *
	 * @param Order $order The order to be processed
	 *
	 * @return array The prepared order details
	 */
	public function getOrderDetails(Order $order)
	{
		$money_service = resolve(MoneyService::class);
		$amount = $money_service->toDecimal($order->amount_cents);
		$currency = $order->amount_cents->getCurrency()->getCode();

		return [
			'body' => OrderRequestBuilder::init(CheckoutPaymentIntent::CAPTURE, [
				PurchaseUnitRequestBuilder::init(
					AmountWithBreakdownBuilder::init($currency, $amount)
						->breakdown(
							AmountBreakdownBuilder::init()
								->itemTotal(
									MoneyBuilder::init($currency, $amount)->build()
								)
								->build()
						)
						->build()
				)
					// lookup item details in `cart` from database
					->items(
						$order->items->map(fn (OrderItem $item) => ItemBuilder::init(
							$item->title,
							MoneyBuilder::init($currency, $money_service->toDecimal($item->price_cents))->build(),
							'1'
						)
							->description('')
							->sku(($item->purchasable_id ?? 'No-longer-existing') . '-' . $item->size_variant_type->value . '-' . $item->license_type->value)
							->build()
						)->all()
					)
					->build(),
			])
				->build(),
		];
	}

	/**
	 * Create an order to start the transaction.
	 *
	 * @see https://developer.paypal.com/docs/api/orders/v2/#orders_create
	 */
	public function purchase(array $data): ResponseInterface
	{
		try {

			$api_response = $this->client->getOrdersController()->createOrder($data);
			/** @var PaypalOrder $order */
			$order = $api_response->getResult();

			if ($order->getStatus() === 'CREATED') {
				return new OrderCreatedResponse(
					$order->getId()
				);
			}

			Log::error('paypal purchase:', [$order]);
			return new OrderFailedResponse(
				['error' => 'Order creation failed with status: ' . json_encode($order)]
			);
		} catch (\Exception $e) {
			Log::error('paypal purchase:', [$order]);
			return new OrderFailedResponse(
				['error' => $e->getMessage()]
			);
		}
	}

	/**
	 * Complete a purchase.
	 *
	 * @param mixed $options
	 *
	 * @return mixed
	 */
	public function completePurchase($options)
	{
		$capture_body = [
			'id' => $options['transactionReference'],
		];

		try {
			$api_response = $this->client->getOrdersController()->captureOrder($capture_body);

			/** @var PaypalOrder|array{name:string,details:object{issue:string,description:string}[],message:string,debug_id:string,links:string[]} $capture */
			$capture = $api_response->getResult();

			if ($capture instanceof PaypalOrder && $capture->getStatus() === 'COMPLETED') {
				// Capture successful
				return new CapturedResponse($capture->getId());
			}

			if ($capture instanceof ErrorException) {
				Log::error('complete_purchase:', [$capture]);
				/** @var \PaypalServerSdkLib\Models\ErrorDetails[] $details */
				$details = $capture->getDetails();
				if (count($details) > 0) {
					return new CaptureFailedResponse([
						'issue' => $details[0]->getIssue(),
						'description' => $details[0]->getDescription(),
					]);
				}

				return new CaptureFailedResponse([
					'error' => 'Capture failed: ' . $capture->getMessage(),
				]);
			}

			if (is_array($capture) && is_array($capture['details']) && $capture['details'][0]->issue === 'INSTRUMENT_DECLINED') {
					return new CaptureFailedResponse([
						'issue' => $capture['details'][0]->issue,
						'description' => $capture['details'][0]->description,
						'message' => $capture['message'],
						'debug_id' => $capture['debug_id'],
						'error' => 'Capture failed',
						'links' => $capture['links']
					]);
			}

			return new CaptureFailedResponse([
				'error' => 'Capture not completed: ' . json_encode($capture),
			]);
		} catch (\Exception $e) {
			return new OrderFailedResponse(
				['error' => $e->getMessage()]
			);
		}
	}
}
