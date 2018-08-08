<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Logs extends Model
{
    protected $fillable = ['type','function','line','text'];

    /**
     * @return boolean Returns true when successful.
     */
    public static function notice($function, $line, $text = '') {
        $log = self::create(['type' => 'notice', 'function' => $function, 'line' => $line, 'text' => $text]);
        $log->save();
        return true;
    }

    /**
     * @return boolean Returns true when successful.
     */
    public static function warning($function, $line, $text = '') {
        $log = self::create(['type' => 'warning', 'function' => $function, 'line' => $line, 'text' => $text]);
        $log->save();
        return true;
    }

    /**
     * @return boolean Returns true when successful.
     */
    public static function error($function, $line, $text = '') {
        $log = self::create(['type' => 'error', 'function' => $function, 'line' => $line, 'text' => $text]);
        $log->save();
        return true;
    }

    /**
     * @return boolean Returns true when successful.
     */
    private static function text($type, $function, $line, $text = '') {
        $log = self::create(['type' => $type, 'function' => $function, 'line' => $line, 'text' => $text]);
        $log->save();
        return true;
    }

}
