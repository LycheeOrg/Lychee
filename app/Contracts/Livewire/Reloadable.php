<?php

declare(strict_types=1);

namespace App\Contracts\Livewire;

interface Reloadable
{
	public function reloadPage(): void;
}
