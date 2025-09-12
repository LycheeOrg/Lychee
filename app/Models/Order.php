<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enum\OmnipayProviderType;
use App\Enum\PaymentStatusType;
use App\Enum\PurchasableSizeVariantType;
use App\Services\MoneyService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Money\Money;

/**
 * Class Order.
 *
 * @property int                       $id
 * @property string                    $transaction_id
 * @property OmnipayProviderType       $provider
 * @property int|null                  $user_id
 * @property string|null               $email
 * @property PaymentStatusType         $status
 * @property Money                     $amount_cents
 * @property Carbon|null               $created_at
 * @property Carbon|null               $updated_at
 * @property Carbon|null               $paid_at
 * @property string|null               $comment
 * @property User|null                 $user
 * @property Collection<int,OrderItem> $items
 *
 * Represents a complete purchase transaction in the shop.
 */
class Order extends Model
{
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

	protected $with = ['items'];

	/**
	 * Get the user who placed this order (if any).
	 */
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}

	/**
	 * Get the items in this order.
	 */
	public function items(): HasMany
	{
		return $this->hasMany(OrderItem::class);
	}

	/**
	 * Calculate the total amount for this order based on the order items.
	 *
	 * @return Money The Money object representing the total amount
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
	 * Update the total amount based on the order items.
	 *
	 * @return $this
	 */
	public function updateTotal(): self
	{
		$this->amount_cents = $this->calculateTotal();
		$this->save();

		return $this;
	}

	/**
	 * Mark the order as paid.
	 *
	 * @param string $transaction_id The transaction ID from the payment provider
	 *
	 * @return $this
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
	 * Find an order by its transaction ID.
	 *
	 * @param string $transaction_id The transaction ID to search for
	 *
	 * @return Order|null The order if found, null otherwise
	 */
	public static function findByTransactionId(string $transaction_id): ?Order
	{
		return self::where('transaction_id', $transaction_id)->first();
	}

	/**
	 * Get all orders for a user.
	 *
	 * @param User $user The user to get orders for
	 *
	 * @return Collection Collection of orders
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
	 * @param string $email The email to search for
	 *
	 * @return Collection Collection of orders
	 */
	public static function getOrdersByEmail(string $email): Collection
	{
		return self::where('email', $email)
			->orderBy('created_at', 'desc')
			->get();
	}

	/**
	 * We can only checkout if the order is still pending AND
	 * processer has been chosem AND
	 * we have either an email or a user associated with the order.
	 * This is necessary so we can send the files later / provide link.
	 *
	 * @return bool
	 */
	public function canCheckout(): bool
	{
		if (!$this->status->canCheckout()) {
			return false;
		}

		if ($this->email !== null) {
			return true;
		}

		// We do not have a mail, so we cannot checkout if the order contains FULL size variants
		if ($this->items()->where('size_variant_type', PurchasableSizeVariantType::FULL)->exists()) {
			return false;
		}

		return $this->user_id !== null;
	}

	/**
	 * We can only process a payment if we can checkout AND if provider is set...
	 *
	 * @return bool
	 */
	public function canProcessPayment(): bool
	{
		return $this->canCheckout() && $this->provider !== null;
	}
}
