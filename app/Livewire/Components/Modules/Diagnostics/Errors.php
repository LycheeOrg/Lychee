<?php

namespace App\Livewire\Components\Modules\Diagnostics;

use App\Actions\Diagnostics\Errors as DiagnosticsErrors;

class Errors extends AbstractPreSection
{
	public string $title = 'Diagnostics';
	public string $error_msg = 'No critical problems found. Lychee should work without problems!';

	public function placeholder(): string
	{
		return '<p class="font-mono">
	Diagnostics
	-----------
	<span class="text-sky-500 font-bold">    ' . __('lychee.LOADING') . ' ...</span>
</p>
';
	}

	/**
	 * Computable property to access the errors.
	 * If we are not ready to load, we return an empty array.
	 *
	 * @return array
	 */
	public function getDataProperty(): array
	{
		return resolve(DiagnosticsErrors::class)->get();
	}
}
