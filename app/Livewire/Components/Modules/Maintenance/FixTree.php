<?php

namespace App\Livewire\Components\Modules\Maintenance;

use App\Models\Album;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * When an upload/extract job fail, they tend to leave files behind.
 * This provides the ability to cleans this up.
 */
class FixTree extends Component
{
	#[Locked] public int|null $result = null;
	#[Locked] public string $path = '';
	#[Locked] public array $stats;
	/**
	 * Rendering of the front-end.
	 *
	 * @return View
	 */
	public function render(): View
	{
		Gate::authorize(SettingsPolicy::CAN_UPDATE, Configs::class);

		$query = Album::query();

		$this->stats = $query->countErrors();

		return view('livewire.modules.maintenance.fix-tree');
	}

	/**
	 * Clean the path from all files excluding $this->skip.
	 *
	 * @return void
	 */
	public function do(): void
	{
		Gate::authorize(SettingsPolicy::CAN_UPDATE, Configs::class);

		$query = Album::query();
		$this->result = $query->fixTree();
	}

	/**
	 * Check whether there are files to be removed.
	 * If not, we will not display the module to reduce complexity.
	 *
	 * @return bool
	 */
	public function getNoErrorsFoundProperty(): bool
	{
		return 0 === ($this->stats['oddness'] ?? 0) + ($this->stats['duplicates'] ?? 0) + ($this->stats['wrong_parent'] ?? 0) + ($this->stats['missing_parent'] ?? 0);
	}
}
