<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enum\PurchasableLicenseType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Money\Money;

/**
 * Class PurchasablePixelSize.
 *
 * Represents a per-purchasable pixel size assignment with price.
 * Each row links a Purchasable to a PixelSize from the global catalogue
 * and defines the price for that combination.
 *
 * @property int                    $id             Primary key
 * @property int                    $purchasable_id Foreign key to purchasables
 * @property int                    $pixel_size_id  Foreign key to pixel_sizes
 * @property Money                  $price_cents    Price for this size on this purchasable
 * @property PurchasableLicenseType $license_type   License type for this assignment
 * @property PixelSize              $pixelSize      The associated global pixel size
 * @property Purchasable            $purchasable    The associated purchasable
 */
class PurchasablePixelSize extends Model
{
	/** @phpstan-use HasFactory<\Database\Factories\PurchasablePixelSizeFactory> */
	use HasFactory;

	public $timestamps = false;

	/**
	 * Always eager-load the related global pixel size so that resources can
	 * read its fields (label, width, height…) without triggering lazy loads.
	 */
	protected $with = ['pixelSize'];

	/**
	 * {@inheritdoc}
	 */
	protected $fillable = [
		'purchasable_id',
		'pixel_size_id',
		'price_cents',
		'license_type',
	];

	/**
	 * {@inheritdoc}
	 */
	protected $casts = [
		'price_cents' => MoneyCast::class,
		'license_type' => PurchasableLicenseType::class,
	];

	/**
	 * Get the purchasable this entry belongs to.
	 *
	 * @return BelongsTo<Purchasable,$this>
	 */
	public function purchasable(): BelongsTo
	{
		return $this->belongsTo(Purchasable::class);
	}

	/**
	 * Get the global pixel size catalogue entry.
	 *
	 * @return BelongsTo<PixelSize,$this>
	 */
	public function pixelSize(): BelongsTo
	{
		return $this->belongsTo(PixelSize::class);
	}
}
