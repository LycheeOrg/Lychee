<?php

namespace App\Casts;

use App\Models\PatchedBaseModel;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Carbon;

class DateTimeWithTimezoneCast implements CastsAttributes
{
	const TZ_ATTRIBUTE_SUFFIX = '_orig_tz';

	/**
	 * Cast the given value into a Carbon object which respects the timezone
	 * which accompanies the attribute.
	 *
	 * Attention:
	 * For this method to work properly, the method assume that the database
	 * relation contains a second string attribute whose name equals
	 * $key . '_orig_tz' and which stores the original timezone of the
	 * (key, value)-pair at hand.
	 * Moreover, this class assumes that the associated model extends
	 * {@link \App\Models\PatchedBaseModel}, because this method relies on
	 * proper datetime conversion with respect to the underlying timezone of
	 * the database.
	 *
	 * @param PatchedBaseModel $model      the associated model class
	 * @param string           $key        the name of the SQL column holding the datetime
	 * @param string           $value      the SQL datetime string
	 * @param array            $attributes all SQL attributes of the entity
	 *
	 * @return Carbon|null The Carbon object with a properly set timezone
	 */
	public function get($model, string $key, $value, array $attributes): ?Carbon
	{
		$tzKey = $key . self::TZ_ATTRIBUTE_SUFFIX;
		if ($value === null) {
			return null;
		}
		if (!($model instanceof PatchedBaseModel)) {
			throw new \InvalidArgumentException('$model must extend \App\Models\PatchedBaseModel');
		}
		if (array_key_exists($tzKey, $attributes)) {
			$tz = $attributes[$tzKey];
		} else {
			throw new \InvalidArgumentException('Missing column \'' . $tzKey . '\'');
		}
		// If the datetime value is non-null, then the accompanying timezone
		// must not be null neither.
		if (!is_string($tz) || empty($tz)) {
			throw new \InvalidArgumentException('Column \'' . $key . '\' is not null, but column \'' . $tzKey . '\' is either not a string, an empty string or null');
		}
		$result = $model->asDateTime($value);
		$result->setTimezone($tz);

		return $result;
	}

	/**
	 * Converts the given value into an SQL string for storage.
	 *
	 * Attention:
	 * For this method to work properly, the method assume that the associated
	 * model extends {@link \App\Models\PatchedBaseModel}, because this method
	 * relies on proper datetime conversion with respect to the underlying
	 * timezone of the database.
	 *
	 * @param PatchedBaseModel $model      the associated model class
	 * @param string           $key        the name of the SQL column holding the datetime
	 * @param Carbon|null      $value      the Carbon object of the model
	 * @param array            $attributes
	 *
	 * @return array An associative map of SQL columns and their values
	 */
	public function set($model, string $key, $value, array $attributes): array
	{
		$tzKey = $key . self::TZ_ATTRIBUTE_SUFFIX;
		if (!($model instanceof PatchedBaseModel)) {
			throw new \InvalidArgumentException('$model must extend \App\Models\PatchedBaseModel');
		}
		if ($value !== null && !($value instanceof Carbon)) {
			throw new \InvalidArgumentException('$value must extend \DateTimeInterface');
		}
		$sqlDatetimeString = $model->fromDateTime($value);
		$sqlTimezoneString = $value === null ? null : $value->getTimezone()->getName();

		return [
			$key => $sqlDatetimeString,
			$tzKey => $sqlTimezoneString,
		];
	}
}
