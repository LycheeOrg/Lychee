<?php

namespace App\Livewire\DTO;

use App\DTO\ArrayableDTO;
use App\Livewire\Traits\UseWireable;
use App\Models\Configs;
use Illuminate\Support\Facades\Session;
use Livewire\Wireable;

class SessionFlags extends ArrayableDTO implements Wireable
{
	use UseWireable;

	public function __construct(
		public bool $can_fullscreen,
		public bool $is_fullscreen,
		public bool $are_photo_details_open,
		public bool $nsfwAlbumsVisible,
	) {
	}

	public static function get(): SessionFlags
	{
		$default = new SessionFlags(
			can_fullscreen: true,
			is_fullscreen: false,
			are_photo_details_open: false,
			nsfwAlbumsVisible: Configs::getValueAsBool('nsfw_visible')
		);

		return Session::get('session-flags', $default);
	}

	public function save(): void
	{
		Session::put('session-flags', $this);
	}
}