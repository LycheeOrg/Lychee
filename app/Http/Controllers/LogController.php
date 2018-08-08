<?php

namespace App\Http\Controllers;

use App\Logs;
//use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LogController extends Controller
{


    function list($safety = false)
    {


        // Ensure that user is logged in
        if (!$safety || (Session::has('login') && Session::get('login')===true &&
            Session::has('identifier') && Session::get('identifier') === Settings::get()['identifier'])) {


            // Output
            if (Logs::count() == 0) {
                return 'Everything looks fine, Lychee has not reported any problems!';
            } else {
               $logs = Logs::all();
               return view('logs.list', ['logs' => $logs]);
            }

        } else {
            // Don't go further if the user is not logged in
            return 'You have to be logged in to see the log.';
        }
    }

    function clear()
    {
        Logs::where('id','>=',0)->delete();
        return 'Log cleared';
    }

}