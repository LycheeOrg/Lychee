<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace Database\Factories;

use App\Enum\PurchasableLicenseType;
use App\Enum\PurchasableSizeVariantType;
use App\Models\Album;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Photo;
use App\Models\Purchasable;
use App\Services\MoneyService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var string
	 */
	protected $model = OrderItem::class;

	/**
	 * Define the model's default state.
	 *
	 * @return array<string,mixed>
	 */
	public function definition(): array
	{
		return [
			'order_id' => null,
			'purchasable_id' => null,
			'album_id' => null,
			'photo_id' => null,
			'title' => null,
			'license_type' => fake()->randomElement(PurchasableLicenseType::cases()),
			'price_cents' => resolve(MoneyService::class)->createFromCents(fake()->numberBetween(500, 5000)),
			'size_variant_type' => fake()->randomElement(PurchasableSizeVariantType::cases()),
			'item_notes' => fake()->optional()->sentence(),
		];
	}

	/**
	 * Create an order item for a specific order.
	 *
	 * @param Order|int $order
	 *
	 * @return static
	 */
	public function forOrder(Order|int $order): static
	{
		$order_id = $order instanceof Order ? $order->id : $order;

		return $this->state(fn (array $attributes) => [
			'order_id' => $order_id,
		]);
	}

	/**
	 * Create an order item for a specific purchasable.
	 *
	 * @param Purchasable|int $purchasable
	 *
	 * @return static
	 */
	public function forPurchasable(Purchasable|int $purchasable): static
	{
		$purchasable_id = $purchasable instanceof Purchasable ? $purchasable->id : $purchasable;

		return $this->state(fn (array $attributes) => [
			'purchasable_id' => $purchasable_id,
		]);
	}

	/**
	 * Create an order item for a photo.
	 *
	 * @param Photo|string|null $photo
	 *
	 * @return static
	 */
	public function forPhoto(Photo|string|null $photo = null): static
	{
		$photo_title = fake()->words(2, true) . ' Photo';
		if ($photo instanceof string || $photo === null) {
			$photo_id = $photo;
		} else {
			$photo_id = $photo->id;
			$photo_title = $photo->title ?? $photo_title;
		}

		return $this->state(fn (array $attributes) => [
			'photo_id' => $photo_id,
			'title' => $photo_title,
		]);
	}

	/**
	 * Create an order item for an album.
	 *
	 * @param Album|string|null $album
	 *
	 * @return static
	 */
	public function forAlbum(Album|string|null $album = null): static
	{
		$album_title = fake()->words(2, true) . ' Ahoto';
		if ($album instanceof string || $album === null) {
			$album_id = $album;
		} else {
			$album_id = $album->id;
			$album_title = $album->title ?? $album_title;
		}

		return $this->state(fn (array $attributes) => [
			'album_id' => $album_id,
			'title' => $album_title,
		]);
	}

	/**
	 * Create an order item with a specific license type.
	 *
	 * @param PurchasableLicenseType $licenseType
	 *
	 * @return static
	 */
	public function withLicenseType(PurchasableLicenseType $licenseType): static
	{
		return $this->state(fn (array $attributes) => [
			'license_type' => $licenseType,
		]);
	}

	/**
	 * Create an order item with a specific size variant.
	 *
	 * @param PurchasableSizeVariantType $sizeVariant
	 *
	 * @return static
	 */
	public function withSizeVariant(PurchasableSizeVariantType $sizeVariant): static
	{
		return $this->state(fn (array $attributes) => [
			'size_variant_type' => $sizeVariant,
		]);
	}

	/**
	 * Create an order item with personal license.
	 *
	 * @return static
	 */
	public function personalLicense(): static
	{
		return $this->withLicenseType(PurchasableLicenseType::PERSONAL);
	}

	/**
	 * Create an order item with commercial license.
	 *
	 * @return static
	 */
	public function commercialLicense(): static
	{
		return $this->withLicenseType(PurchasableLicenseType::COMMERCIAL);
	}

	/**
	 * Create an order item with extended license.
	 *
	 * @return static
	 */
	public function extendedLicense(): static
	{
		return $this->withLicenseType(PurchasableLicenseType::EXTENDED);
	}

	/**
	 * Create an order item with medium size variant.
	 *
	 * @return static
	 */
	public function mediumSize(): static
	{
		return $this->withSizeVariant(PurchasableSizeVariantType::MEDIUM);
	}

	/**
	 * Create an order item with medium2x size variant.
	 *
	 * @return static
	 */
	public function medium2xSize(): static
	{
		return $this->withSizeVariant(PurchasableSizeVariantType::MEDIUM2x);
	}

	/**
	 * Create an order item with original size variant.
	 *
	 * @return static
	 */
	public function originalSize(): static
	{
		return $this->withSizeVariant(PurchasableSizeVariantType::ORIGINAL);
	}

	/**
	 * Create an order item with full size variant.
	 *
	 * @return static
	 */
	public function fullSize(): static
	{
		return $this->withSizeVariant(PurchasableSizeVariantType::FULL);
	}

	/**
	 * Create an order item with a specific price in cents.
	 *
	 * @param int $cents
	 *
	 * @return static
	 */
	public function withPriceCents(int $cents): static
	{
		return $this->state(fn (array $attributes) => [
			'price_cents' => resolve(MoneyService::class)->createFromCents($cents),
		]);
	}

	/**
	 * Create an order item with a specific price in dollars.
	 *
	 * @param float $dollars
	 *
	 * @return static
	 */
	public function withPriceDollars(float $dollars): static
	{
		return $this->withPriceCents((int) ($dollars * 100));
	}

	/**
	 * Create an order item with a specific title.
	 *
	 * @param string $title
	 *
	 * @return static
	 */
	public function withTitle(string $title): static
	{
		return $this->state(fn (array $attributes) => [
			'title' => $title,
		]);
	}

	/**
	 * Create an order item with notes.
	 *
	 * @param string|null $notes
	 *
	 * @return static
	 */
	public function withNotes(string|null $notes = null): static
	{
		return $this->state(fn (array $attributes) => [
			'item_notes' => $notes ?? fake()->paragraph(),
		]);
	}
}