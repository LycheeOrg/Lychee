<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Shop\Gateway;

use App\Exceptions\Internal\LycheeLogicException;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\MoneyService;
use Illuminate\Support\Facades\Log;
use Omnipay\Common\AbstractGateway;
use Omnipay\Common\GatewayInterface;
use Omnipay\Common\Message\ResponseInterface;
use PaypalServerSdkLib\Authentication\ClientCredentialsAuthCredentialsBuilder;
use PaypalServerSdkLib\Environment;
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

/**
 * PaypalGateway - Custom Omnipay gateway for PayPal integration.
 *
 * This gateway provides PayPal payment processing for the Lychee webshop using
 * the official PayPal Server SDK. It implements Omnipay's GatewayInterface to
 * maintain compatibility with the existing payment infrastructure while leveraging
 * PayPal's native SDK for optimal integration.
 *
 * PayPal Payment Flow:
 * 1. Order Creation: Create a PayPal order with line items (purchase method)
 * 2. Customer Approval: Redirect customer to PayPal for payment authorization
 * 3. Payment Capture: Capture the authorized payment (completePurchase method)
 *
 * Why Custom Gateway?
 * - PayPal requires specific SDK implementation for proper order management
 * - Direct SDK integration provides better error handling and type safety
 * - Native support for line item details and itemized invoices
 * - More reliable than generic HTTP-based Omnipay drivers
 *
 * Configuration Requirements:
 * - clientId: PayPal REST API client ID from developer dashboard
 * - secret: PayPal REST API secret key for authentication
 * - testMode: Boolean to toggle between sandbox and production environments
 *
 * Error Handling:
 * This gateway returns custom response objects (OrderCreatedResponse,
 * CapturedResponse, OrderFailedResponse, CaptureFailedResponse) that
 * implement Omnipay's ResponseInterface for consistent error handling
 * across different payment providers.
 *
 * @see https://developer.paypal.com/docs/api/orders/v2/ PayPal Orders API Documentation
 * @see OrderCreatedResponse Successful order creation response
 * @see CapturedResponse Successful payment capture response
 * @see OrderFailedResponse Failed order/capture response
 * @see \Omnipay\Common\AbstractGateway Base Omnipay gateway
 */
class PaypalGateway extends AbstractGateway implements GatewayInterface
{
	/**
	 * PayPal Server SDK client instance.
	 *
	 * This client handles all communication with PayPal's REST API including
	 * authentication, request formatting, and response parsing. It is initialized
	 * in the initialize() method with credentials from configuration.
	 */
	private ?PaypalServerSdkClient $client = null;

	/**
	 * Setter for unit tests.
	 *
	 * @param PaypalServerSdkClient $client
	 *
	 * @return void
	 */
	public function setClient(PaypalServerSdkClient $client): void
	{
		$this->client = $client;
	}

	/**
	 * Get the human-readable name of the payment gateway.
	 *
	 * This name is used in admin interfaces, logs, and user-facing displays
	 * to identify the payment provider.
	 *
	 * @return string The gateway display name
	 */
	public function getName()
	{
		return 'PayPal Gateway';
	}

	/**
	 * Get the short identifier name for the payment gateway.
	 *
	 * This short name is used as a unique identifier for the gateway in
	 * configuration arrays, database records, and API calls. It should be
	 * concise and URL-safe.
	 *
	 * @return string The gateway short identifier
	 */
	public function getShortName()
	{
		return 'PayPal';
	}

	/**
	 * Get the default configuration parameters for the gateway.
	 *
	 * These parameters define the credentials and settings required to
	 * initialize the PayPal gateway. The actual values should be provided
	 * via environment variables or configuration files.
	 *
	 * Configuration Parameters:
	 * - clientId: PayPal REST API client ID (from PayPal Developer Dashboard)
	 * - secret: PayPal REST API secret key for authentication
	 * - testMode: Boolean indicating whether to use sandbox (true) or production (false)
	 *
	 * @return array<string,mixed> Default parameter values (empty strings/false)
	 */
	public function getDefaultParameters()
	{
		return [
			'clientId' => '',
			'secret' => '',
			'testMode' => false,
		];
	}

	/**
	 * Initialize the gateway with credentials and configuration.
	 *
	 * This method sets up the PayPal Server SDK client with OAuth 2.0
	 * client credentials authentication. The client is configured to use
	 * PayPal's sandbox environment for testing. In production, this should
	 * be changed to Environment::PRODUCTION.
	 *
	 * Required Parameters:
	 * - clientId: PayPal REST API client ID (string)
	 * - secret: PayPal REST API secret key (string)
	 *
	 * If required credentials are missing, the method returns early without
	 * initializing the client. This allows the gateway to fail gracefully
	 * during configuration validation.
	 *
	 * @param array<string,mixed> $parameters Configuration parameters including clientId and secret
	 *
	 * @return $this Fluent interface for method chaining
	 */
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
			->environment(
				config('omnipay.testMode', false) === true ? Environment::SANDBOX : Environment::PRODUCTION)
			->build();

		return $this;
	}

	/**
	 * Prepare order details for PayPal order creation.
	 *
	 * This method transforms a Lychee Order into PayPal's order request format,
	 * including detailed line items for each photo purchase. PayPal's API requires
	 * a specific structure with amount breakdowns, line items, and SKU information.
	 *
	 * The order structure includes:
	 * - Intent: CAPTURE (immediate payment capture after authorization)
	 * - Purchase Unit: Contains amount, breakdown, and line items
	 * - Amount Breakdown: Itemized total matching sum of all line items
	 * - Line Items: Each OrderItem becomes a PayPal line item with:
	 *   - Title: Photo or album title
	 *   - Unit Price: Price in decimal format (e.g., "10.99")
	 *   - Quantity: Always "1" for digital goods
	 *   - SKU: Unique identifier combining purchasable_id, size variant, and license
	 *
	 * SKU Format: "{purchasable_id}-{size_variant}-{license_type}"
	 * Example: "123-MEDIUM-PERSONAL" or "No-longer-existing-FULL-COMMERCIAL"
	 *
	 * Currency Handling:
	 * The method uses MoneyService to convert Money objects to decimal strings
	 * that PayPal accepts. Currency code is extracted from the order's amount.
	 *
	 * @param Order $order The Lychee order to convert to PayPal format
	 *
	 * @return array{body: \PaypalServerSdkLib\Models\OrderRequest} PayPal order request structure
	 */
	public function getOrderDetails(Order $order)
	{
		if ($this->client === null) {
			throw new LycheeLogicException('PayPal client not initialized');
		}

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
	 * Create a PayPal order to initiate the payment process.
	 *
	 * This method implements the first step of PayPal's two-step payment flow:
	 * 1. Create Order: Establish order with PayPal and get order ID
	 * 2. Customer Approval: Customer is redirected to PayPal to authorize payment
	 * 3. Capture Payment: Handled separately by completePurchase() method
	 *
	 * The method sends the order details to PayPal's Orders API and expects
	 * a CREATED status in response. If successful, it returns an OrderCreatedResponse
	 * containing the PayPal order ID (transaction reference) which is used for
	 * subsequent capture operations.
	 *
	 * Success Response:
	 * - Returns OrderCreatedResponse with PayPal order ID
	 * - Order ID stored as transaction_reference in database
	 * - Customer redirected to PayPal approval URL
	 *
	 * Failure Response:
	 * - Returns OrderFailedResponse with error details
	 * - Logs error for debugging
	 * - User sees error message and can retry
	 *
	 * Common Failure Scenarios:
	 * - Invalid credentials (clientId/secret)
	 * - Network connectivity issues
	 * - Invalid order data (currency, amounts)
	 * - API rate limiting
	 * - PayPal service outage
	 *
	 * @param array{body: \PaypalServerSdkLib\Models\OrderRequest} $data Order details from getOrderDetails()
	 *
	 * @return ResponseInterface OrderCreatedResponse on success, OrderFailedResponse on failure
	 *
	 * @see https://developer.paypal.com/docs/api/orders/v2/#orders_create PayPal Orders API Documentation
	 * @see OrderCreatedResponse Successful order creation response
	 * @see OrderFailedResponse Failed order creation response
	 */
	public function purchase(array $data): ResponseInterface
	{
		if ($this->client === null) {
			throw new LycheeLogicException('PayPal client not initialized');
		}

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
			Log::error('paypal purchase:', [$e->getMessage()]);

			return new OrderFailedResponse(
				['error' => $e->getMessage()]
			);
		}
	}

	/**
	 * Complete a purchase by capturing the authorized payment.
	 *
	 * This method implements the final step of PayPal's two-step payment flow.
	 * After the customer has approved the payment on PayPal's website and been
	 * redirected back to Lychee, this method captures the funds.
	 *
	 * Payment Capture Flow:
	 * 1. Customer approves payment on PayPal (redirected from purchase())
	 * 2. PayPal redirects customer back to Lychee with order ID
	 * 3. This method captures the payment using the order ID
	 * 4. Funds are transferred from customer to merchant
	 *
	 * The method attempts to capture the payment and handles three possible outcomes:
	 *
	 * Success (Status: COMPLETED):
	 * - Returns CapturedResponse with PayPal order ID
	 * - Payment is complete and funds are being transferred
	 * - Order status updated to COMPLETED in database
	 *
	 * Failure (ErrorException):
	 * - Returns CaptureFailedResponse with error details
	 * - Includes issue code and description from PayPal
	 * - Common issues: INSTRUMENT_DECLINED (card declined), insufficient funds
	 *
	 * Failure (Array with details):
	 * - Handles INSTRUMENT_DECLINED specifically
	 * - Provides detailed error information including debug_id
	 * - Allows merchant to troubleshoot payment issues
	 *
	 * Error Handling:
	 * The method includes comprehensive error handling for:
	 * - Payment instrument declined (card/bank account)
	 * - Insufficient funds
	 * - Payment authorization expired
	 * - Network/API errors
	 * - Unexpected response formats
	 *
	 * All errors are logged for debugging and returned as appropriate response
	 * objects that implement Omnipay's ResponseInterface.
	 *
	 * @param array{transactionReference: string} $options Must include transactionReference (PayPal order ID)
	 *
	 * @return ResponseInterface CapturedResponse on success, CaptureFailedResponse or OrderFailedResponse on failure
	 *
	 * @see CapturedResponse Successful payment capture response
	 * @see CaptureFailedResponse Failed payment capture response
	 * @see https://developer.paypal.com/docs/api/orders/v2/#orders_capture PayPal Capture API Documentation
	 */
	public function completePurchase($options)
	{
		if ($this->client === null) {
			throw new LycheeLogicException('PayPal client not initialized');
		}

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

			Log::error('complete_purchase:', [$capture]);
			if (is_array($capture) && is_array($capture['details']) && $capture['details'][0]->issue === 'INSTRUMENT_DECLINED') {
				return new CaptureFailedResponse([
					'issue' => $capture['details'][0]->issue,
					'description' => $capture['details'][0]->description,
					'message' => $capture['message'],
					'debug_id' => $capture['debug_id'],
					'error' => 'Capture failed',
					'links' => $capture['links'],
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
