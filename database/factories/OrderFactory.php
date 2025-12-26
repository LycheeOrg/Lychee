<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace Database\Factories;

use App\Enum\OmnipayProviderType;
use App\Enum\PaymentStatusType;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Services\MoneyService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 *
 * Example of usage:
 * Create a complete order ready for checkout
 *   $order = Order::factory()->canCheckout()->create();
 *
 * Create an order with items
 *   $order = Order::factory()->completed()
 *      ->has(OrderItem::factory()->forPhoto()->fullSize()->count(3))
 *      ->create();
 *
 * Create a specific order item
 *   $item = OrderItem::factory()
 *      ->forOrder($order)
 *      ->commercialLicense()
 *      ->originalSize()
 *      ->withPriceDollars(29.99)
 *      ->create();
 */
class OrderFactory extends Factory
{
	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var class-string<Order>
	 */
	protected $model = Order::class;

	/**
	 * Define the model's default state.
	 *
	 * @return array<string,mixed>
	 */
	public function definition(): array
	{
		return [
			'transaction_id' => Str::uuid()->toString(),
			'provider' => null,
			'user_id' => null,
			'email' => null,
			'status' => PaymentStatusType::PENDING,
			'amount_cents' => resolve(MoneyService::class)->createFromCents(0),
			'paid_at' => null,
			'comment' => fake()->optional()->sentence(),
		];
	}

	public function withTransactionId(string $transaction_id): self
	{
		return $this->state(fn (array $attributes) => [
			'transaction_id' => $transaction_id,
		]);
	}

	/**
	 * Create an order with a specific provider.
	 *
	 * @param OmnipayProviderType $provider
	 *
	 * @return self
	 */
	public function withProvider(OmnipayProviderType $provider): self
	{
		return $this->state(fn (array $attributes) => [
			'provider' => $provider,
		]);
	}

	/**
	 * Create an order with a specific status.
	 *
	 * @param PaymentStatusType $status
	 *
	 * @return self
	 */
	public function withStatus(PaymentStatusType $status): self
	{
		return $this->state(fn (array $attributes) => [
			'status' => $status,
		]);
	}

	/**
	 * Create an order for a specific user.
	 *
	 * @param User|int|null $user
	 *
	 * @return self
	 */
	public function forUser(User|int|null $user = null): self
	{
		$user_id = $user instanceof User ? $user->id : $user;
		$user_id = $user_id ?? User::factory()->create()->id;

		return $this->state(fn (array $attributes) => [
			'user_id' => $user_id,
		]);
	}

	/**
	 * Create an order with a specific email.
	 *
	 * @param string|null $email
	 *
	 * @return self
	 */
	public function withEmail(string|null $email = null): self
	{
		return $this->state(fn (array $attributes) => [
			'email' => $email ?? fake()->email(),
		]);
	}

	/**
	 * Create a pending order (default state).
	 *
	 * @return self
	 */
	public function pending(): self
	{
		return $this->withStatus(PaymentStatusType::PENDING);
	}

	/**
	 * Create a processing order.
	 *
	 * @return self
	 */
	public function processing(): self
	{
		return $this->withStatus(PaymentStatusType::PROCESSING)
			->withProvider(OmnipayProviderType::DUMMY);
	}

	/**
	 * Create an offline order.
	 *
	 * @return self
	 */
	public function offline(): self
	{
		return $this->withStatus(PaymentStatusType::OFFLINE);
	}

	/**
	 * Create a completed order.
	 *
	 * @return self
	 */
	public function completed(): self
	{
		return $this->withStatus(PaymentStatusType::COMPLETED)
			->withProvider(OmnipayProviderType::DUMMY)
			->state(fn (array $attributes) => [
				'paid_at' => fake()->dateTimeBetween('-1 month', 'now'),
			]);
	}

	/**
	 * Create a closed order.
	 *
	 * @return self
	 */
	public function closed(): self
	{
		return $this->withStatus(PaymentStatusType::CLOSED)
			->state(fn (array $attributes) => [
				'paid_at' => fake()->dateTimeBetween('-1 month', 'now'),
			]);
	}

	/**
	 * Create a failed order.
	 *
	 * @return self
	 */
	public function failed(): self
	{
		return $this->withStatus(PaymentStatusType::FAILED)
			->withProvider(OmnipayProviderType::DUMMY);
	}

	/**
	 * Create a cancelled order.
	 *
	 * @return self
	 */
	public function cancelled(): self
	{
		return $this->withStatus(PaymentStatusType::CANCELLED)
			->withProvider(OmnipayProviderType::DUMMY);
	}

	/**
	 * Create an order that can checkout (has email or user).
	 *
	 * @return self
	 */
	public function canCheckout(): self
	{
		return $this->has(OrderItem::factory()->forPhoto()->fullSize()->count(1))->pending()->withEmail();
	}

	/**
	 * Create an order that can process payment (can checkout + has provider).
	 *
	 * @return self
	 */
	public function canProcessPayment(): self
	{
		return $this->canCheckout()->withProvider(OmnipayProviderType::DUMMY);
	}

	/**
	 * Create an order with a specific amount in cents.
	 *
	 * @param int $cents
	 *
	 * @return self
	 */
	public function withAmountCents(int $cents): self
	{
		return $this->state(fn (array $attributes) => [
			'amount_cents' => resolve(MoneyService::class)->createFromCents($cents),
		]);
	}
}