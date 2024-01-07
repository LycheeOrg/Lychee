<?php

namespace App\Livewire\Components\Modules\Diagnostics;

use App\Models\Configs;
use App\Policies\SettingsPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Basic pre section to display the diagnostics parts.
 */
abstract class AbstractPreSection extends Component
{
	#[Locked] public bool $can;
	final public function boot(): void
	{
		$this->can = Gate::check(SettingsPolicy::CAN_SEE_DIAGNOSTICS, Configs::class);
	}

	/**
	 * Rendering of the front-end.
	 *
	 * @return View
	 */
	final public function render(): View
	{
		return view('livewire.modules.diagnostics.pre');
	}

	/**
	 * Defined the data to be displayed.
	 *
	 * @return array<int,string>
	 */
	abstract public function getDataProperty(): array;

	/**
	 * Defined the title to be displayed.
	 *
	 * @return string
	 */
	abstract public function getTitleProperty(): string;
}
