<?php

namespace App\Livewire\DTO;

use App\DTO\ArrayableDTO;
use App\Enum\Livewire\AlbumMode;
use App\Livewire\Traits\UseWireable;
use App\Models\Configs;
use Livewire\Wireable;

class AlbumFlags extends ArrayableDTO implements Wireable
{
	use UseWireable;

	public function __construct(
		public bool $is_locked = false,
		public bool $is_ready_to_load = false,
		public bool $is_base_album = false,
		public int $layout = 0,
	) {
		$this->layout = Configs::getValueAsEnum('layout', AlbumMode::class)->value;
	}
}