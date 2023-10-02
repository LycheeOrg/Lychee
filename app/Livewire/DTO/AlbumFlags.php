<?php

namespace App\Livewire\DTO;

use App\Enum\AlbumLayoutType;
use App\Models\Configs;
use Illuminate\Support\Facades\Auth;

class AlbumFlags
{
	public function __construct(
		public bool $is_accessible = false,
		public bool $is_password_protected = false,
		public bool $is_map_accessible = false,
		public bool $is_base_album = false,
		public bool $can_edit = false,
		public ?string $layout = null,
	) {
		$this->is_map_accessible = Configs::getValueAsBool('map_display');
		$this->is_map_accessible = $this->is_map_accessible && (Auth::check() || Configs::getValueAsBool('map_display_public'));
		$this->layout ??= Configs::getValueAsEnum('layout', AlbumLayoutType::class)->value;
	}

	public function layout(): AlbumLayoutType
	{
		return AlbumLayoutType::from($this->layout);
	}
}