<?php

namespace App\Models;

use App\Exceptions\Internal\InvalidConfigOption;
use App\Exceptions\ModelDBException;
use App\Facades\Helpers;
use App\Models\Extensions\ConfigsHas;
use App\Models\Extensions\ThrowsConsistentExceptions;
use Illuminate\Database\Eloquent\Builder;
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
 * @method static Builder admin()
 * @method static Builder info()
 * @method static Builder public ()
 * @method static Builder whereCat($value)
 * @method static Builder whereConfidentiality($value)
 * @method static Builder whereId($value)
 * @method static Builder whereKey($value)
 * @method static Builder whereValue($value)
 */
class Configs extends Model
{
	use ConfigsHas;
	use ThrowsConsistentExceptions;

	const FRIENDLY_MODEL_NAME = 'config';

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
	 * @var array<Configs>
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
		if (!defined('INT')) {
			define('INT', 'int');
			define('STRING', 'string');
			define('STRING_REQ', 'string_required');
			define('BOOL', '0|1');
			define('TERNARY', '0|1|2');
			define('DISABLED', '');
			define('LICENSE', 'license');
		}

		$message = '';
		$val_range = [BOOL => explode('|', BOOL), TERNARY => explode('|', TERNARY)];

		switch ($this->type_range) {
			case STRING:
			case DISABLED:
				break;
			case STRING_REQ:
				if ($value == '') {
					$message = 'Error: ' . $this->key . ' empty or not set in database';
				}
				break;
			case INT:
				// we make sure that we only have digits in the chosen value.
				if (!ctype_digit(strval($value))) {
					$message = 'Error: Wrong property for ' . $this->key . ' in database, expected positive integer.';
				}
				break;
			case BOOL:
			case TERNARY:
				if (!in_array($value, $val_range[$this->type_range])) { // BOOL or TERNARY
					$message = 'Error: Wrong property for ' . $this->key
						. ' in database, expected ' . implode(
							' or ',
							$val_range[$this->type_range]
						) . ', got ' . ($value ?: 'NULL');
				}
				break;
			case LICENSE:
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
	 * @return array
	 */
	public static function get(): array
	{
		if (self::$cache) {
			return self::$cache;
		}

		try {
			$query = Configs::query()->select([
				'key',
				'value',
			]);
			$return = $query->pluck('value', 'key')->all();

			$return['sorting_Photos'] = 'ORDER BY ' . $return['sorting_Photos_col'] . ' ' . $return['sorting_Photos_order'];
			$return['sorting_Albums'] = 'ORDER BY ' . $return['sorting_Albums_col'] . ' ' . $return['sorting_Albums_order'];

			self::$cache = $return;
		} catch (\Exception $e) {
			self::$cache = [];
		}

		return self::$cache;
	}

	/**
	 * The best way to request a value from the config...
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return int|bool|string
	 */
	public static function get_value(string $key, $default = null)
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
				Logs::error(__METHOD__, __LINE__, $message);
				throw new InvalidConfigOption($message);
			}
			$config->value = $value;
			$config->save();
		} catch (ModelNotFoundException $e) {
			$msg = 'key ' . $key . ' not found!';
			Logs::warning(__METHOD__, __LINE__, $msg);
			throw new InvalidConfigOption($msg, $e);
		} catch (ModelDBException $e) {
			Logs::error(__METHOD__, __LINE__, $e->getMessage());
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
	 * @param Builder $query
	 *
	 * @return Builder
	 */
	public function scopePublic(Builder $query): Builder
	{
		return $query->where('confidentiality', '=', 0);
	}

	/**
	 * Logged user can see.
	 *
	 * @param Builder $query
	 *
	 * @return Builder
	 */
	public function scopeInfo(Builder $query): Builder
	{
		return $query->where('confidentiality', '<=', 2);
	}

	/**
	 * Only admin can see.
	 *
	 * @param Builder $query
	 *
	 * @return Builder
	 */
	public function scopeAdmin(Builder $query): Builder
	{
		return $query->where('confidentiality', '<=', 3);
	}

	protected function friendlyModelName(): string
	{
		return self::FRIENDLY_MODEL_NAME;
	}
}
