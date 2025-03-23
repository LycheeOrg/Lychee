<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Models;

use App\Models\Builders\ConfigCategoryBuilder;
use App\Models\Extensions\ThrowsConsistentExceptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\ConfigCategory.
 *
 * @property string      $cat
 * @property string      $name
 * @property string|null $description
 * @property int         $order
 *
 * @method static ConfigsBuilder|Configs addSelect($column)
 * @method static ConfigsBuilder|Configs join(string $table, string $first, string $operator = null, string $second = null, string $type = 'inner', string $where = false)
 * @method static ConfigsBuilder|Configs joinSub($query, $as, $first, $operator = null, $second = null, $type = 'inner', $where = false)
 * @method static ConfigsBuilder|Configs leftJoin(string $table, string $first, string $operator = null, string $second = null)
 * @method static ConfigsBuilder|Configs newModelQuery()
 * @method static ConfigsBuilder|Configs newQuery()
 * @method static ConfigsBuilder|Configs orderBy($column, $direction = 'asc')
 * @method static ConfigsBuilder|Configs query()
 * @method static ConfigsBuilder|Configs select($columns = [])
 * @method static ConfigsBuilder|Configs whereCat($value)
 * @method static ConfigsBuilder|Configs whereConfidentiality($value)
 * @method static ConfigsBuilder|Configs whereDescription($value)
 * @method static ConfigsBuilder|Configs whereId($value)
 * @method static ConfigsBuilder|Configs whereIn(string $column, string $values, string $boolean = 'and', string $not = false)
 * @method static ConfigsBuilder|Configs whereKey($value)
 * @method static ConfigsBuilder|Configs whereNotIn(string $column, string $values, string $boolean = 'and')
 * @method static ConfigsBuilder|Configs whereTypeRange($value)
 * @method static ConfigsBuilder|Configs whereValue($value)
 *
 * @mixin \Eloquent
 */
class ConfigCategory extends Model
{
	use ThrowsConsistentExceptions;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int,string>
	 */
	protected $fillable = ['cat', 'name', 'description', 'order'];

	/**
	 * @param $query
	 *
	 * @return ConfigCategoryBuilder
	 */
	public function newEloquentBuilder($query): ConfigCategoryBuilder
	{
		return new ConfigCategoryBuilder($query);
	}

	/**
	 * Returns the relationship between an album and its associated permissions.
	 *
	 * @return hasMany<Configs,$this>
	 */
	public function configs(): HasMany
	{
		return $this->hasMany(Configs::class, 'cat', 'cat')->orderBy('order', 'asc')->orderBy('key', 'asc');
	}
}
