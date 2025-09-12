<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
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
	 * @var string
	 */
	protected $model = PurchasablePrice::class;

	/**
	 * Define the model's default state.
	 *
	 * @return array<string,mixed>
	 */
	public function definition(): array
	{
		$moneyService = resolve(MoneyService::class);

		return [
			'purchasable_id' => null,
			'size_variant' => fake()->randomElement(PurchasableSizeVariantType::cases()),
			'license_type' => fake()->randomElement(PurchasableLicenseType::cases()),
			'price_cents' => $moneyService->createFromCents(fake()->numberBetween(999, 9999)), // $9.99 to $99.99
		];
	}

	/**
	 * Set a specific price in cents.
	 *
	 * @param int $cents
	 *
	 * @return static
	 */
	public function withPrice(int $cents): static
	{
		return $this->state(function (array $attributes) use ($cents) {
			$moneyService = resolve(MoneyService::class);

			return [
				'price_cents' => $moneyService->createFromCents($cents),
			];
		});
	}

	/**
	 * Set specific size variant and license type.
	 *
	 * @param PurchasableSizeVariantType $sizeVariant
	 * @param PurchasableLicenseType     $licenseType
	 *
	 * @return static
	 */
	public function withVariant(PurchasableSizeVariantType $sizeVariant, PurchasableLicenseType $licenseType): static
	{
		return $this->state(fn (array $attributes) => [
			'size_variant' => $sizeVariant,
			'license_type' => $licenseType,
		]);
	}
}
