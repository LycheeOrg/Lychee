<?php

namespace App\Http\Controllers;

use App\Logs;
use Illuminate\Support\Facades\DB;

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
		}
		else {
			$logs = self::list();
			return view('logs.list', ['logs' => $logs]);
		}
	}



	static public function clear()
	{
		DB::table('logs')->truncate();
		return 'Log cleared';
	}



	static public function clearNoise()
	{
		Logs::where('function', '=', 'App\Photo::createMedium')->delete();
		Logs::where('function', '=', 'App\Photo::createThumb')->delete();
		Logs::where('function', '=', 'App\Configs::get_value')->delete();
		Logs::where('function', '=', 'App\Configs::hasImagick')->delete();
		Logs::where('function', '=', 'App\Http\Controllers\AlbumController::get')->delete();
		Logs::where('function', '=', 'App\Http\Controllers\AlbumController::move')->delete();
		Logs::where('function', '=', 'App\Http\Controllers\AlbumController::merge')->delete();
		Logs::where('function', '=', 'App\Http\Controllers\ImportController::server_exec')->delete();
		Logs::where('function', '=', 'App\Http\Controllers\PhotoController::add')->delete();
		Logs::where('function', '=', 'App\Http\Controllers\SettingsController::setCSS')->delete();
		Logs::where('function', '=', 'App\Http\Controllers\ViewController::view')->delete();
		Logs::where('function', '=', 'App\ModelFunctions\PhotoFunctions::add')->delete();
		Logs::where('function', '=', 'App\ModelFunctions\PhotoFunctions::createMedium')->delete();
		Logs::where('function', '=', 'App\ModelFunctions\PhotoFunctions::createThumb')->delete();

		return 'Log Noise cleared';
	}

}