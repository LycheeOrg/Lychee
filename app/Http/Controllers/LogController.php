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
		Logs::where('function', '=', 'App\Photo::createMedium')->
		orWhere('function', '=', 'App\Photo::createThumb')->
		orWhere('function', '=', 'App\Configs::get_value')->
		orWhere('function', '=', 'App\Configs::hasImagick')->
		orWhere('function', '=', 'App\Http\Controllers\AlbumController::get')->
		orWhere('function', '=', 'App\Http\Controllers\AlbumController::move')->
		orWhere('function', '=', 'App\Http\Controllers\AlbumController::merge')->
		orWhere('function', '=', 'App\Http\Controllers\ImportController::server_exec')->
		orWhere('function', '=', 'App\Http\Controllers\PhotoController::add')->
		orWhere('function', '=', 'App\Http\Controllers\SettingsController::setCSS')->
		orWhere('function', '=', 'App\Http\Controllers\ViewController::view')->
		orWhere('function', '=', 'App\ModelFunctions\PhotoFunctions::add')->
		orWhere('function', '=', 'App\ModelFunctions\PhotoFunctions::createMedium')->
		orWhere('function', '=', 'App\ModelFunctions\PhotoFunctions::createThumb')->
		orWhere('function', '=', 'App\ModelFunctions\PhotoFunctions::resizePhoto')->
		orWhere('function', '=', 'App\Photo::predelete')->
		orWhere('function', '=', 'App\Image\ImagickHandler::scale')->
		orWhere('function', '=', 'App\Image\ImagickHandler::crop')->
		orWhere('function', '=', 'App\ModelFunctions\PhotoFunctions::save')->delete();

		return 'Log Noise cleared';
	}

}
