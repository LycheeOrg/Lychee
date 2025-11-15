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
	 * @var string
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

	public function withTransactionId(string $transaction_id): static
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
	 * @return static
	 */
	public function withProvider(OmnipayProviderType $provider): static
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
	 * @return static
	 */
	public function withStatus(PaymentStatusType $status): static
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
	 * @return static
	 */
	public function forUser(User|int|null $user = null): static
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
	 * @return static
	 */
	public function withEmail(string|null $email = null): static
	{
		return $this->state(fn (array $attributes) => [
			'email' => $email ?? fake()->email(),
		]);
	}

	/**
	 * Create a pending order (default state).
	 *
	 * @return static
	 */
	public function pending(): static
	{
		return $this->withStatus(PaymentStatusType::PENDING);
	}

	/**
	 * Create a processing order.
	 *
	 * @return static
	 */
	public function processing(): static
	{
		return $this->withStatus(PaymentStatusType::PROCESSING)
			->withProvider(OmnipayProviderType::DUMMY);
	}

	/**
	 * Create a offline order.
	 *
	 * @return static
	 */
	public function offline(): static
	{
		return $this->withStatus(PaymentStatusType::OFFLINE);
	}

	/**
	 * Create a completed order.
	 *
	 * @return static
	 */
	public function completed(): static
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
	 * @return static
	 */
	public function closed(): static
	{
		return $this->withStatus(PaymentStatusType::CLOSED)
			->state(fn (array $attributes) => [
				'paid_at' => fake()->dateTimeBetween('-1 month', 'now'),
			]);
	}


	/**
	 * Create a failed order.
	 *
	 * @return static
	 */
	public function failed(): static
	{
		return $this->withStatus(PaymentStatusType::FAILED)
			->withProvider(OmnipayProviderType::DUMMY);
	}

	/**
	 * Create a cancelled order.
	 *
	 * @return static
	 */
	public function cancelled(): static
	{
		return $this->withStatus(PaymentStatusType::CANCELLED)
			->withProvider(OmnipayProviderType::DUMMY);
	}

	/**
	 * Create an order that can checkout (has email or user).
	 *
	 * @return static
	 */
	public function canCheckout(): static
	{
		return $this->has(OrderItem::factory()->forPhoto()->fullSize()->count(1))->pending()->withEmail();
	}

	/**
	 * Create an order that can process payment (can checkout + has provider).
	 *
	 * @return static
	 */
	public function canProcessPayment(): static
	{
		return $this->canCheckout()->withProvider(OmnipayProviderType::DUMMY);
	}

	/**
	 * Create an order with a specific amount in cents.
	 *
	 * @param int $cents
	 *
	 * @return static
	 */
	public function withAmountCents(int $cents): static
	{
		return $this->state(fn (array $attributes) => [
			'amount_cents' => resolve(MoneyService::class)->createFromCents($cents),
		]);
	}
}