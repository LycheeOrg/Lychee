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
 * Class OrderItem.
 *
 * Represents an individual item within a purchase order in the webshop system.
 * Each order item corresponds to a specific photo or album being purchased with
 * a particular size variant and license type combination.
 *
 * Order items capture the state of a purchasable item at the time of purchase,
 * including the price paid, license type selected, and size variant chosen.
 * This allows for historical accuracy even if the original purchasable item
 * is modified or deleted after the purchase.
 *
 * The item can reference either:
 * - A specific photo with a size variant (most common case)
 * - An album purchase (for bulk album downloads)
 * - A custom download link (for special cases like FULL size variants)
 *
 * @property int                        $id                Primary key
 * @property int                        $order_id          Foreign key to the parent order
 * @property int|null                   $purchasable_id    Foreign key to the purchasable definition (nullable if deleted)
 * @property string|null                $album_id          Foreign key to album (for album-level purchases)
 * @property string|null                $photo_id          Foreign key to photo (for photo purchases)
 * @property string                     $title             Item title at time of purchase (for historical record)
 * @property int|null                   $size_variant_id   Foreign key to size variant (nullable for custom sizes)
 * @property string|null                $download_link     Custom download URL (used for FULL variants or special cases)
 * @property bool                       $is_print          True when item requires physical print fulfilment
 * @property int|null                   $print_size_id     Foreign key to print_sizes (nullable)
 * @property int|null                   $pixel_size_id     Foreign key to pixel_sizes (nullable)
 * @property int|null                   $print_width       Snapshot of print width at basket-add time
 * @property int|null                   $print_height      Snapshot of print height at basket-add time
 * @property string|null                $print_unit        Snapshot of print unit at basket-add time
 * @property string|null                $print_paper_type  Snapshot of print paper type at basket-add time
 * @property int|null                   $pixel_width       Snapshot of pixel width at basket-add time
 * @property int|null                   $pixel_height      Snapshot of pixel height at basket-add time
 * @property PurchasableLicenseType     $license_type      License type purchased (personal, commercial, extended, print)
 * @property \Money\Money               $price_cents       Price paid for this item (uses Money library for precision)
 * @property PurchasableSizeVariantType $size_variant_type Size variant purchased (medium, medium2x, original, full)
 * @property string|null                $item_notes        Optional notes specific to this item
 * @property Order                      $order             The parent order this item belongs to
 * @property Purchasable|null           $purchasable       The purchasable definition this item was based on
 * @property Photo|null                 $photo             The photo being purchased (if applicable)
 * @property Album|null                 $album             The album being purchased (if applicable)
 * @property SizeVariant|null           $size_variant      The size variant being purchased (if applicable)
 * @property PrintSize|null             $printSize         The print size catalogue entry (if applicable)
 * @property PixelSize|null             $pixelSize         The pixel size catalogue entry (if applicable)
 *
 * @see Order The parent order model
 * @see Purchasable The purchasable item definition
 * @see PurchasableLicenseType License type enumeration
 * @see PurchasableSizeVariantType Size variant enumeration
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
		'size_variant_id',
		'download_link',
		'is_print',
		'print_size_id',
		'pixel_size_id',
		'print_width',
		'print_height',
		'print_unit',
		'print_paper_type',
		'pixel_width',
		'pixel_height',
	];

	/**
	 * {@inheritdoc}
	 */
	protected $casts = [
		'price_cents' => MoneyCast::class,
		'license_type' => PurchasableLicenseType::class,
		'size_variant_type' => PurchasableSizeVariantType::class,
		'is_print' => 'boolean',
	];

	/**
	 * {@inheritdoc}
	 */
	protected $with = [
		'size_variant',
	];

	/**
	 * Get the order this item belongs to.
	 *
	 * @return BelongsTo<Order,$this>
	 */
	public function order(): BelongsTo
	{
		return $this->belongsTo(Order::class);
	}

	/**
	 * Get the purchasable definition this item was based on.
	 *
	 * This relationship may be null if the original purchasable item
	 * has been deleted after the order was placed.
	 *
	 * @return BelongsTo<Purchasable,$this>
	 */
	public function purchasable(): BelongsTo
	{
		return $this->belongsTo(Purchasable::class);
	}

	/**
	 * Get the photo this item refers to.
	 *
	 * This is populated for photo-level purchases and provides access
	 * to the original photo metadata and content.
	 *
	 * @return BelongsTo<Photo,$this>
	 */
	public function photo(): BelongsTo
	{
		return $this->belongsTo(Photo::class);
	}

	/**
	 * Get the SizeVariant this item refers to.
	 *
	 * This relationship provides access to the specific size variant
	 * being purchased. May be null for custom sizes or album purchases.
	 *
	 * @return BelongsTo<SizeVariant,$this>
	 */
	public function size_variant(): BelongsTo
	{
		return $this->belongsTo(SizeVariant::class);
	}

	/**
	 * Get the album this item refers to.
	 *
	 * This is populated for album-level purchases where the customer
	 * is purchasing access to an entire album.
	 *
	 * @return BelongsTo<Album,$this>
	 */
	public function album(): BelongsTo
	{
		return $this->belongsTo(Album::class);
	}

	/**
	 * Get the global print size catalogue entry for this item.
	 *
	 * @return BelongsTo<PrintSize,$this>
	 */
	public function printSize(): BelongsTo
	{
		return $this->belongsTo(PrintSize::class);
	}

	/**
	 * Get the global pixel size catalogue entry for this item.
	 *
	 * @return BelongsTo<PixelSize,$this>
	 */
	public function pixelSize(): BelongsTo
	{
		return $this->belongsTo(PixelSize::class);
	}

	/**
	 * Get the download URL for this order item's content.
	 *
	 * This method determines the appropriate download URL based on the
	 * item type and configuration:
	 * 1. If a custom download_link is set, use it (for FULL variants, etc.)
	 * 2. If a size_variant_id is set, use the size variant's download URL
	 * 3. For albums, this functionality is not yet implemented
	 * 4. Returns null if no download source is available
	 *
	 * @return string|null The download URL or null if not available
	 */
	public function getContentUrlAttribute(): string|null
	{
		// If download_link is set, use it
		if ($this->download_link !== null) {
			return $this->download_link;
		}

		// Size variant not set
		if ($this->size_variant_id !== null) {
			return $this->size_variant?->getDownloadUrlAttribute();
		}

		// TODO: later consider how to handle albums.

		// No data associated
		// Return null.
		return null;
	}
}
