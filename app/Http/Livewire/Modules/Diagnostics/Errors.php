<?php

namespace App\Http\Livewire\Modules\Diagnostics;

use App\Actions\Diagnostics\Errors as DiagnosticsErrors;

class Errors extends PreSection
{
	public string $title = 'Diagnostics';
	public string $error_msg = 'No critical problems found. Lychee should work without problems!';

	public function getDataProperty(): array
	{
		return resolve(DiagnosticsErrors::class)->get();
	}
}
