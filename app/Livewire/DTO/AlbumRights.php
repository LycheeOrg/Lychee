<?php

namespace App\Livewire\DTO;

use App\Contracts\Models\AbstractAlbum;
use App\Livewire\Traits\UseWireable;
use App\Models\Album;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Gate;
use Livewire\Wireable;

/**
 * @implements Wireable<AlbumRights>
 */
class AlbumRights implements Wireable
{
	/** @phpstan-use UseWireable<AlbumRights> */
	use UseWireable;

	public function __construct(
		public bool $can_edit = false,
		public bool $can_share = false,
		public bool $can_share_with_users = false,
		public bool $can_download = false,
		public bool $can_upload = false,
		public bool $can_move = false,
		public bool $can_delete = false,
		public bool $can_access_original = false,
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
			can_edit: Gate::check(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $abstractAlbum]),
			can_share: Gate::check(AlbumPolicy::CAN_SHARE, [AbstractAlbum::class, $abstractAlbum]),
			can_share_with_users: Gate::check(AlbumPolicy::CAN_SHARE_WITH_USERS, [AbstractAlbum::class, $abstractAlbum]),
			can_download: Gate::check(AlbumPolicy::CAN_DOWNLOAD, [AbstractAlbum::class, $abstractAlbum]),
			can_upload: Gate::check(AlbumPolicy::CAN_UPLOAD, [AbstractAlbum::class, $abstractAlbum]),
			can_move: Gate::check(AlbumPolicy::CAN_DELETE, [AbstractAlbum::class, $abstractAlbum]) && $abstractAlbum instanceof Album,
			can_delete: Gate::check(AlbumPolicy::CAN_DELETE, [AbstractAlbum::class, $abstractAlbum]),
			can_access_original: Gate::check(AlbumPolicy::CAN_ACCESS_FULL_PHOTO, [AbstractAlbum::class, $abstractAlbum]),
		);
	}
}
