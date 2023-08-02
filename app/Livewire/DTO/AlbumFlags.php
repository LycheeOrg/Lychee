<?php

namespace App\Livewire\DTO;

use App\DTO\ArrayableDTO;
use App\Livewire\Traits\UseWireable;
use Livewire\Wireable;

class AlbumFlags extends ArrayableDTO implements Wireable
{
	use UseWireable;

	public function __construct(
		public bool $is_locked = false,
		public bool $is_ready_to_load = false,
		public bool $is_base_album = false,
	) {
	}
}