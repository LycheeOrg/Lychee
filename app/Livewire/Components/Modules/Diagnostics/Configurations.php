<?php

namespace App\Livewire\Components\Modules\Diagnostics;

use App\Actions\Diagnostics\Configuration;
use Livewire\Attributes\Locked;

class Configurations extends AbstractPreSection
{
	#[Locked] public string $title = 'Config Information';
	public function getDataProperty(): array
	{
		return $this->can ? resolve(Configuration::class)->get() : [];
	}
}
