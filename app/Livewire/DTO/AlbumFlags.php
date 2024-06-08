<?php

namespace App\Livewire\DTO;

use App\Enum\AspectRatioType;
use App\Livewire\Traits\UseWireable;
use App\Models\Configs;
use Illuminate\Support\Facades\Auth;
use Livewire\Wireable;

/**
 * @implements Wireable<AlbumFlags>
 */
class AlbumFlags implements Wireable
{
	/** @phpstan-use UseWireable<AlbumFlags> */
	use UseWireable;

	public function __construct(
		public bool $is_accessible = false,
		public bool $is_password_protected = false,
		public bool $is_map_accessible = false,
		public bool $is_base_album = false,
		public bool $is_mod_frame_enabled = false,
		public string $album_thumb_css_aspect_ratio = '',
		public string|null $cover_id = null,
	) {
		$this->is_map_accessible = Configs::getValueAsBool('map_display');
		$this->is_map_accessible = $this->is_map_accessible && (Auth::check() || Configs::getValueAsBool('map_display_public'));
		$this->is_mod_frame_enabled = Configs::getValueAsBool('mod_frame_enabled');
		$this->album_thumb_css_aspect_ratio = Configs::getValueAsEnum('default_album_thumb_aspect_ratio', AspectRatioType::class)->css();
	}
}