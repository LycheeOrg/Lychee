<?php

namespace App\Models;

use App\Exceptions\Internal\InvalidConfigOption;
use App\Exceptions\Internal\QueryBuilderException;
use App\Exceptions\ModelDBException;
use App\Facades\Helpers;
use App\Models\Extensions\ConfigsHas;
use App\Models\Extensions\FixedQueryBuilder;
use App\Models\Extensions\ThrowsConsistentExceptions;
use App\Models\Extensions\UseFixedQueryBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * App\Configs.
 *
 * @property int         $id
 * @property string      $key
 * @property string|null $value
 * @property string      $cat
 * @property string      $type_range
 * @property int         $confidentiality
 * @property string      $description
 *
 * @method static FixedQueryBuilder info()   Starts to query the model with results scoped to confidentiality level "info".
 * @method static FixedQueryBuilder public() Starts to query the model with results scoped to confidentiality level "public".
 * @method static FixedQueryBuilder admin()  Starts to query the model with results scoped to confidentiality level "admin".
 */
class Configs extends Model
{
	use ConfigsHas;
	use ThrowsConsistentExceptions;
	use UseFixedQueryBuilder;

	protected const INT = 'int';
	protected const SIGNED_INT = 'signed_int';
	protected const STRING = 'string';
	protected const STRING_REQ = 'string_required';
	protected const BOOL = '0|1';
	protected const TERNARY = '0|1|2';
	protected const DISABLED = '';
	protected const LICENSE = 'license';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['key', 'value', 'cat', 'type_range', 'confidentiality', 'description'];

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
	 * We define this as a singleton.
	 *
	 * @var array<string, string>
	 */
	private static array $type_range_cache = [];

	/**
	 * Sanity check.
	 *
	 * @param $value
	 *
	 * @return string
	 */
	public function sanity($value): string
	{
		$message = '';
		$val_range = [
			self::BOOL => explode('|', self::BOOL),
			self::TERNARY => explode('|', self::TERNARY),
		];

		switch ($this->type_range) {
						case self::STRING:
						case self::DISABLED:
								break;
						case self::STRING_REQ:
								if ($value == '') {
									$message = 'Error: ' . $this->key . ' empty or not set in database';
								}
								break;
						case self::INT:
								// we make sure that we only have digits in the chosen value.
								if (!ctype_digit(strval($value))) {
									$message = 'Error: Wrong property for ' . $this->key . ' in database, expected positive integer.';
								}
								break;
						case self::SIGNED_INT:
								// we make sure that we only have digits and - in the chosen value.
								if (!ctype_digit(strval(str_replace('-', '', $value)))) {
									$message = 'Error: Wrong property for ' . $this->key . ' in database, expected positive or negative integer.';
								}
								break;
						case self::BOOL:
						case self::TERNARY:
								if (!in_array($value, $val_range[$this->type_range])) { // BOOL or TERNARY
									$message = 'Error: Wrong property for ' . $this->key
												. ' in database, expected ' . implode(
														' or ',
														$val_range[$this->type_range]
												) . ', got ' . ($value ?: 'NULL');
								}
								break;
						case self::LICENSE:
								if (!in_array($value, Helpers::get_all_licenses())) {
									$message = 'Error: Wrong property for ' . $this->key
												. ' in database, expected a valid license, got ' . ($value ?: 'NULL');
								}
								break;
						default:
								$values = explode('|', $this->type_range);
								if (!in_array($value, $values)) {
									$message = 'Error: Wrong property for ' . $this->key
												. ' in database, expected ' . implode(' or ', $values)
												. ', got ' . ($value ?: 'NULL');
								}
								break;
				}

		return $message;
	}

	/**
	 * Set the type of a config value.
	 *
	 * @param $value
	 *
	 * @return string|int|bool
	 */
	public static function set_type_of($value, $type_range): string|int|bool
	{
		try {
			switch ($type_range) {
								case self::INT:
								case self::SIGNED_INT:
								case self::TERNARY:
										if (!is_numeric($value)) {
											throw new InvalidConfigOption($value . ' is not an integer');
										}

										return intval($value);
								case self::BOOL:
										return boolval($value);
								case self::STRING:
								case self::DISABLED:
								case self::STRING_REQ:
								case self::LICENSE:
								default:
										return $value;
						}
		} catch (\Throwable $e) {
			Logs::notice(__METHOD__, __LINE__, 'The value ' . $value . ' does not match its type_range (' . $type_range . ')');

			return $value;
		}
	}

	/**
	 * Cache and return the current settings of this Lychee installation.
	 *
	 * @return array<string, mixed>
	 */
	public static function get(): array
	{
		if (self::$cache) {
			return self::$cache;
		}

		try {
			self::$cache = Configs::query()
								->select(['key', 'value'])
								->pluck('value', 'key')
								->all();
		}
		// fails only if the datavase is corrupt
		// @codeCoverageIgnoreStart
		catch (\Throwable) {
			self::$cache = [];
		}
		// @codeCoverageIgnoreEnd

		return self::$cache;
	}

	/**
	 * Cache and return the current type_range for the settings.
	 *
	 * @return array<string, mixed>
	 */
	protected static function get_type_range(): array
	{
		if (self::$type_range_cache) {
			return self::$type_range_cache;
		}

		try {
			self::$type_range_cache = Configs::query()
								->select(['key', 'type_range'])
								->pluck('type_range', 'key')
								->all();
		}
		// fails only if the datavase is corrupt
		// @codeCoverageIgnoreStart
		catch (\Throwable) {
			self::$type_range_cache = [];
		}
		// @codeCoverageIgnoreEnd

		return self::$type_range_cache;
	}

	/**
	 * The best way to request a value from the config...
	 *
	 * @param string               $key
	 * @param int|bool|string|null $default
	 *
	 * @return int|bool|string|null
	 */
	public static function get_value(string $key, int|bool|string|null $default = null): int|bool|string|null
	{
		self::get();

		if (!isset(self::$cache[$key])) {
			/*
			 * For some reason the $default is not returned above...
			 */
			Logs::notice(__METHOD__, __LINE__, $key . ' does not exist in config (local) !');

			return $default;
		}

		self::get_type_range();

		if (!isset(self::$type_range_cache[$key])) {
			// happens only if the datavase is corrupt
			// @codeCoverageIgnoreStart
			$type_range = self::STRING;
		// @codeCoverageIgnoreEnd
		} else {
			$type_range = self::$type_range_cache[$key];
		}

		return self::set_type_of(self::$cache[$key], $type_range);
	}

	/**
	 * Update Lychee configuration
	 * Note that we must invalidate the cache now.
	 *
	 * @param string $key
	 * @param $value
	 *
	 * @return void
	 *
	 * @throws InvalidConfigOption
	 * @throws QueryBuilderException
	 */
	public static function set(string $key, $value): void
	{
		try {
			/** @var Configs $config */
			$config = Configs::query()
								->where('key', '=', $key)
								->firstOrFail();

			/**
			 * Sanity check. :).
			 */
			$message = $config->sanity($value);
			if ($message != '') {
				throw new InvalidConfigOption($message);
			}
			$config->value = $value;
			$config->save();
		} catch (ModelNotFoundException $e) {
			throw new InvalidConfigOption('key ' . $key . ' not found!', $e);
		} catch (ModelDBException $e) {
			// fails only if the datavase is corrupt
			// @codeCoverageIgnoreStart
			throw new InvalidConfigOption('Could not save configuration', $e);
			// @codeCoverageIgnoreEnd
		} finally {
			// invalidate cache.
			self::$cache = [];
		}
	}

	/**
	 * Get a config value as cron specification for the scheduler.
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	public static function get_value_as_cron_spec(string $key, int $default = 0): string
	{
		$value = self::get_value($key, $default);
		if ($value > 0) {
			if ($value < 60) {
				$minute = sprintf('*/%s', $value);
				$hour = '*';
			} elseif ($value < 24 * 60) {
				$minute = $value % 60;
				$hour = sprintf('*/%s', intdiv($value, 60));
			} else {
				$minute = $value % 60;
				$hour = intdiv($value, 60) % 24;
			}

			return sprintf('%s %s * * *', $minute, $hour);
		} else {
			return '';
		}
	}

	/**
	 * Define scopes.
	 */

	/**
	 * @param FixedQueryBuilder $query
	 *
	 * @return FixedQueryBuilder
	 *
	 * @throws QueryBuilderException
	 */
	public function scopePublic(FixedQueryBuilder $query): FixedQueryBuilder
	{
		return $query->where('confidentiality', '=', 0);
	}

	/**
	 * Logged user can see.
	 *
	 * @param FixedQueryBuilder $query
	 *
	 * @return FixedQueryBuilder
	 *
	 * @throws QueryBuilderException
	 */
	public function scopeInfo(FixedQueryBuilder $query): FixedQueryBuilder
	{
		return $query->where('confidentiality', '<=', 2);
	}

	/**
	 * Only admin can see.
	 *
	 * @param FixedQueryBuilder $query
	 *
	 * @return FixedQueryBuilder
	 *
	 * @throws QueryBuilderException
	 */
	public function scopeAdmin(FixedQueryBuilder $query): FixedQueryBuilder
	{
		return $query->where('confidentiality', '<=', 3);
	}
}
