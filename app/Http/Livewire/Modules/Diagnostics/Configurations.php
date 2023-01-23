<?php

namespace App\Http\Livewire\Modules\Diagnostics;

use App\Actions\Diagnostics\Configuration;

class Configurations extends AbstractPreSection
{
	public string $title = 'Config Information';

	public function getDataProperty(): array
	{
		return resolve(Configuration::class)->get();
	}
}
