<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PixelSize.
 *
 * Represents a global pixel size catalogue entry. Administrators define
 * available pixel sizes (dimensions in pixels) here; per-purchasable
 * prices are stored in the `purchasable_pixel_sizes` table.
 *
 * @property int    $id
 * @property string $label     Display label shown to customers
 * @property int    $width     Width in pixels
 * @property int    $height    Height in pixels
 * @property bool   $is_active Whether this size is visible to customers
 */
class PixelSize extends Model
{
	/** @phpstan-use HasFactory<\Database\Factories\PixelSizeFactory> */
	use HasFactory;

	public $timestamps = false;

	/**
	 * {@inheritdoc}
	 */
	protected $fillable = [
		'label',
		'width',
		'height',
		'is_active',
	];

	/**
	 * {@inheritdoc}
	 */
	protected $casts = [
		'is_active' => 'boolean',
	];

	/**
	 * Scope a query to only include active pixel sizes.
	 *
	 * @param Builder<PixelSize> $query
	 *
	 * @return Builder<PixelSize>
	 */
	public function scopeActive(Builder $query): Builder
	{
		return $query->where('is_active', true);
	}
}
