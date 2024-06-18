<?php

declare(strict_types=1);

namespace App\Livewire\Components\Modules\Diagnostics;

use App\Actions\Diagnostics\Info;

class Infos extends AbstractPreSection
{
	public function getTitleProperty(): string
	{
		return 'System Information';
	}

	public function getDataProperty(): array
	{
		return $this->can ? resolve(Info::class)->get() : [];
	}
}
