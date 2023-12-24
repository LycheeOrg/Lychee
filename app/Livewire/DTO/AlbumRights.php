<?php

namespace App\Livewire\DTO;

use App\Contracts\Models\AbstractAlbum;
use App\Livewire\Traits\UseWireable;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Gate;
use Livewire\Wireable;

class AlbumRights implements Wireable
{
	use UseWireable;

	public function __construct(
		public bool $can_edit = false,
		public bool $can_share_with_users = false,
		public bool $can_download = false,
		public bool $can_upload = false,
		public bool $can_delete = false,
	) {
	}

	/**
	 * Builder for easy instanciation.
	 *
	 * @param AbstractAlbum $abstractAlbum
	 *
	 * @return self
	 */
	public static function make(?AbstractAlbum $abstractAlbum): AlbumRights
	{
		return new self(
			Gate::check(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $abstractAlbum]),
			Gate::check(AlbumPolicy::CAN_SHARE_WITH_USERS, [AbstractAlbum::class, $abstractAlbum]),
			Gate::check(AlbumPolicy::CAN_DOWNLOAD, [AbstractAlbum::class, $abstractAlbum]),
			Gate::check(AlbumPolicy::CAN_UPLOAD, [AbstractAlbum::class, $abstractAlbum]),
			Gate::check(AlbumPolicy::CAN_DELETE, [AbstractAlbum::class, $abstractAlbum]),
		);
	}
}
