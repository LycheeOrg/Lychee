<?php

namespace App\Models;

use App\Facades\Helpers;
use App\Models\Extensions\ConfigsHas;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

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
 * @method static Builder|Configs admin()
 * @method static Builder|Configs info()
 * @method static Builder|Configs newModelQuery()
 * @method static Builder|Configs newQuery()
 * @method static Builder|Configs public ()
 * @method static Builder|Configs query()
 * @method static Builder|Configs whereCat($value)
 * @method static Builder|Configs whereConfidentiality($value)
 * @method static Builder|Configs whereId($value)
 * @method static Builder|Configs whereKey($value)
 * @method static Builder|Configs whereValue($value)
 */
class Configs extends Model
{
	use ConfigsHas;

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

	/** We define this as a singleton */
	private static $cache = null;

	/**
	 * Sanity check.
	 *
	 * @param $value
	 *
	 * @return string
	 */
	public function sanity($value)
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
						) . ', got ' . ($value ? $value : 'NULL');
				}
				break;
			case LICENSE:
				if (!in_array($value, Helpers::get_all_licenses())) {
					$message = 'Error: Wrong property for ' . $this->key
						. ' in database, expected a valide license, got ' . ($value ? $value : 'NULL');
				}
				break;
			default:
				$values = explode('|', $this->type_range);
				if (!in_array($value, $values)) {
					$message = 'Error: Wrong property for ' . $this->key
						. ' in database, expected ' . implode(' or ', $values)
						. ', got ' . ($value ? $value : 'NULL');
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
	public static function get()
	{
		if (self::$cache) {
			return self::$cache;
		}

		try {
			$query = Configs::select([
				'key',
				'value',
			]);
			$return = $query->pluck('value', 'key')->all();

			$return['sorting_Photos'] = 'ORDER BY ' . $return['sorting_Photos_col'] . ' ' . $return['sorting_Photos_order'];
			$return['sorting_Albums'] = 'ORDER BY ' . $return['sorting_Albums_col'] . ' ' . $return['sorting_Albums_order'];

			self::$cache = $return;
		} catch (Exception $e) {
			self::$cache = null;

			return null;
		}

		return $return;
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
			/*
			 * try is here because when composer does the package discovery it
			 * looks at AppServiceProvider which register a singleton with:
			 * $compressionQuality = Configs::get_value('compression_quality', 90);
			 *
			 * this will fail for sure as the config table does not exist yet
			 */
			try {
				self::get();
			} catch (QueryException $e) {
				return $default;
			}
		}

		if (!isset(self::$cache[$key])) {
			/*
			 * For some reason the $default is not returned above...
			 */
			try {
				Logs::notice(__METHOD__, __LINE__, $key . ' does not exist in config (local) !');
			} catch (Exception $e) {
				// yeah we do nothing because we cannot do anything in that case ...  :p
			}

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
	 * @return bool returns true when successful
	 */
	public static function set(string $key, $value)
	{
		$config = Configs::where('key', '=', $key)->first();

		// first() may return null, fixup 'Creating default object from empty value' error
		// we also log a warning
		if ($config == null) {
			Logs::warning(__METHOD__, __LINE__, 'key ' . $key . ' not found!');

			return true;
		}

		/**
		 * Sanity check. :).
		 */
		$message = $config->sanity($value);
		if ($message != '') {
			Logs::error(__METHOD__, __LINE__, $message);

			return false;
		}

		$config->value = $value;

		try {
			$config->save();
		} catch (Exception $e) {
			Logs::error(__METHOD__, __LINE__, $e->getMessage());

			return false;
		}

		// invalidate cache.
		self::$cache = null;

		return true;
	}

	/**
	 * Define scopes.
	 */

	/**
	 * @param $query
	 *
	 * @return mixed
	 */
	public function scopePublic(Builder $query)
	{
		return $query->where('confidentiality', '=', 0);
	}

	/**
	 * Logged user can see.
	 *
	 * @param $query
	 *
	 * @return mixed
	 */
	public function scopeInfo(Builder $query)
	{
		return $query->where('confidentiality', '<=', 2);
	}

	/**
	 * Only admin can see.
	 *
	 * @param $query
	 *
	 * @return mixed
	 */
	public function scopeAdmin(Builder $query)
	{
		return $query->where('confidentiality', '<=', 3);
	}
}
