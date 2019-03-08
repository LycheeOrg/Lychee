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

	public static function arrayify($query)
	{
		$configs = $query->get();

		$return = array();

		// Add each to return
		foreach ($configs as $config) {
			$return[$config->key] = $config->value;
		}

		return $return;
	}



	/**
	 * @return array Returns the upload settings of Lychee.
	 */
	public static function get()
	{
		if (self::$cache) {
			return self::$cache;
		}

		$query = Configs::select('key', 'value');
		$return = Configs::arrayify($query);

		$return['sortingPhotos'] = 'ORDER BY '.$return['sortingPhotos_col'].' '.$return['sortingPhotos_order'];
		$return['sortingAlbums'] = 'ORDER BY '.$return['sortingAlbums_col'].' '.$return['sortingAlbums_order'];

		$return['lang_available'] = Lang::get_lang_available();

		self::$cache = $return;

		return $return;
	}



	/**
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public static function get_value(string $key, $default = null)
	{
		if (self::$cache) {
			if (!isset(self::$cache[$key])) {
				Logs::error(__METHOD__, __LINE__, $key.' does not exist in config (local) !');
				return false;
			}
			return self::$cache[$key];
		};

		try {
			// if public cache does not exist it is possible to access forbidden values here!
			if (Configs::select('value')->where('key', '=', $key)->count() == 0) {
				Logs::error(__METHOD__, __LINE__, $key.' does not exist in config !');
				return false;
			}
			return Configs::select('value')->where('key', '=', $key)->first()->value;
		}
		catch (QueryException $exception) {
			return $default;
		}
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
		Logs::notice(__METHOD__, __LINE__, "hasImagick : false");
		return false;
	}



	public function scopePublic($query)
	{
		return $query->where('confidentiality', '=', 0);
	}



	public function scopeMin_info($query)
	{
		return $query->where('confidentiality', '<=', 1);
	}



	public function scopeAdmin($query)
	{
		return $query->where('confidentiality', '<=', 2);
	}
}
