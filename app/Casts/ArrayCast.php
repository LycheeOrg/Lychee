<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class ArrayCast implements CastsAttributes
{
	/**
	 * @param Model  $model      the associated model class
	 * @param string $key        the name of the SQL column holding the stringified array
	 * @param mixed  $value      the stringified array
	 * @param array  $attributes all SQL attributes of the entity
	 *
	 * @return array the array
	 */
	public function get($model, string $key, $value, array $attributes): array
	{
		return ($value === null || $value === '') ? [] : explode(',', strval($value));
	}

	/**
	 * @param Model  $model      the associated model class
	 * @param string $key        the name of the SQL column holding the stringified array
	 * @param mixed  $value      the array
	 * @param array  $attributes
	 *
	 * @return array An associative map of SQL columns and their values
	 */
	public function set($model, string $key, $value, array $attributes): array
	{
		// Normalize the input value
		// The array must not contain empty tags and tags which contain a comma
		// TODO: Either use a separate table to store the tags or another encoding (e.g. JSON) which also allows commas in tags

		$arr = !is_array($value) ? [] : array_values(array_filter(
			$value,
			fn ($elem) => ($elem !== null && $elem !== '' && !str_contains($elem, ',')),
		));

		return [
			$key => count($arr) === 0 ? null : implode(',', $arr),
		];
	}
}
