<?php

namespace App\Http\Livewire\Modules\Diagnostics;

use App\Actions\Diagnostics\Errors as DiagnosticsErrors;
use Livewire\Component;

class Errors extends Component
{
	public $ready_to_load = false;

	public function render()
	{
		return view('livewire.modules.diagnostics.errors');
	}

	public function loadErrors()
	{
		$this->ready_to_load = true;
	}

	public string $title = 'Diagnostics';
	public string $error_msg = 'No critical problems found. Lychee should work without problems!';

	public function getDataProperty(): array
	{
		return $this->ready_to_load ? resolve(DiagnosticsErrors::class)->get() : [];
	}
}
