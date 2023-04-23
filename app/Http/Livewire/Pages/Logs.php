<?php

namespace App\Http\Livewire\Pages;

use App\Enum\Livewire\PageMode;
use App\Enum\SeverityType;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\QueryBuilderException;
use App\Models\Configs;
use App\Models\Logs as ModelsLogs;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class Logs extends Component
{
	public PageMode $mode = PageMode::LOGS;

	/**
	 * We use a computed property instead of attributes
	 * in order to avoid poluting the data sent to the user.
	 *
	 * @return Collection
	 *
	 * @throws ConfigurationKeyMissingException
	 */
	public function getLogsProperty(): Collection
	{
		return ModelsLogs::query()
			->orderBy('id', 'desc')
			->limit(Configs::getValueAsInt('log_max_num_line'))
			->get();
	}

	/**
	 * Empty the log table.
	 *
	 * @return void
	 */
	public function clear(): void
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
	public function clearNoise(): void
	{
		ModelsLogs::query()
			->where('function', '!=', 'App\Http\Controllers\SessionController::login')
			->where('type', '=', SeverityType::NOTICE)
			->delete();
	}

	/**
	 * Rendering of the front-end.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.pages.logs');
	}
}
