<?php

declare(strict_types=1);

namespace App\Livewire\Components\Modules\Diagnostics;

use App\Actions\Diagnostics\Configuration;

class Configurations extends AbstractPreSection
{
	public function getTitleProperty(): string
	{
		return 'Config Information';
	}

	public function getDataProperty(): array
	{
		return $this->can ? resolve(Configuration::class)->get() : [];
	}
}
