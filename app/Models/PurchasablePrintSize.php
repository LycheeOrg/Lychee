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
 * Class PurchasablePrintSize.
 *
 * Represents a per-purchasable print size assignment with price.
 * Each row links a Purchasable to a PrintSize from the global catalogue
 * and defines the price for that combination.
 *
 * @property int         $id             Primary key
 * @property int         $purchasable_id Foreign key to purchasables
 * @property int         $print_size_id  Foreign key to print_sizes
 * @property Money       $price_cents    Price for this size on this purchasable
 * @property PrintSize   $printSize      The associated global print size
 * @property Purchasable $purchasable    The associated purchasable
 */
class PurchasablePrintSize extends Model
{
	/** @phpstan-use HasFactory<\Database\Factories\PurchasablePrintSizeFactory> */
	use HasFactory;

	public $timestamps = false;

	/**
	 * Always eager-load the related global print size so that resources can
	 * read its fields (label, width, height…) without triggering lazy loads.
	 */
	protected $with = ['printSize'];

	/**
	 * {@inheritdoc}
	 */
	protected $fillable = [
		'purchasable_id',
		'print_size_id',
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
	 * Get the global print size catalogue entry.
	 *
	 * @return BelongsTo<PrintSize,$this>
	 */
	public function printSize(): BelongsTo
	{
		return $this->belongsTo(PrintSize::class);
	}
}
