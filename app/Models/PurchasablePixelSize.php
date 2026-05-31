<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Models;

use App\Casts\MoneyCast;
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
 * @property int         $id             Primary key
 * @property int         $purchasable_id Foreign key to purchasables
 * @property int         $pixel_size_id  Foreign key to pixel_sizes
 * @property Money       $price_cents    Price for this size on this purchasable
 * @property PixelSize   $pixelSize      The associated global pixel size
 * @property Purchasable $purchasable    The associated purchasable
 */
class PurchasablePixelSize extends Model
{
	/** @phpstan-use HasFactory<\Database\Factories\PurchasablePixelSizeFactory> */
	use HasFactory;

	public $timestamps = false;

	/**
	 * {@inheritdoc}
	 */
	protected $fillable = [
		'purchasable_id',
		'pixel_size_id',
		'price_cents',
	];

	/**
	 * {@inheritdoc}
	 */
	protected $casts = [
		'price_cents' => MoneyCast::class,
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
