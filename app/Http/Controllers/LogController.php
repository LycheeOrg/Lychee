<?php

namespace App\Http\Controllers;

use App\Logs;

class LogController extends Controller
{

    static public function list($order = 'DESC')
    {
        $logs = Logs::orderBy('id', $order)->get();
        return $logs;
    }

    public function display()
    {

        // Output
        if (Logs::count() == 0) {
            return 'Everything looks fine, Lychee has not reported any problems!';
        } else {
            $logs = self::list();
            return view('logs.list', ['logs' => $logs]);
        }
    }

    static public function clear()
    {
        Logs::where('id','>=',0)->delete();
        return 'Log cleared';
    }

}