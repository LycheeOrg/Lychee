<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Models;

use App\Enum\ConfigType;
use App\Enum\LicenseType;
use App\Enum\MapProviders;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\InvalidConfigOption;
use App\Exceptions\Internal\LycheeAssertionError;
use App\Exceptions\Internal\QueryBuilderException;
use App\Exceptions\ModelDBException;
use App\Exceptions\UnexpectedException;
use App\Models\Builders\ConfigsBuilder;
use App\Models\Extensions\ConfigsHas;
use App\Models\Extensions\ThrowsConsistentExceptions;
use BackedEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

/**
 * App\Configs.
 *
 * @property int         $id
 * @property string      $key
 * @property string|null $value
 * @property string      $cat
 * @property string      $type_range
 * @property bool        $is_secret
 * @property string      $description
 * @property string      $details
 * @property int         $level
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
class Configs extends Model
{
	use ConfigsHas;
	use ThrowsConsistentExceptions;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int,string>
	 */
	protected $fillable = ['key', 'value', 'cat', 'type_range', 'is_secret', 'description', 'level'];

	/**
	 *  this is a parameter for Laravel to indicate that there is no created_at, updated_at columns.
	 */
	public $timestamps = false;

	/**
	 * We define this as a singleton.
	 *
	 * @var array<string, int|bool|string|null>
	 */
	private static array $cache = [];

	/**
	 * @param $query
	 *
	 * @return ConfigsBuilder
	 */
	public function newEloquentBuilder($query): ConfigsBuilder
	{
		return new ConfigsBuilder($query);
	}

	/**
	 * Sanity check.
	 *
	 * @param string|null $candidateValue
	 * @param string|null $message_template
	 *
	 * @return string
	 */
	public function sanity(?string $candidateValue, ?string $message_template = null): string
	{
		$message = '';
		$val_range = [
			ConfigType::BOOL->value => explode('|', ConfigType::BOOL->value),
			ConfigType::TERNARY->value => explode('|', ConfigType::TERNARY->value),
		];

		$message_template ??= 'Error: Wrong property for ' . $this->key . ', expected %s, got ' . ($candidateValue ?? 'NULL') . '.';
		switch ($this->type_range) {
			case ConfigType::STRING->value:
			case ConfigType::DISABLED->value:
				break;
			case ConfigType::STRING_REQ->value:
				if ($candidateValue === '' || $candidateValue === null) {
					$message = 'Error: ' . $this->key . ' empty or not set';
				}
				break;
			case ConfigType::INT->value:
				// we make sure that we only have digits in the chosen value.
				if (!ctype_digit(strval($candidateValue))) {
					$message = sprintf($message_template, 'positive integer or 0');
				}
				break;
			case ConfigType::POSTIIVE->value:
				if (!ctype_digit(strval($candidateValue)) || intval($candidateValue, 10) === 0) {
					$message = sprintf($message_template, 'strictly positive integer');
				}
				break;
			case ConfigType::BOOL->value:
			case ConfigType::TERNARY->value:
				if (!in_array($candidateValue, $val_range[$this->type_range], true)) { // BOOL or TERNARY
					$message = sprintf($message_template, implode(' or ', $val_range[$this->type_range]));
				}
				break;
			case ConfigType::LICENSE->value:
				if (LicenseType::tryFrom($candidateValue) === null) {
					$message = sprintf($message_template, 'a valid license');
				}
				break;
			case ConfigType::MAP_PROVIDER->value:
				if (MapProviders::tryFrom($candidateValue) === null) {
					$message = sprintf($message_template, 'a valid map provider');
				}
				break;
			default:
				$values = explode('|', $this->type_range);
				if (!in_array($candidateValue, $values, true)) {
					$message = sprintf($message_template, implode(' or ', $values));
				}
				break;
		}

		return $message;
	}

	/**
	 * Cache and return the current settings of this Lychee installation.
	 *
	 * @return array<string, mixed>
	 */
	public static function get(): array
	{
		if (count(self::$cache) > 0) {
			return self::$cache;
		}

		try {
			self::$cache = Configs::query()
				->select(['key', 'value'])
				->pluck('value', 'key')
				->all();
			// @codeCoverageIgnoreStart
		} catch (\Throwable) {
			self::$cache = [];
		}
		// @codeCoverageIgnoreEnd

		return self::$cache;
	}

	/**
	 * The best way to request a value from the config...
	 *
	 * @param string $key
	 *
	 * @return int|bool|string|null
	 *
	 * @throws ConfigurationKeyMissingException if a key does not exist
	 */
	public static function getValue(string $key): int|bool|string|null
	{
		if (count(self::$cache) === 0) {
			self::get();
		}

		if (!array_key_exists($key, self::$cache)) {
			/*
			 * For some reason the $default is not returned above...
			 */
			// @codeCoverageIgnoreStart
			Log::critical(__METHOD__ . ':' . __LINE__ . ' ' . $key . ' does not exist in config (local) !');

			throw new ConfigurationKeyMissingException($key . ' does not exist in config!');
			// @codeCoverageIgnoreEnd
		}

		return self::$cache[$key];
	}

	/**
	 * Get string configuration value.
	 *
	 * @param string $key
	 *
	 * @return string
	 *
	 * @throws ConfigurationKeyMissingException
	 */
	public static function getValueAsString(string $key): string
	{
		return strval(self::getValue($key));
	}

	/**
	 * Get string configuration value.
	 *
	 * @param string $key
	 *
	 * @return int
	 *
	 * @throws ConfigurationKeyMissingException
	 */
	public static function getValueAsInt(string $key): int
	{
		return intval(self::getValue($key));
	}

	/**
	 * Get bool configuration value.
	 *
	 * @param string $key
	 *
	 * @return bool
	 *
	 * @throws ConfigurationKeyMissingException
	 */
	public static function getValueAsBool(string $key): bool
	{
		return self::getValue($key) === '1';
	}

	/**
	 * @template T of BackedEnum
	 *
	 * @param string          $key
	 * @param class-string<T> $type
	 *
	 * @return T|null
	 */
	public static function getValueAsEnum(string $key, string $type): \BackedEnum|null
	{
		if (!function_exists('enum_exists') || !enum_exists($type) || !method_exists($type, 'tryFrom')) {
			// @codeCoverageIgnoreStart
			throw new UnexpectedException();
			// @codeCoverageIgnoreEnd
		}

		return $type::tryFrom(self::getValue($key));
	}

	/**
	 * Update Lychee configuration
	 * Note that we must invalidate the cache now.
	 *
	 * @param string          $key
	 * @param string|int|bool $value
	 *
	 * @return void
	 *
	 * @throws InvalidConfigOption
	 * @throws QueryBuilderException
	 */
	public static function set(string $key, string|int|bool|\BackedEnum $value): void
	{
		try {
			/** @var Configs $config */
			$config = Configs::query()
				->where('key', '=', $key)
				->firstOrFail();

			// For BackEnm we take the value. In theory this is no longer necessary because we enforce at the column type.
			if ($value instanceof \BackedEnum) {
				$value = $value->value;
			}

			$strValue = match (gettype($value)) {
				'boolean' => $value === true ? '1' : '0',
				'integer', 'string' => strval($value),
				// @codeCoverageIgnoreStart
				default => throw new LycheeAssertionError('Unexpected type'),
				// @codeCoverageIgnoreEnd
			};

			/**
			 * Sanity check. :).
			 */
			$message = $config->sanity($strValue);
			if ($message !== '') {
				throw new InvalidConfigOption($message);
			}
			$config->value = $strValue;
			$config->save();
			// @codeCoverageIgnoreStart
		} catch (ModelNotFoundException $e) {
			throw new InvalidConfigOption('key ' . $key . ' not found!', $e);
		} catch (ModelDBException $e) {
			throw new InvalidConfigOption('Could not save configuration', $e);
			// @codeCoverageIgnoreEnd
		} finally {
			// invalidate cache.
			self::$cache = [];
		}
	}

	/**
	 * Reset the cache.
	 */
	public static function invalidateCache(): void
	{
		self::$cache = [];
	}
}
