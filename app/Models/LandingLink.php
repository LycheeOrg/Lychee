<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Models;

use App\Enum\LandingLinkPlacement;
use App\Models\Extensions\ThrowsConsistentExceptions;
use App\Models\Extensions\UTCBasedTimes;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\LandingLink.
 *
 * @property string               $id
 * @property string               $label
 * @property string               $url
 * @property LandingLinkPlacement $placement
 * @property bool                 $open_in_new_tab
 * @property int                  $sort_order
 * @property bool                 $enabled
 * @property bool                 $is_built_in
 * @property Carbon               $created_at
 * @property Carbon               $updated_at
 */
class LandingLink extends Model
{
	/** @phpstan-use HasFactory<\Database\Factories\LandingLinkFactory> */
	use HasFactory;
	use UTCBasedTimes;
	use ThrowsConsistentExceptions;

	public $incrementing = false;
	protected $keyType = 'string';

	/**
	 * `is_built_in` is intentionally excluded: it marks the built-in
	 * "Gallery"/"Contact" rows and must never be settable through mass
	 * assignment from the Store/Update API requests.
	 */
	protected $fillable = [
		'label', 'url', 'placement', 'open_in_new_tab', 'sort_order', 'enabled',
	];

	/** @var array<string,mixed> */
	protected $attributes = [
		'open_in_new_tab' => true,
		'sort_order' => 0,
		'enabled' => true,
		'is_built_in' => false,
	];

	protected $casts = [
		'placement' => LandingLinkPlacement::class,
		'open_in_new_tab' => 'boolean',
		'sort_order' => 'integer',
		'enabled' => 'boolean',
		'is_built_in' => 'boolean',
		'created_at' => 'datetime',
		'updated_at' => 'datetime',
	];

	protected static function boot(): void
	{
		parent::boot();

		static::creating(function (LandingLink $landing_link): void {
			if ($landing_link->id === null || $landing_link->id === '') {
				$landing_link->id = (string) \Illuminate\Support\Str::ulid();
			}
		});
	}

	public function scopeEnabled(Builder $query): Builder
	{
		return $query->where('enabled', '=', true);
	}
}
