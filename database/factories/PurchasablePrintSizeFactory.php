<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Database\Factories;

use App\Models\PurchasablePrintSize;
use App\Services\MoneyService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PurchasablePrintSize>
 */
class PurchasablePrintSizeFactory extends Factory
{
	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var class-string<PurchasablePrintSize>
	 */
	protected $model = PurchasablePrintSize::class;

	/**
	 * Define the model's default state.
	 *
	 * @return array<string,mixed>
	 */
	public function definition(): array
	{
		$money_service = resolve(MoneyService::class);

		return [
			'purchasable_id' => null,
			'print_size_id' => null,
			'price_cents' => $money_service->createFromCents(fake()->numberBetween(999, 9999)),
		];
	}

	/**
	 * Set a specific price in cents.
	 *
	 * @param int $cents
	 *
	 * @return self
	 */
	public function withPrice(int $cents): self
	{
		return $this->state(function (array $attributes) use ($cents) {
			$money_service = resolve(MoneyService::class);

			return [
				'price_cents' => $money_service->createFromCents($cents),
			];
		});
	}
}
