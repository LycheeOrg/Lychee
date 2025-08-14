<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Casts;

use App\Models\Tag;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * @implements CastsAttributes<array,(Tag|null)[]>
 */
class TagArrayCast implements CastsAttributes
{
	/**
	 * @param Model               $model      the associated model class
	 * @param string              $key        the name of the SQL column holding the stringified array
	 * @param mixed               $value      the stringified array
	 * @param array<string,mixed> $attributes all SQL attributes of the entity
	 *
	 * @return array<int,Tag> the array
	 */
	public function get(Model $model, string $key, mixed $value, array $attributes): array
	{
		if ($value === null || $value === '') {
			return [];
		}

		// Split the string by ' OR ' and return an array of Tag objects
		return Tag::whereIn('id', explode(' OR ', strval($value)))->get()->all();
	}

	/**
	 * @param Model               $model      the associated model class
	 * @param string              $key        the name of the SQL column holding the stringified array
	 * @param (Tag|null)[]|null   $value      the array
	 * @param array<string,mixed> $attributes
	 *
	 * @return array<string,mixed> An associative map of SQL columns and their values
	 */
	public function set(Model $model, string $key, mixed $value, array $attributes): array
	{
		// Normalize the input value
		// TODO: Either use a separate table to store the tags or another encoding (e.g. JSON) which also allows commas in tags

		$arr = !is_array($value) ? [] : array_values(array_filter(
			$value,
			fn ($elem) => ($elem !== null && $elem !== '' && $elem instanceof Tag && $elem->name !== ''),
		));

		return [
			$key => count($arr) === 0 ? null : implode(' OR ', array_map(fn ($t) => $t->id, $arr)),
		];
	}
}
