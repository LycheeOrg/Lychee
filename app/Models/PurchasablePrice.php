<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enum\PurchasableLicenseType;
use App\Enum\PurchasableSizeVariantType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class PurchasablePrice.
 *
 * @property int                        $id
 * @property int                        $purchasable_id
 * @property PurchasableSizeVariantType $size_variant
 * @property PurchasableLicenseType     $license_type
 * @property \Money\Money               $price_cents
 *
 * Defines a price for a specific size variant and license type combination.
 */
class PurchasablePrice extends Model
{
	/** @phpstan-use HasFactory<\Database\Factories\PurchasablePriceFactory> */
	use HasFactory;

	public $timestamps = false;

	/**
	 * {@inheritdoc}
	 */
	protected $fillable = [
		'purchasable_id',
		'size_variant',
		'license_type',
		'price_cents',
	];

	/**
	 * {@inheritdoc}
	 */
	protected $casts = [
		'price_cents' => MoneyCast::class,
		'size_variant' => PurchasableSizeVariantType::class,
		'license_type' => PurchasableLicenseType::class,
	];

	/**
	 * Get the purchasable item this price belongs to.
	 */
	public function purchasable(): BelongsTo
	{
		return $this->belongsTo(Purchasable::class);
	}
}
