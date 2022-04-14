<?php

namespace App\Http\Controllers\Administration;

use App\Exceptions\Internal\QueryBuilderException;
use App\Models\Configs;
use App\Models\Logs;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LogController extends Controller
{
	/**
	 * @param string $order
	 *
	 * @return Collection<Logs>
	 *
	 * @throws QueryBuilderException
	 */
	public function list(string $order = 'desc'): Collection
	{
		return Logs::query()
			->orderBy('id', $order)
			->limit(intval(Configs::get_value('log_max_num_line', 1000)))
			->get();
	}

	/**
	 * display the Logs.
	 *
	 * @return View
	 *
	 * @throws BindingResolutionException
	 * @throws QueryBuilderException
	 */
	public function view(): View
	{
		return view('logs.list', ['logs' => $this->list()]);
	}

	/**
	 * Empty the log table.
	 *
	 * @return void
	 */
	public static function clear(): void
	{
		DB::table('logs')->truncate();
	}

	/**
	 * This function does pretty much the same as clear but only does it on notice
	 * and also keeps the log of the log-in attempts.
	 *
	 * @return void
	 *
	 * @throws QueryBuilderException
	 */
	public static function clearNoise(): void
	{
		Logs::query()
			->where('function', '!=', 'App\Http\Controllers\SessionController::login')
			->where('type', '=', 'notice')
			->delete();
	}
}
