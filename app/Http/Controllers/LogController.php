<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers;

use App\Logs;
use Illuminate\Support\Facades\DB;

class LogController extends Controller
{
    /**
     * @param string $order
     *
     * @return mixed
     */
    public function list($order = 'DESC')
    {
        $logs = Logs::orderBy('id', $order)->get();

        return $logs;
    }

    public function display()
    {
        if (Logs::count() == 0) {
            return 'Everything looks fine, Lychee has not reported any problems!';
        } else {
            $logs = $this->list();

            return view('logs.list', ['logs' => $logs]);
        }
    }

    public static function clear()
    {
        DB::table('logs')->truncate();

        return 'Log cleared';
    }

    public static function clearNoise()
    {
        Logs::where('function', '!=', 'App\Http\Controllers\SessionController::login')->
			where('type', '=', 'notice')->delete();

        return 'Log Noise cleared';
    }
}
