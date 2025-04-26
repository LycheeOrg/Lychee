<?php

/**
 * SPDX-License-Identifier: MIT
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
 * @method static ConfigCategoryBuilder|ConfigCategory addSelect($column)
 * @method static ConfigCategoryBuilder|ConfigCategory join(string $table, string $first, string $operator = null, string $second = null, string $type = 'inner', string $where = false)
 * @method static ConfigCategoryBuilder|ConfigCategory joinSub($query, $as, $first, $operator = null, $second = null, $type = 'inner', $where = false)
 * @method static ConfigCategoryBuilder|ConfigCategory leftJoin(string $table, string $first, string $operator = null, string $second = null)
 * @method static ConfigCategoryBuilder|ConfigCategory newModelQuery()
 * @method static ConfigCategoryBuilder|ConfigCategory newQuery()
 * @method static ConfigCategoryBuilder|ConfigCategory orderBy($column, $direction = 'asc')
 * @method static ConfigCategoryBuilder|ConfigCategory query()
 * @method static ConfigCategoryBuilder|ConfigCategory select($columns = [])
 * @method static ConfigCategoryBuilder|ConfigCategory whereCat($value)
 * @method static ConfigCategoryBuilder|ConfigCategory whereConfidentiality($value)
 * @method static ConfigCategoryBuilder|ConfigCategory whereDescription($value)
 * @method static ConfigCategoryBuilder|ConfigCategory whereId($value)
 * @method static ConfigCategoryBuilder|ConfigCategory whereIn(string $column, string $values, string $boolean = 'and', string $not = false)
 * @method static ConfigCategoryBuilder|ConfigCategory whereKey($value)
 * @method static ConfigCategoryBuilder|ConfigCategory whereNotIn(string $column, string $values, string $boolean = 'and')
 * @method static ConfigCategoryBuilder|ConfigCategory whereTypeRange($value)
 * @method static ConfigCategoryBuilder|ConfigCategory whereValue($value)
 *
 * @mixin \Eloquent
 */
class ConfigCategory extends Model
{
	use ThrowsConsistentExceptions;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var list<string>
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
