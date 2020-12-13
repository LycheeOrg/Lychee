<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use App\Models\Configs;
use App\Models\Logs;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LogController extends Controller
{
	/**
	 * @param string $order
	 *
	 * @return mixed
	 */
	public function list($order = 'DESC')
	{
		return Logs::orderBy('id', $order)->limit(intval(Configs::get_value('log_max_num_line', 1000)))->get();
	}

	/**
	 * display the Logs.
	 *
	 * @return View|string
	 */
	public function display()
	{
		if (Logs::count() == 0) {
			return 'Everything looks fine, Lychee has not reported any problems!';
		} else {
			$logs = $this->list();

			return view('logs.list', ['logs' => $logs]);
		}
	}

	/**
	 * Empty the log table.
	 *
	 * @return string
	 */
	public static function clear()
	{
		DB::table('logs')->truncate();

		return 'Log cleared';
	}

	/**
	 * This function does pretty much the same as clear but only does it on notice
	 * and also keeps the log of the loggin attempts.
	 *
	 * @return string
	 */
	public static function clearNoise()
	{
		Logs::where('function', '!=', 'App\Http\Controllers\SessionController::login')->where('type', '=', 'notice')->delete();

		return 'Log Noise cleared';
	}
}
