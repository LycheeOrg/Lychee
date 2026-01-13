<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enum\OmnipayProviderType;
use App\Enum\PaymentStatusType;
use App\Enum\PurchasableSizeVariantType;
use App\Services\MoneyService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Money\Money;

/**
 * Class Order.
 *
 * Represents a complete purchase transaction in the Lychee webshop system.
 * Orders manage the entire lifecycle of a customer purchase, from initial
 * basket creation through payment processing to final fulfillment.
 *
 * Order Lifecycle:
 * 1. PENDING - Initial state when items are added to basket
 * 2. PROCESSING - Payment is being processed by provider
 * 3. COMPLETED - Payment successful, order fulfilled
 * 4. OFFLINE - Manual/offline payment processing
 * 5. CLOSED - Order delivered and finalized
 * 6. CANCELLED/FAILED - Order cancelled or payment failed
 *
 * The order system supports both registered users and guest purchases.
 * For guest purchases, an email address is required for delivery of FULL
 * size variants that require manual processing by the photographer.
 *
 * Payment processing is handled through Omnipay providers (Stripe, Mollie,
 * PayPal, etc.) with support for offline payments when enabled.
 *
 * @property int                       $id             Primary key
 * @property string                    $transaction_id Unique identifier for payment provider tracking
 * @property OmnipayProviderType       $provider       Payment provider used (Stripe, Mollie, PayPal, etc.)
 * @property int|null                  $user_id        Foreign key to users table (null for guest purchases)
 * @property string|null               $email          Customer email (required for guest purchases and FULL variants)
 * @property PaymentStatusType         $status         Current order status (pending, processing, completed, etc.)
 * @property Money                     $amount_cents   Total order amount using Money library for precision
 * @property Carbon|null               $created_at     Order creation timestamp
 * @property Carbon|null               $updated_at     Last modification timestamp
 * @property Carbon|null               $paid_at        Payment completion timestamp (null if unpaid)
 * @property string|null               $comment        Optional order notes or comments
 * @property User|null                 $user           Associated user account (null for guest purchases)
 * @property Collection<int,OrderItem> $items          Collection of items purchased in this order
 *
 * @see OrderItem Individual items within the order
 * @see PaymentStatusType Order status enumeration
 * @see OmnipayProviderType Payment provider enumeration
 * @see MoneyService Service for handling monetary calculations
 */
class Order extends Model
{
	/** @phpstan-use HasFactory<\Database\Factories\OrderFactory> */
	use HasFactory;

	/**
	 * {@inheritdoc}
	 */
	protected $fillable = [
		'transaction_id',
		'provider',
		'user_id',
		'email',
		'status',
		'amount_cents',
		'paid_at',
		'comment',
	];

	/**
	 * {@inheritdoc}
	 */
	protected $casts = [
		'amount_cents' => MoneyCast::class,
		'paid_at' => 'datetime',
		'updated_at' => 'datetime',
		'created_at' => 'datetime',
		'status' => PaymentStatusType::class,
		'provider' => OmnipayProviderType::class,
	];

	protected $with = ['items', 'user'];

	/**
	 * Get the user who placed this order.
	 *
	 * This relationship is nullable to support guest purchases where
	 * customers can complete orders without creating an account.
	 * Guest purchases rely on email addresses for delivery and communication.
	 *
	 * @return BelongsTo<User, $this>
	 */
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}

	/**
	 * Get all items included in this order.
	 *
	 * Order items represent individual photos, albums, or size variants
	 * being purchased. Each item captures the price, license type, and
	 * size variant selected at the time of purchase for historical accuracy.
	 *
	 * @return HasMany<OrderItem, $this>
	 */
	public function items(): HasMany
	{
		return $this->hasMany(OrderItem::class);
	}

	/**
	 * Calculate the total amount for this order based on all order items.
	 *
	 * This method recalculates the order total by summing the individual
	 * price_cents of all order items. It uses the Money library to ensure
	 * precise decimal arithmetic without floating-point errors.
	 *
	 * The calculation is performed fresh each time to ensure accuracy,
	 * particularly important when items are added or removed from the order.
	 *
	 * @return Money The total order amount as a Money object
	 */
	public function calculateTotal(): Money
	{
		$money_service = resolve(MoneyService::class);
		$total = $money_service->createFromCents(0);
		// Because we are working with Money object we cannot simply sum up the cents
		foreach ($this->items as $item) {
			$total = $total->add($item->price_cents);
		}

		return $total;
	}

	/**
	 * Update and persist the order total based on current items.
	 *
	 * This method recalculates the order total using calculateTotal()
	 * and immediately saves it to the database. Should be called whenever
	 * order items are added, removed, or their prices change.
	 *
	 * @return $this Fluent interface for method chaining
	 */
	public function updateTotal(): self
	{
		$this->amount_cents = $this->calculateTotal();
		$this->save();

		return $this;
	}

	/**
	 * Mark the order as successfully paid and completed.
	 *
	 * This method is called when payment has been confirmed by the payment
	 * provider. It updates the order status to COMPLETED, records the payment
	 * timestamp, and stores the provider's transaction ID for reference.
	 *
	 * After calling this method, the order is considered fulfilled and
	 * customers should have access to their purchased content.
	 *
	 * @param string $transaction_id Unique transaction identifier from payment provider
	 *
	 * @return $this Fluent interface for method chaining
	 */
	public function markAsPaid(string $transaction_id): self
	{
		$this->transaction_id = $transaction_id;
		$this->status = PaymentStatusType::COMPLETED;
		$this->paid_at = now();
		$this->save();

		return $this;
	}

	/**
	 * Find an order by its payment provider transaction ID.
	 *
	 * This method is commonly used during payment callback processing
	 * to locate the order associated with a payment provider's response.
	 * The transaction_id is set when payment processing begins.
	 *
	 * @param string $transaction_id Unique transaction identifier from payment provider
	 *
	 * @return Order|null The matching order or null if not found
	 */
	public static function findByTransactionId(string $transaction_id): ?Order
	{
		return self::where('transaction_id', $transaction_id)->first();
	}

	/**
	 * Get all orders placed by a specific user.
	 *
	 * Returns orders associated with the user's account, sorted by creation
	 * date with most recent first. Does not include guest orders made with
	 * the same email address but no user account.
	 *
	 * @param User $user The user whose orders to retrieve
	 *
	 * @return Collection<int, Order> Collection of orders for the user
	 */
	public static function getOrdersForUser(User $user): Collection
	{
		return self::where('user_id', $user->id)
			->orderBy('created_at', 'desc')
			->get();
	}

	/**
	 * Get all orders associated with an email address.
	 *
	 * This method retrieves both guest orders and registered user orders
	 * that used the specified email address. Useful for customer service
	 * and order lookup functionality.
	 *
	 * @param string $email Email address to search for
	 *
	 * @return Collection<int, Order> Collection of orders for the email
	 */
	public static function getOrdersByEmail(string $email): Collection
	{
		return self::where('email', $email)
			->orderBy('created_at', 'desc')
			->get();
	}

	/**
	 * Determine if this order is eligible for checkout.
	 *
	 * An order can proceed to checkout if:
	 * 1. The order status allows checkout (PENDING, FAILED, CANCELLED)
	 * 2. The order contains at least one item
	 *
	 * This is the first validation step before payment processing.
	 *
	 * @return bool True if the order can proceed to checkout
	 */
	public function canCheckout(): bool
	{
		return $this->status->canCheckout() && $this->items->count() > 0;
	}

	/**
	 * Determine if items can be added to this order.
	 *
	 * Items can only be added to orders that haven't been paid or finalized.
	 * Once an order enters processing or is completed, the item list is locked
	 * to maintain transaction integrity.
	 *
	 * @return bool True if items can be added to the order
	 */
	public function canAddItems(): bool
	{
		return $this->status->canAddItems();
	}

	/**
	 * Determine if this order can proceed with payment processing.
	 *
	 * Payment processing requires several conditions to be met:
	 * 1. Order must be eligible for checkout (canCheckout() returns true)
	 * 2. A payment provider must be selected
	 * 3. Contact information requirements must be satisfied:
	 *    - Email address is provided, OR
	 *    - User is logged in AND order contains no FULL size variants
	 *
	 * The email requirement for FULL variants exists because these require
	 * manual processing and delivery by the photographer, necessitating
	 * direct customer communication.
	 *
	 * @return bool True if payment can be processed for this order
	 */
	public function canProcessPayment(): bool
	{
		// Not in a state that allows checkout
		if ($this->canCheckout() === false) {
			return false;
		}

		// No provider, how are we supposed to know what to do?
		if ($this->provider === null) {
			return false;
		}

		// Email is set, we are fine.
		if ($this->email !== null && $this->email !== '') {
			return true;
		}

		// We do not have a mail, so we cannot checkout if the order contains FULL size variants
		if ($this->items()->where('size_variant_type', PurchasableSizeVariantType::FULL)->exists()) {
			return false;
		}

		return $this->user_id !== null;
	}
}
