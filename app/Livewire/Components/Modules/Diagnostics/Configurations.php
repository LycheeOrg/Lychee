<?php

namespace App\Livewire\Components\Modules\Diagnostics;

use App\Actions\Diagnostics\Configuration;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;

class Configurations extends AbstractPreSection
{
	#[Locked] public string $title = 'Config Information';
	public function getDataProperty(): array
	{
		return Gate::check(SettingsPolicy::CAN_SEE_DIAGNOSTICS, Configs::class) ? resolve(Configuration::class)->get() : [];
	}
}
