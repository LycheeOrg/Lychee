<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Models;

use App\Enum\LandingFeaturedItemType;
use App\Models\Extensions\ThrowsConsistentExceptions;
use App\Models\Extensions\UTCBasedTimes;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\LandingFeaturedItem.
 *
 * @property string                  $id
 * @property LandingFeaturedItemType $item_type
 * @property string                  $item_id
 * @property int                     $sort_order
 * @property bool                    $enabled
 * @property Carbon                  $created_at
 * @property Carbon                  $updated_at
 */
class LandingFeaturedItem extends Model
{
	/** @phpstan-use HasFactory<\Database\Factories\LandingFeaturedItemFactory> */
	use HasFactory;
	use UTCBasedTimes;
	use ThrowsConsistentExceptions;

	public $incrementing = false;
	protected $keyType = 'string';

	protected $fillable = [
		'item_type', 'item_id', 'sort_order', 'enabled',
	];

	/** @var array<string,mixed> */
	protected $attributes = [
		'sort_order' => 0,
		'enabled' => true,
	];

	protected $casts = [
		'item_type' => LandingFeaturedItemType::class,
		'sort_order' => 'integer',
		'enabled' => 'boolean',
		'created_at' => 'datetime',
		'updated_at' => 'datetime',
	];

	protected static function boot(): void
	{
		parent::boot();

		static::creating(function (LandingFeaturedItem $landing_featured_item): void {
			if ($landing_featured_item->id === null || $landing_featured_item->id === '') {
				$landing_featured_item->id = (string) \Illuminate\Support\Str::ulid();
			}
		});
	}

	public function scopeEnabled(Builder $query): Builder
	{
		return $query->where('enabled', '=', true);
	}
}
