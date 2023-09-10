<?php

namespace App\Livewire\Components\Modules\Diagnostics;

use App\Actions\Diagnostics\Info;
use Livewire\Attributes\Locked;

class Infos extends AbstractPreSection
{
	#[Locked] public string $title = 'System Information';
	public function getDataProperty(): array
	{
		return resolve(Info::class)->get();
	}
}
