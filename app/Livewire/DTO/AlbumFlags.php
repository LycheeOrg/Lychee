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
		public bool $is_accessible = false,
		public bool $is_password_protected = false,
		public bool $is_ready_to_load = false,
		public bool $is_base_album = false,
		public ?int $layout = null,
	) {
		$this->layout ??= Configs::getValueAsEnum('layout', AlbumMode::class)->value;
	}

	public function layout(): AlbumMode {
		return AlbumMode::from($this->layout);
	}
}