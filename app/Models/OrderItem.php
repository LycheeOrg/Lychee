<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enum\PurchasableLicenseType;
use App\Enum\PurchasableSizeVariantType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class OrderItem.
 *
 * @property int                        $id
 * @property int                        $order_id
 * @property int|null                   $purchasable_id
 * @property string|null                $album_id
 * @property string|null                $photo_id
 * @property string                     $title
 * @property PurchasableLicenseType     $license_type
 * @property \Money\Money               $price_cents
 * @property PurchasableSizeVariantType $size_variant_type
 * @property string|null                $item_notes
 * @property Order                      $order
 * @property Purchasable                $purchasable
 * @property Photo|null                 $photo
 * @property Album|null                 $album
 *
 * Represents an individual item within an order.
 */
class OrderItem extends Model
{
	/** @phpstan-use HasFactory<\Database\Factories\OrderItemFactory> */
	use HasFactory;

	public $timestamps = false;

	/**
	 * {@inheritdoc}
	 */
	protected $fillable = [
		'order_id',
		'purchasable_id',
		'album_id',
		'photo_id',
		'title',
		'license_type',
		'price_cents',
		'size_variant_type',
		'item_notes',
	];

	/**
	 * {@inheritdoc}
	 */
	protected $casts = [
		'price_cents' => MoneyCast::class,
		'license_type' => PurchasableLicenseType::class,
		'size_variant_type' => PurchasableSizeVariantType::class,
	];

	/**
	 * Get the order this item belongs to.
	 */
	public function order(): BelongsTo
	{
		return $this->belongsTo(Order::class);
	}

	/**
	 * Get the purchasable definition this item was based on.
	 */
	public function purchasable(): BelongsTo
	{
		return $this->belongsTo(Purchasable::class);
	}

	/**
	 * Get the photo this item refers to.
	 */
	public function photo(): BelongsTo
	{
		return $this->belongsTo(Photo::class);
	}

	/**
	 * Get the album this item refers to.
	 */
	public function album(): BelongsTo
	{
		return $this->belongsTo(Album::class);
	}
}
