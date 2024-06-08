<?php

namespace App\Livewire\DTO;

use App\Contracts\Models\AbstractAlbum;
use App\Enum\AspectRatioType;
use App\Livewire\Traits\UseWireable;
use App\Models\Configs;
use App\Models\Photo;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Laragear\WebAuthn\Models\WebAuthnCredential;
use Livewire\Wireable;

/**
 * @implements Wireable<AlbumsFlags>
 */
class AlbumsFlags implements Wireable
{
	/** @phpstan-use UseWireable<AlbumsFlags> */
	use UseWireable;

	public function __construct(
		public bool $can_edit = false,
		public bool $can_use_2fa = false,
		public bool $is_map_accessible = false,
		public bool $is_mod_frame_enabled = false,
		public bool $is_search_accessible = false,
		public string $album_thumb_css_aspect_ratio = '',
	) {
		$count_locations = Photo::whereNotNull('latitude')->whereNotNull('longitude')->count() > 0;
		$this->is_map_accessible = $count_locations && Configs::getValueAsBool('map_display');
		$this->is_map_accessible = $this->is_map_accessible && (Auth::check() || Configs::getValueAsBool('map_display_public'));
		$this->is_mod_frame_enabled = Configs::getValueAsBool('mod_frame_enabled');
		$this->can_use_2fa = !Auth::check() && (WebAuthnCredential::query()->whereNull('disabled_at')->count() > 0);
		$this->can_edit = Gate::check(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, null]);
		$this->is_search_accessible = Auth::check() || Configs::getValueAsBool('search_public');
		$this->album_thumb_css_aspect_ratio = Configs::getValueAsEnum('default_album_thumb_aspect_ratio', AspectRatioType::class)->css();
	}
}