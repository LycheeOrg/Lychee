<?php

namespace App\Livewire\Modules\Diagnostics;

use App\Actions\Diagnostics\Configuration;

class Configurations extends AbstractPreSection
{
	public string $title = 'Config Information';

	public function getDataProperty(): array
	{
		return resolve(Configuration::class)->get();
	}
}
