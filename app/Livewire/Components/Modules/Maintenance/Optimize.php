<?php

namespace App\Livewire\Components\Modules\Maintenance;

use App\Actions\Db\OptimizeDb;
use App\Actions\Db\OptimizeTables;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * This modules takes care of the optimization of the Database.
 */
class Optimize extends Component
{
	/** @var string[] */
	#[Locked] public array $result = [];
	/**
	 * Rendering of the front-end.
	 *
	 * @return View
	 */
	public function render(): View
	{
		Gate::authorize(SettingsPolicy::CAN_UPDATE, Configs::class);

		return view('livewire.modules.maintenance.optimize-db');
	}

	/**
	 * Apply the indexing and optimization of the database.
	 *
	 * @return void
	 */
	public function do(): void
	{
		Gate::authorize(SettingsPolicy::CAN_UPDATE, Configs::class);
		$this->result = collect(resolve(OptimizeDb::class)->do())
			->merge(collect(resolve(OptimizeTables::class)->do()))
			->all();
	}
}
