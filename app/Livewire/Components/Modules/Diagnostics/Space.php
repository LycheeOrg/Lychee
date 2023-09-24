<?php

namespace App\Livewire\Components\Modules\Diagnostics;

use App\Actions\Diagnostics\Space as DiagnosticsSpace;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Space extends Component
{
	#[Locked] public string $title = 'Space Usage';
	#[Locked] public array $space = [];
	private DiagnosticsSpace $diagnostics;

	public function boot(): void
	{
		$this->diagnostics = resolve(DiagnosticsSpace::class);
	}

	/**
	 * Rendering of the front-end.
	 *
	 * @return View
	 */
	public function render(): View
	{
		if (!Gate::check(SettingsPolicy::CAN_SEE_DIAGNOSTICS, Configs::class)) {
			$this->space[] = 'Error: You must have administrator rights to see this.';
		}

		return view('livewire.modules.diagnostics.space');
	}

	/**
	 * Return the size used by Lychee.
	 * We now separate this from the initial get() call as this is quite time consuming.
	 *
	 * @return void
	 */
	public function getSize(): void
	{
		Gate::authorize(SettingsPolicy::CAN_SEE_DIAGNOSTICS, Configs::class);
		$this->space = $this->diagnostics->get();
	}
}
