<?php

namespace App;

use App\Locale\Lang;
use Illuminate\Database\Eloquent\Model;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\String_;

class Configs extends Model
{
    public $timestamps = false;
    private static $cache = null;
    private static $public_cache = null;

    protected static $except = [
        'username',
        'password',
		'dropboxKey',
		'plugins'
    ];

    protected static $clear_field = [
    	'lang_available',
	    'imagick',
	    'skipDuplicates',
	    'sortingAlbums',
	    'sortingAlbums_col',
	    'sortingAlbums_order',
	    'sortingPhotos',
	    'sortingPhotos_col',
	    'sortingPhotos_order',
    ];

    /**
     * @param string $key
     * @param bool $public
     * @return bool
     */
    static protected function inExceptArray(string $key, bool $public)
    {

        if ($public)
        {
            foreach (self::$except as $exception) {
                if ($exception == $key) {
                    return true;
                }
            }
            return false;
        }

        return false;
    }

    /**
     * @param bool $public
     * @return array Returns the upload settings of Lychee.
     */
    public static function get(bool $public = true) {
        if ($public && self::$public_cache) return self::$public_cache;
        if (!$public && self::$cache)       return self::$cache;


        // Execute query
        $configs    = Configs::all();

        $return = array();

        // Add each to return
        foreach ($configs as $config)
            if(!Configs::inExceptArray($config->key, $public))
            {
                $return[$config->key] = $config->value;
            }

//        // Convert plugins to array
//        $return['plugins'] = explode(';', $return['plugins']);
        $return['sortingPhotos'] = 'ORDER BY '.$return['sortingPhotos_col'].' '.$return['sortingPhotos_order'];
        $return['sortingAlbums'] = 'ORDER BY '.$return['sortingAlbums_col'].' '.$return['sortingAlbums_order'];
	    $return['lang_available'] = Lang::get_lang_available();


        if($public) {
//            Logs::notice(__METHOD__, __LINE__, 'cache populated with public parameters');
            self::$public_cache = $return;
        }
        else {
//            Logs::warning(__METHOD__, __LINE__, 'cache populated with dangerous parameters');
            self::$cache = $return;
        }

        return $return;
    }


    /**
     * @param string $key
     * @return mixed
     */
    public static function get_value(string $key)
    {
        if (self::$public_cache)
        {
            if (!isset(self::$public_cache[$key]))
            {
                Logs::error(__METHOD__,__LINE__,$key . ' does not exist in config (local) !');
                return false;
            }
            return self::$public_cache[$key];
        };
        // if public cache does not exist it is possible to access forbidden values here!
        if(Configs::select('value')->where('key','=',$key)->count() == 0)
        {
            Logs::error(__METHOD__,__LINE__,$key . ' does not exist in config !');
            return false;
        }
        return Configs::select('value')->where('key','=',$key)->first()->value;
    }

    /**
     * @param string $key
     * @param $value
     * @return bool Returns true when successful.
     */
    public static function set(string $key, $value) {

            $config = Configs::where('key', '=', $key)->first();
            $config->value = $value;
            if(!$config->save())
            {
                Logs::error(__METHOD__,__LINE__,$config->getErrors());
                return false;
            }
            return true;
    }

    /**
     * @return bool Returns the Imagick setting.
     */
    public static function hasImagick() {
        if((bool)(extension_loaded('imagick') && self::get()['imagick'] == '1')) {
            return true;
        }
        Logs::notice(__METHOD__,__LINE__,"hasImagick : false");
        return false;
    }

	/**
	 * @return array Returns the public settings of Lychee.
	 */
	public static function min_info() {

		// Execute query
		$configs    = Configs::all();

		$return = array();

		// Add each to return
		foreach ($configs as $config)
		{
			$found = false;
			foreach (self::$except as $exception) {
				if ($exception == $config->key) {
					$found = true;
				}
			}
			foreach (self::$clear_field as $exception) {
				if ($exception == $config->key) {
					$found = true;
				}
			}
			if (!$found)
			{
				$return[$config->key] = $config->value;
			}
		}
		return $return;
	}

}
