<?php

namespace App;

use App\Locale\Lang;
use Eloquent;
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
 * @mixin Eloquent
 */
class Configs extends Model
{
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
						. ' in database, expected ' . implode(' or ',
							$val_range[$this->type_range]) . ', got ' . ($value ? $value : 'NULL');
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

			$return['lang_available'] = Lang::get_lang_available();

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
				Logs::error(__METHOD__, __LINE__, $key . ' does not exist in config (local) !');
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
			Logs::warning(__FUNCTION__, __LINE__, 'key ' . $key . ' not found!');

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
	 * @return bool returns the Imagick setting
	 */
	public static function hasImagick()
	{
		if ((bool) (extension_loaded('imagick') && self::get_value('imagick', '1') == '1')) {
			return true;
		}
		try {
			Logs::notice(__METHOD__, __LINE__, 'hasImagick : false');
		} catch (Exception $e) {
			//do nothing
		}

		return false;
	}

	/**
	 * @return bool returns the Exiftool setting
	 */
	public static function hasExiftool()
	{
		// has_exiftool has the following values:
		// 0: No Exiftool
		// 1: Exiftool is available
		// 2: Not yet tested if exiftool is available

		$has_exiftool = self::get_value('has_exiftool');

		// value not yet set -> let's see if exiftool is available
		if ($has_exiftool == 2) {
			$status = 0;
			$output = '';
			exec('which exiftool 2>&1 > /dev/null', $output, $status);
			if ($status != 0) {
				self::set('has_exiftool', 0);
				$has_exiftool = false;
			} else {
				self::set('has_exiftool', 1);
				$has_exiftool = true;
			}
		} elseif ($has_exiftool == 1) {
			$has_exiftool = true;
		} else {
			$has_exiftool = false;
		}

		return $has_exiftool;
	}

	/**
	 * @return bool returns the Exiftool setting
	 */
	public static function hasFFmpeg()
	{
		// has_ffmpeg has the following values:
		// 0: No FFmpeg
		// 1: FFmpeg is available
		// 2: Not yet tested if FFmpeg is available

		$has_ffmpeg = self::get_value('has_ffmpeg');

		// value not yet set -> let's see if FFmpeg is available
		if ($has_ffmpeg == 2) {
			$status = 0;
			$output = '';
			exec('which FFmpeg 2>&1 > /dev/null', $output, $status);
			if ($status != 0) {
				self::set('has_ffmpeg', 0);
				$has_ffmpeg = false;
			} else {
				self::set('has_ffmpeg', 1);
				$has_ffmpeg = true;
			}
		} elseif ($has_ffmpeg == 1) {
			$has_ffmpeg = true;
		} else {
			$has_ffmpeg = false;
		}

		return $has_ffmpeg;
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
