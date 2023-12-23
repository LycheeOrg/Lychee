<?php

namespace App\Livewire\Components\Modules\Diagnostics;

use App\Actions\Diagnostics\Info;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;

class Infos extends AbstractPreSection
{
	#[Locked] public string $title = 'System Information';
	public function getDataProperty(): array
	{
		return Gate::check(SettingsPolicy::CAN_SEE_DIAGNOSTICS, Configs::class) ? resolve(Info::class)->get() : [];
	}
}
