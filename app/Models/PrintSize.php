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
 * Class PrintSize.
 *
 * Represents a global print size catalogue entry. Administrators define
 * available print sizes (dimensions + unit + optional paper type) here;
 * per-purchasable prices are stored in the `purchasable_print_sizes` table.
 *
 * @property int         $id
 * @property string      $label      Display label shown to customers
 * @property int         $width      Width dimension
 * @property int         $height     Height dimension
 * @property string      $unit       Unit of measurement ('cm' or 'inch')
 * @property string|null $paper_type Optional paper type description
 * @property bool        $is_active  Whether this size is visible to customers
 */
class PrintSize extends Model
{
	/** @phpstan-use HasFactory<\Database\Factories\PrintSizeFactory> */
	use HasFactory;

	public $timestamps = false;

	/**
	 * {@inheritdoc}
	 */
	protected $fillable = [
		'label',
		'width',
		'height',
		'unit',
		'paper_type',
		'is_active',
	];

	/**
	 * {@inheritdoc}
	 */
	protected $casts = [
		'is_active' => 'boolean',
	];

	/**
	 * Scope a query to only include active print sizes.
	 *
	 * @param Builder<PrintSize> $query
	 *
	 * @return Builder<PrintSize>
	 */
	public function scopeActive(Builder $query): Builder
	{
		return $query->where('is_active', true);
	}
}
