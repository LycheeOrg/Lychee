<?php

namespace App\Http\Livewire\Modules\Diagnostics;

use App\Actions\Diagnostics\Info;

class Infos extends AbstractPreSection
{
	public string $title = 'System Information';

	public function getDataProperty(): array
	{
		return resolve(Info::class)->get();
	}
}
