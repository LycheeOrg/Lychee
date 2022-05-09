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
		} catch (\Throwable) {
			self::$cache = [];
		}

		return self::$cache;
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
		if (!self::$cache) {
			self::get();
		}

		if (!isset(self::$cache[$key])) {
			/*
			 * For some reason the $default is not returned above...
			 */
			Logs::notice(__METHOD__, __LINE__, $key . ' does not exist in config (local) !');

			return $default;
		}

		return self::$cache[$key];
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
			throw new InvalidConfigOption('Could not save configuration', $e);
		} finally {
			// invalidate cache.
			self::$cache = [];
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
