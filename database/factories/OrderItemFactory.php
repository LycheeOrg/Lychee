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
	 * @var class-string<OrderItem>
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
	 * @return self
	 */
	public function forOrder(Order|int $order): self
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
	 * @return self
	 */
	public function forPurchasable(Purchasable|int $purchasable): self
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
	 * @return self
	 */
	public function forPhoto(Photo|string|null $photo = null): self
	{
		$photo_title = fake()->words(2, true) . ' Photo';
		if (is_string($photo) || $photo === null) {
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
	 * @return self
	 */
	public function forAlbum(Album|string|null $album = null): self
	{
		$album_title = fake()->words(2, true) . ' Album';
		if (is_string($album) || $album === null) {
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
	 * @return self
	 */
	public function withLicenseType(PurchasableLicenseType $licenseType): self
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
	 * @return self
	 */
	public function withSizeVariant(PurchasableSizeVariantType $sizeVariant): self
	{
		return $this->state(fn (array $attributes) => [
			'size_variant_type' => $sizeVariant,
		]);
	}

	/**
	 * Create an order item with personal license.
	 *
	 * @return self
	 */
	public function personalLicense(): self
	{
		return $this->withLicenseType(PurchasableLicenseType::PERSONAL);
	}

	/**
	 * Create an order item with commercial license.
	 *
	 * @return self
	 */
	public function commercialLicense(): self
	{
		return $this->withLicenseType(PurchasableLicenseType::COMMERCIAL);
	}

	/**
	 * Create an order item with extended license.
	 *
	 * @return self
	 */
	public function extendedLicense(): self
	{
		return $this->withLicenseType(PurchasableLicenseType::EXTENDED);
	}

	/**
	 * Create an order item with medium size variant.
	 *
	 * @return self
	 */
	public function mediumSize(): self
	{
		return $this->withSizeVariant(PurchasableSizeVariantType::MEDIUM);
	}

	/**
	 * Create an order item with medium2x size variant.
	 *
	 * @return self
	 */
	public function medium2xSize(): self
	{
		return $this->withSizeVariant(PurchasableSizeVariantType::MEDIUM2x);
	}

	/**
	 * Create an order item with original size variant.
	 *
	 * @return self
	 */
	public function originalSize(): self
	{
		return $this->withSizeVariant(PurchasableSizeVariantType::ORIGINAL);
	}

	/**
	 * Create an order item with full size variant.
	 *
	 * @return self
	 */
	public function fullSize(): self
	{
		return $this->withSizeVariant(PurchasableSizeVariantType::FULL);
	}

	/**
	 * Create an order item with a specific price in cents.
	 *
	 * @param int $cents
	 *
	 * @return self
	 */
	public function withPriceCents(int $cents): self
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
	 * @return self
	 */
	public function withPriceDollars(float $dollars): self
	{
		return $this->withPriceCents((int) ($dollars * 100));
	}

	/**
	 * Create an order item with a specific title.
	 *
	 * @param string $title
	 *
	 * @return self
	 */
	public function withTitle(string $title): self
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
	 * @return self
	 */
	public function withNotes(string|null $notes = null): self
	{
		return $this->state(fn (array $attributes) => [
			'item_notes' => $notes ?? fake()->paragraph(),
		]);
	}
}