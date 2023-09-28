<?php

namespace App\Livewire\DTO;

use App\Enum\AlbumLayoutType;
use App\Livewire\Traits\UseWireable;
use App\Models\Configs;
use Livewire\Wireable;

class AlbumFlags implements Wireable
{
	use UseWireable;

	public function __construct(
		public bool $is_accessible = false,
		public bool $is_password_protected = false,
		public bool $is_base_album = false,
		public bool $can_edit = false,
		public ?string $layout = null,
	) {
		$this->layout ??= Configs::getValueAsEnum('layout', AlbumLayoutType::class)->value;
	}

	public function layout(): AlbumLayoutType
	{
		return AlbumLayoutType::from($this->layout);
	}
}