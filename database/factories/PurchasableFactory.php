<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace Database\Factories;

use App\Enum\PurchasableLicenseType;
use App\Enum\PurchasableSizeVariantType;
use App\Models\Purchasable;
use App\Models\PurchasablePrice;
use App\Services\MoneyService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Purchasable>
 */
class PurchasableFactory extends Factory
{
	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var class-string<Purchasable>
	 */
	protected $model = Purchasable::class;

	/**
	 * Define the model's default state.
	 *
	 * @return array<string,mixed>
	 */
	public function definition(): array
	{
		return [
			'album_id' => null,
			'photo_id' => null,
			'description' => fake()->sentence(),
			'owner_notes' => fake()->text(200),
			'is_active' => true,
		];
	}

	/**
	 * Create a purchasable with predefined prices.
	 *
	 * @return self
	 */
	public function withPrices(): self
	{
		return $this->afterCreating(function (Purchasable $purchasable): void {
			$money_service = resolve(MoneyService::class);

			// Create some default pricing options
			PurchasablePrice::create([
				'purchasable_id' => $purchasable->id,
				'size_variant' => PurchasableSizeVariantType::MEDIUM,
				'license_type' => PurchasableLicenseType::PERSONAL,
				'price_cents' => $money_service->createFromCents(1999), // $19.99
			]);

			PurchasablePrice::create([
				'purchasable_id' => $purchasable->id,
				'size_variant' => PurchasableSizeVariantType::ORIGINAL,
				'license_type' => PurchasableLicenseType::PERSONAL,
				'price_cents' => $money_service->createFromCents(2999), // $29.99
			]);

			PurchasablePrice::create([
				'purchasable_id' => $purchasable->id,
				'size_variant' => PurchasableSizeVariantType::ORIGINAL,
				'license_type' => PurchasableLicenseType::COMMERCIAL,
				'price_cents' => $money_service->createFromCents(4999), // $49.99
			]);
		});
	}

	/**
	 * Create a purchasable for a specific album.
	 *
	 * @param string $album_id
	 *
	 * @return self
	 */
	public function forAlbum(string $album_id): self
	{
		return $this->state(fn (array $attributes) => [
			'album_id' => $album_id,
			'photo_id' => null,
		]);
	}

	/**
	 * Create a purchasable for a specific photo.
	 *
	 * @param string $photo_id
	 * @param string $album_id
	 *
	 * @return self
	 */
	public function forPhoto(string $photo_id, string $album_id): self
	{
		return $this->state(fn (array $attributes) => [
			'album_id' => $album_id,
			'photo_id' => $photo_id,
		]);
	}

	/**
	 * Mark the purchasable as inactive.
	 *
	 * @return self
	 */
	public function inactive(): self
	{
		return $this->state(fn (array $attributes) => [
			'is_active' => false,
		]);
	}
}
