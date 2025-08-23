<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Models;

use App\Enum\RenamerModeType;
use App\Models\Builders\RenamerRuleBuilder;
use App\Models\Extensions\ThrowsConsistentExceptions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\RenamerRule.
 *
 * This model represents a rule for renaming strings based on pattern matching.
 * Each rule defines a pattern to find (needle) and what to replace it with (replacement),
 * along with the mode that determines how the replacement should be performed.
 * Rules are ordered by the 'order' field and can be enabled/disabled.
 *
 * @property int             $id
 * @property int             $order
 * @property int             $owner_id
 * @property string          $rule
 * @property string          $description
 * @property string          $needle
 * @property string          $replacement
 * @property RenamerModeType $mode
 * @property bool            $is_enabled
 * @property User            $owner
 */
class RenamerRule extends Model
{
	use ThrowsConsistentExceptions;
	/** @phpstan-use HasFactory<\Database\Factories\RenamerRuleFactory> */
	use HasFactory;

	// Disable the default timestamps handling
	public $timestamps = false;

	/**
	 * @var list<string> the attributes that are mass assignable
	 */
	protected $fillable = [
		'order',
		'owner_id',
		'rule',
		'description',
		'needle',
		'replacement',
		'mode',
		'is_enabled',
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array<string,string>
	 */
	protected $casts = [
		'order' => 'integer',
		'owner_id' => 'integer',
		'mode' => RenamerModeType::class,
		'is_enabled' => 'boolean',
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var list<string>
	 */
	protected $hidden = [];

	/**
	 * Get the owner of the renamer rule.
	 *
	 * @return BelongsTo<User,$this>
	 */
	public function owner(): BelongsTo
	{
		return $this->belongsTo(User::class, 'owner_id');
	}

	/**
	 * @param $query
	 *
	 * @return RenamerRuleBuilder
	 */
	public function newEloquentBuilder($query): RenamerRuleBuilder
	{
		return new RenamerRuleBuilder($query);
	}
}
