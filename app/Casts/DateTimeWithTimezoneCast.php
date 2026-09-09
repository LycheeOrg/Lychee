<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Casts;

use App\Contracts\Models\HasUTCBasedTimes;
use App\Exceptions\Internal\LycheeDomainException;
use App\Exceptions\Internal\LycheeInvalidArgumentException;
use App\Exceptions\Internal\LycheeLogicException;
use App\Exceptions\Internal\MissingModelAttributeException;
use Carbon\Exceptions\InvalidFormatException;
use Carbon\Exceptions\InvalidTimeZoneException;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Database\Eloquent\ComparesCastableAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @implements CastsAttributes<Carbon,Carbon>
 */
class DateTimeWithTimezoneCast implements CastsAttributes, ComparesCastableAttributes
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
	 * @throws LycheeLogicException
	 * @throws InvalidFormatException
	 * @throws InvalidTimeZoneException
	 */
	public function get(Model $model, string $key, $value, array $attributes): ?Carbon
	{
		// Validate that the model implements the required functions.
		if (!$model instanceof HasUTCBasedTimes) {
			throw new LycheeLogicException('Model must implement HasUTCBasedTimes');
		}

		$tz_key = $key . self::TZ_ATTRIBUTE_SUFFIX;
		if ($value === null) {
			return null;
		}
		if (!is_string($value)) {
			throw new LycheeInvalidArgumentException('$value must be an SQL datetime string');
		}
		if (array_key_exists($tz_key, $attributes)) {
			$tz = $attributes[$tz_key];
		} else {
			throw new MissingModelAttributeException(get_class($model), $tz_key);
		}
		// If the datetime value is non-null, then the accompanying timezone
		// must not be null neither.
		if (!is_string($tz) || $tz === '') {
			throw new LycheeDomainException('Column \'' . $key . '\' is not null, but column \'' . $tz_key . '\' is either not a string, an empty string or null');
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
		$sql_datetime_string = $model->fromDateTime($value);
		$sql_timezone_string = $value?->getTimezone()->getName();
		$tz_key = $key . self::TZ_ATTRIBUTE_SUFFIX;

		return [
			$key => $sql_datetime_string,
			$tz_key => $sql_timezone_string,
		];
	}

	/**
	 * Determines whether two *raw* (not-yet-cast) attribute values represent
	 * the same instant, for Eloquent's dirty-checking (`wasChanged()`/`isDirty()`).
	 *
	 * Without this method, `HasAttributes::originalIsEquivalent()` falls
	 * through to its final, generic fallback for any class cast that doesn't
	 * implement {@see ComparesCastableAttributes}: it requires the two raw
	 * SQL datetime strings to be numeric (they never are) or byte-identical
	 * — otherwise the attribute is reported as "changed" even when the two
	 * values are the exact same instant. That byte-identity assumption holds
	 * on SQLite (its `TEXT` storage returns exactly the string that was
	 * written) but not on PostgreSQL, whose native `timestamp`/`timestamptz`
	 * columns re-serialize the value on read in Postgres's own canonical
	 * format — which does not always match what {@see self::set()} wrote —
	 * so an entirely unchanged reassignment (e.g. `$photo->taken_at =
	 * $photo->initial_taken_at` when the two were already equal) was
	 * spuriously detected as a change on PostgreSQL only. Comparing the
	 * parsed instants instead of the raw strings fixes this regardless of
	 * which DB engine's serialization quirk would otherwise trigger it.
	 *
	 * @param Model  $model
	 * @param string $key
	 * @param mixed  $first_value
	 * @param mixed  $second_value
	 *
	 * @return bool
	 */
	public function compare(Model $model, string $key, mixed $first_value, mixed $second_value): bool
	{
		if ($first_value === $second_value) {
			return true;
		}
		if ($first_value === null || $second_value === null) {
			return false;
		}
		if (!is_string($first_value) || !is_string($second_value)) {
			return false;
		}
		// Same precondition as `get()` — `asDateTime()` is only public on a
		// model using the `HasUTCBasedTimes` trait; on the base `Model` type
		// this parameter is declared as, it is protected.
		if (!$model instanceof HasUTCBasedTimes) {
			throw new LycheeLogicException('Model must implement HasUTCBasedTimes');
		}

		return $model->asDateTime($first_value)->eq($model->asDateTime($second_value));
	}
}