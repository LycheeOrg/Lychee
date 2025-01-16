<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Casts;

use App\Exceptions\Internal\LycheeDomainException;
use App\Exceptions\Internal\LycheeInvalidArgumentException;
use App\Exceptions\Internal\MissingModelAttributeException;
use Carbon\Exceptions\InvalidFormatException;
use Carbon\Exceptions\InvalidTimeZoneException;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @implements CastsAttributes<Carbon,Carbon>
 */
class DateTimeWithTimezoneCast implements CastsAttributes
{
	public const TZ_ATTRIBUTE_SUFFIX = '_orig_tz';

	/**
	 * Cast the given value into a Carbon object which respects the timezone
	 * which accompanies the attribute.
	 *
	 * Attention:
	 * For this method to work properly, the method assume that the database
	 * relation contains a second string attribute whose name equals
	 * $key . '_orig_tz' and which stores the original timezone of the
	 * (key, value)-pair at hand.
	 *
	 * @param Model               $model      the associated model class
	 * @param string              $key        the name of the SQL column holding the datetime
	 * @param mixed               $value      the SQL datetime string
	 * @param array<string,mixed> $attributes all SQL attributes of the entity
	 *
	 * @return Carbon|null The Carbon object with a properly set timezone
	 *
	 * @throws LycheeInvalidArgumentException
	 * @throws MissingModelAttributeException
	 * @throws LycheeDomainException
	 * @throws InvalidFormatException
	 * @throws InvalidTimeZoneException
	 */
	public function get(Model $model, string $key, $value, array $attributes): ?Carbon
	{
		$tzKey = $key . self::TZ_ATTRIBUTE_SUFFIX;
		if ($value === null) {
			return null;
		}
		if (!is_string($value)) {
			throw new LycheeInvalidArgumentException('$value must be an SQL datetime string');
		}
		if (array_key_exists($tzKey, $attributes)) {
			$tz = $attributes[$tzKey];
		} else {
			throw new MissingModelAttributeException(get_class($model), $tzKey);
		}
		// If the datetime value is non-null, then the accompanying timezone
		// must not be null neither.
		if (!is_string($tz) || $tz === '') {
			throw new LycheeDomainException('Column \'' . $key . '\' is not null, but column \'' . $tzKey . '\' is either not a string, an empty string or null');
		}
		$result = $model->asDateTime($value);
		$result->setTimezone($tz);

		return $result;
	}

	/**
	 * Converts the given value into an SQL string for storage.
	 *
	 * @param Model               $model      the associated model class
	 * @param string              $key        the name of the SQL column holding the datetime
	 * @param Carbon|null         $value      the Carbon object of the model
	 * @param array<string,mixed> $attributes
	 *
	 * @return array<string,mixed> An associative map of SQL columns and their values
	 *
	 * @throws LycheeInvalidArgumentException
	 * @throws InvalidTimeZoneException
	 */
	public function set(Model $model, string $key, mixed $value, array $attributes): array
	{
		if ($value !== null && !($value instanceof Carbon)) {
			$type = gettype($value);
			if ($type === 'object') {
				$type = get_class($value);
			}
			throw new LycheeInvalidArgumentException('"' . $type . '" does not implement \DateTimeInterface');
		}
		$sqlDatetimeString = $model->fromDateTime($value);
		$sqlTimezoneString = $value?->getTimezone()->getName();
		$tzKey = $key . self::TZ_ATTRIBUTE_SUFFIX;

		return [
			$key => $sqlDatetimeString,
			$tzKey => $sqlTimezoneString,
		];
	}
}
