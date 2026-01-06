# Payment Gateway Integration

This directory contains custom payment gateway implementations and response objects that extend the Omnipay payment processing library for the Lychee webshop.

## Purpose

The Gateway pattern provides a unified interface for integrating with different payment providers (PayPal, Stripe, Mollie, etc.). While Lychee primarily uses the Omnipay library for payment processing, some payment providers require custom implementations that don't fit perfectly into Omnipay's standard interface.

## Why Custom Gateways?

**Standard Omnipay Integration**: Most payment providers (Stripe, Mollie) work perfectly with Omnipay's built-in drivers and require no custom code.

**Custom Gateway Requirements**: Some payment providers (like PayPal) have specific SDK requirements or API patterns that necessitate:
- Direct SDK integration (PayPal Server SDK)
- Custom request/response handling
- Provider-specific error processing
- Non-standard authentication flows

## Components

### PaypalGateway.php

Custom implementation of Omnipay's `AbstractGateway` for PayPal integration using the official PayPal Server SDK.

**Key Features**:
- **Direct SDK Integration**: Uses `PaypalServerSdkClient` instead of generic HTTP requests
- **Order Creation**: Converts Lychee orders into PayPal order format with line items
- **Payment Capture**: Handles the two-step PayPal flow (create order → capture payment)
- **Error Handling**: Translates PayPal-specific errors into consistent response objects

**Methods**:
- `getOrderDetails(Order $order)`: Transforms Lychee order data into PayPal order structure
- `purchase(array $data)`: Creates a PayPal order and returns transaction reference
- `completePurchase($options)`: Captures the payment after customer approval

### Response Objects

These classes implement Omnipay's `ResponseInterface` to provide consistent response handling across different payment providers:

#### OrderCreatedResponse.php

Represents a successful order creation with the payment provider.

**Properties**:
- `transaction_reference`: Unique identifier from payment provider for tracking

**Usage**: Returned when an order is successfully created but payment is not yet captured (two-step payment flow).

#### CapturedResponse.php

Extends `OrderCreatedResponse` to represent a successful payment capture.

**Usage**: Returned when payment has been successfully captured and funds are secured.

#### OrderFailedResponse.php

Represents a failed order creation or processing error.

**Features**:
- Extracts error details from provider-specific error formats
- Provides user-friendly error messages
- Includes debug information for troubleshooting

**Usage**: Returned when order creation fails due to validation errors, API issues, or provider rejections.

#### CaptureFailedResponse.php

Extends `OrderFailedResponse` to represent a failed payment capture.

**Usage**: Returned when payment capture fails (insufficient funds, card declined, etc.).

## Integration with Omnipay

The custom gateway extends Omnipay's `AbstractGateway` to maintain compatibility with the existing payment infrastructure:

```php
class PaypalGateway extends AbstractGateway implements GatewayInterface
{
    // Custom implementation using PayPal SDK
}
```

This allows the CheckoutService to use PayPal the same way it uses other providers:

```php
$gateway = Omnipay::create('PayPal');
$gateway->initialize($config);
$response = $gateway->purchase($orderDetails);
```

## Response Flow

### Successful Payment Flow

1. **Order Creation**: `PaypalGateway::purchase()` → `OrderCreatedResponse`
   - Transaction reference stored in database
   - Customer redirected to PayPal for approval

2. **Payment Capture**: `PaypalGateway::completePurchase()` → `CapturedResponse`
   - Payment captured after customer approval
   - Order marked as completed

### Failed Payment Flow

1. **Order Creation Failure**: `PaypalGateway::purchase()` → `OrderFailedResponse`
   - API errors, validation failures, configuration issues

2. **Capture Failure**: `PaypalGateway::completePurchase()` → `CaptureFailedResponse`
   - Payment declined, insufficient funds, cancelled by user

## Adding New Payment Providers

To add a new payment provider that requires custom integration:

1. **Evaluate Omnipay Support**: Check if an Omnipay driver exists first
2. **Create Custom Gateway** (if needed): Extend `AbstractGateway`
3. **Implement Required Methods**:
   - `initialize()`: Setup with provider credentials
   - `purchase()`: Create order/payment intent
   - `completePurchase()`: Capture/finalize payment
4. **Create Response Objects**: Implement `ResponseInterface` for provider-specific responses
5. **Register in CheckoutService**: Add provider configuration and initialization logic

## Error Handling Best Practices

Response objects should:
- Extract meaningful error messages from provider responses
- Include debug information (transaction IDs, error codes) for troubleshooting
- Return consistent boolean values for `isSuccessful()`, `isCancelled()`, etc.
- Handle edge cases gracefully (missing fields, unexpected formats)

## Testing Considerations

- **Sandbox Mode**: Use provider sandbox/test environments for development
- **Mock Responses**: Create mock response objects for unit testing
- **Error Scenarios**: Test all error response types (declined, timeout, invalid data)
- **Transaction IDs**: Verify transaction references are properly stored and retrieved

## See Also

- [CheckoutService](../CheckoutService.php) - Orchestrates payment processing
- [OrderService](../OrderService.php) - Manages order lifecycle
- [Omnipay Documentation](https://omnipay.thephpleague.com/) - Payment processing library

---

*Last updated: December 19, 2024*
