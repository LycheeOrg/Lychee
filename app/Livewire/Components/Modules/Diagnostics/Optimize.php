<?php

namespace App\Livewire\Components\Modules\Diagnostics;

use App\Actions\Db\OptimizeDb;
use App\Actions\Db\OptimizeTables;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Optimize extends Component
{
	#[Locked] public string $title = 'Optimize DB';
	#[Locked] public array $result = [];
	#[Locked] public string $action;
	private OptimizeDb $optimizeDb;
	private OptimizeTables $optimizeTables;

	public function boot(): void
	{
		$this->optimizeDb = resolve(OptimizeDb::class);
		$this->optimizeTables = resolve(OptimizeTables::class);
		$this->action = 'Optimize!';
	}

	/**
	 * Rendering of the front-end.
	 *
	 * @return View
	 */
	public function render(): View
	{
		if (!Gate::check(SettingsPolicy::CAN_SEE_DIAGNOSTICS, Configs::class)) {
			$this->result[] = 'Error: You must have administrator rights to see this.';
		}

		return view('livewire.modules.diagnostics.with-action-call');
	}

	/**
	 * Return the size used by Lychee.
	 * We now separate this from the initial get() call as this is quite time consuming.
	 *
	 * @return void
	 */
	public function do(): void
	{
		Gate::authorize(SettingsPolicy::CAN_SEE_DIAGNOSTICS, Configs::class);
		$this->result = collect($this->optimizeDb->do())->merge(collect($this->optimizeTables->do()))->all();
	}
}
