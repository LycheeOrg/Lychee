<?php

namespace App;

use App\Locale\Lang;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

class Configs extends Model
{
	/**
	 *  this is a parameter for Laravel to indicate that there is no created_at, updated_at columns.
	 */
	public $timestamps = false;


	private static $cache = null;



	/**
	 * @return array Returns the upload settings of Lychee.
	 */
	public static function get()
	{
		if (self::$cache) {
			return self::$cache;
		}

		try {
			$query = Configs::select('key', 'value');
			$return = $query->pluck('value', 'key')->all();

			$return['sortingPhotos'] = 'ORDER BY '.$return['sortingPhotos_col'].' '.$return['sortingPhotos_order'];
			$return['sortingAlbums'] = 'ORDER BY '.$return['sortingAlbums_col'].' '.$return['sortingAlbums_order'];

			$return['lang_available'] = Lang::get_lang_available();

			self::$cache = $return;
		}
		catch (\Exception $e) {
			self::$cache = null;

			return null;
		}

		return $return;
	}



	/**
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public static function get_value(string $key, $default = null)
	{
		if (!self::$cache) {
			/**
			 * try is here because when composer does the package discovery it
			 * looks at AppServiceProvider which register a singleton with:
			 * $compressionQuality = Configs::get_value('compression_quality', 90);
			 *
			 * this will fail for sure as the config table does not exist yet
			 */
			try {
				self::get();
			}
			catch (QueryException $e) {
				return $default;
			}

		}

		if (!isset(self::$cache[$key])) {
			/**
			 * For some reason the $default is not returned above...
			 */
			try {
				Logs::error(__METHOD__, __LINE__, $key.' does not exist in config (local) !');
			}
			catch (\Exception $e) {
				// yeah we do nothing because we cannot do anything in that case ...  :p
			}
			return $default;
		}

		return self::$cache[$key];
	}



	/**
	 * @param string $key
	 * @param $value
	 * @return bool Returns true when successful.
	 */
	public static function set(string $key, $value)
	{

		$config = Configs::where('key', '=', $key)->first();
		$config->value = $value;
		if (!$config->save()) {
			Logs::error(__METHOD__, __LINE__, $config->getErrors());
			return false;
		}

		// invalidate cache.
		self::$cache = null;

		return true;
	}



	/**
	 * @return bool Returns the Imagick setting.
	 */
	public static function hasImagick()
	{
		if ((bool) (extension_loaded('imagick') && self::get_value('imagick', '1') == '1')) {
			return true;
		}
		//this call will cause composer install failure (artisan package:discover will fail, because no DB config now)
		//Logs::notice(__METHOD__, __LINE__, "hasImagick : false");
		return false;
	}



	public function scopePublic($query)
	{
		return $query->where('confidentiality', '=', 0);
	}



	public function scopeInfo($query)
	{
		return $query->where('confidentiality', '<=', 2);
	}



	public function scopeAdmin($query)
	{
		return $query->where('confidentiality', '<=', 3);
	}
}
