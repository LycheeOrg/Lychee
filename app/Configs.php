<?php

namespace App;

use App\Locale\Lang;
use Illuminate\Database\Eloquent\Model;

class Configs extends Model
{
    public $timestamps = false;
    private static $cache = null;
    private static $public_cache = null;

    protected static $except = [
        'username',
        'password',
        'identifier',
		'dropboxKey',
//		'imagick',
//		'plugins'
    ];




    static protected function inExceptArray($key)
    {
        foreach (self::$except as $exception) {
            if ($exception == $key) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array Returns the public settings of Lychee.
     */
    public static function get($public = true) {
        if ($public && self::$public_cache) return self::$public_cache;
        if (!$public && self::$cache)       return self::$cache;


        // Execute query
        $configs    = Configs::all();

        $return = array();

        // Add each to return
        foreach ($configs as $config)
            if(!$public || !Configs::inExceptArray($config->key))
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


    public static function get_value($key)
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
     * @return boolean Returns true when successful.
     */
    public static function set($key, $value) {

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
     * @return boolean Returns the Imagick setting.
     */
    public static function hasImagick() {
        if((bool)(extension_loaded('imagick') && self::get()['imagick'] == '1')) {
            return true;
        }
        Logs::notice(__METHOD__,__LINE__,"hasImagick : false");
        return false;
    }

}
