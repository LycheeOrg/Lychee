<?php

namespace App\Livewire\DTO;

use App\Livewire\Traits\UseWireable;
use Livewire\Wireable;

class PhotoFlags implements Wireable
{
	use UseWireable;

	public function __construct(
		public bool $can_autoplay,
		public bool $can_rotate,
	) {
	}
}