<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Database\Factories;

use App\Enum\PurchasableLicenseType;
use App\Enum\PurchasableSizeVariantType;
use App\Models\PurchasablePrice;
use App\Services\MoneyService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PurchasablePrice>
 */
class PurchasablePriceFactory extends Factory
{
	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var class-string<PurchasablePrice>
	 */
	protected $model = PurchasablePrice::class;

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
			'size_variant' => fake()->randomElement(PurchasableSizeVariantType::cases()),
			'license_type' => fake()->randomElement(PurchasableLicenseType::cases()),
			'price_cents' => $money_service->createFromCents(fake()->numberBetween(999, 9999)), // $9.99 to $99.99
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

	/**
	 * Set specific size variant and license type.
	 *
	 * @param PurchasableSizeVariantType $size_variant
	 * @param PurchasableLicenseType     $license_type
	 *
	 * @return self
	 */
	public function withVariant(PurchasableSizeVariantType $size_variant, PurchasableLicenseType $license_type): self
	{
		return $this->state(fn (array $attributes) => [
			'size_variant' => $size_variant,
			'license_type' => $license_type,
		]);
	}
}
