<?php

namespace App\DTO\Livewire;

class AlbumFlags
{
	public function __construct(
		public bool $is_locked = false,
		public bool $is_ready_to_load = false,
		public bool $is_base_album = false,
		public bool $is_toggled = false,
		public bool $is_detail_open = false,
	) {
	}
}