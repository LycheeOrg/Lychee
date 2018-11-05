<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Logs extends Model
{
    protected $fillable = ['type','function','line','text'];

    /**
     * @param string $function
     * @param string $line
     * @param string $text
     * @return boolean Returns true when successful.
     */
    public static function notice(string $function, string $line, string $text = '') {
        $log = self::create(['type' => 'notice', 'function' => $function, 'line' => $line, 'text' => $text]);
        $log->save();
        return true;
    }

    /**
     * @param string $function
     * @param string $line
     * @param string $text
     * @return boolean Returns true when successful.
     */
    public static function warning(string $function, string $line, string $text = '') {
        $log = self::create(['type' => 'warning', 'function' => $function, 'line' => $line, 'text' => $text]);
        $log->save();
        return true;
    }

    /**
     * @param string $function
     * @param string $line
     * @param string $text
     * @return boolean Returns true when successful.
     */
    public static function error(string $function, string $line, string $text = '') {
        $log = self::create(['type' => 'error', 'function' => $function, 'line' => $line, 'text' => $text]);
        $log->save();
        return true;
    }

    /**
     * @param string $type
     * @param string $function
     * @param string $line
     * @param string $text
     * @return boolean Returns true when successful.
     */
    private static function text(string $type, string $function, string $line, string $text = '') {
        $log = self::create(['type' => $type, 'function' => $function, 'line' => $line, 'text' => $text]);
        $log->save();
        return true;
    }

}
