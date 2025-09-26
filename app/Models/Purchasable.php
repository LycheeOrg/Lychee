<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Models;

use App\Enum\PurchasableLicenseType;
use App\Enum\PurchasableSizeVariantType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Money\Money;

/**
 * Class Purchasable.
 *
 * @property int                              $id
 * @property string|null                      $photo_id
 * @property string|null                      $album_id
 * @property string|null                      $description
 * @property string|null                      $owner_notes
 * @property bool                             $is_active
 * @property Album|null                       $album
 * @property Photo|null                       $photo
 * @property Collection<int,PurchasablePrice> $prices
 *
 * Defines whether a photo or album is available for purchase and its pricing options.
 */
class Purchasable extends Model
{
	/** @phpstan-use HasFactory<\Database\Factories\PurchasableFactory> */
	use HasFactory;

	public $timestamps = false;

	/**
	 * {@inheritdoc}
	 */
	protected $fillable = [
		'album_id',
		'photo_id',
		'description',
		'owner_notes',
		'is_active',
	];

	/**
	 * {@inheritdoc}
	 */
	protected $casts = [
		'is_active' => 'boolean',
	];

	/**
	 * {@inheritdoc}
	 */
	protected $with = ['prices'];

	/**
	 * Get the album associated with this purchasable item.
	 */
	public function album(): BelongsTo
	{
		return $this->belongsTo(Album::class);
	}

	/**
	 * Get the photo associated with this purchasable item.
	 */
	public function photo(): BelongsTo
	{
		return $this->belongsTo(Photo::class);
	}

	/**
	 * Get the prices for this purchasable item.
	 *
	 * @return HasMany<PurchasablePrice,$this>
	 */
	public function prices(): HasMany
	{
		return $this->hasMany(PurchasablePrice::class);
	}

	/**
	 * Get price for specific size and license combination.
	 *
	 * @param PurchasableSizeVariantType $size_variant The size variant (MEDIUM, FULL, ORIGINAL)
	 * @param PurchasableLicenseType     $license_type The license type (PERSONAL, COMMERCIAL, EXTENDED)
	 *
	 * @return Money|null The Money object or null if not available
	 */
	public function getPriceFor(PurchasableSizeVariantType $size_variant, PurchasableLicenseType $license_type): ?Money
	{
		$price = $this->prices()
			->where('size_variant', $size_variant)
			->where('license_type', $license_type)
			->first();

		return $price?->price_cents;
	}

	/**
	 * Set price for a specific size and license combination.
	 *
	 * @param PurchasableSizeVariantType $size_variant The size variant (MEDIUM, FULL, ORIGINAL)
	 * @param PurchasableLicenseType     $license_type The license type (PERSONAL, COMMERCIAL, EXTENDED)
	 * @param Money                      $money        The money object to set
	 *
	 * @return PurchasablePrice The created or updated price entry
	 */
	public function setPriceFor(PurchasableSizeVariantType $size_variant, PurchasableLicenseType $license_type, Money $money): PurchasablePrice
	{
		return $this->prices()->updateOrCreate(
			[
				'size_variant' => $size_variant,
				'license_type' => $license_type,
			],
			[
				'price_cents' => $money,
			]
		);
	}

	/**
	 * Check if this is an album-level purchasable.
	 *
	 * @return bool
	 */
	public function isAlbumLevel(): bool
	{
		return $this->album_id !== null && $this->photo_id === null;
	}
}
