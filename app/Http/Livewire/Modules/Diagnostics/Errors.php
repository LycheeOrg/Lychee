<?php

namespace App\Http\Livewire\Modules\Diagnostics;

use App\Actions\Diagnostics\Errors as DiagnosticsErrors;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Errors extends Component
{
	public bool $ready_to_load = false;
	public string $title = 'Diagnostics';
	public string $error_msg = 'No critical problems found. Lychee should work without problems!';

	/**
	 * Renders the Errors.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.modules.diagnostics.errors');
	}

	/**
	 * Method call from the front-end to inform it is time to load the errors.
	 *
	 * @return void
	 */
	public function loadErrors(): void
	{
		$this->ready_to_load = true;
	}

	/**
	 * Computable property to access the errors.
	 * If we are not ready to load, we return an empty array.
	 *
	 * @return array
	 */
	public function getDataProperty(): array
	{
		return $this->ready_to_load ? resolve(DiagnosticsErrors::class)->get() : [];
	}
}
