<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Rights;

use App\Contracts\Models\AbstractAlbum;
use App\Models\Album;
use App\Models\Configs;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Gate;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class AlbumRightsResource extends Data
{
	public bool $can_edit = false;
	public bool $can_share = false;
	public bool $can_share_with_users = false;
	public bool $can_download = false;
	public bool $can_upload = false;
	public bool $can_move = false;
	public bool $can_delete = false;
	public bool $can_transfer = false;
	public bool $can_access_original = false;
	public bool $can_pasword_protect = false;

	/**
	 * Given an album, returns the access rights associated to it.
	 */
	public function __construct(?AbstractAlbum $abstract_album)
	{
		$this->can_edit = Gate::check(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $abstract_album]);
		$this->can_share = Gate::check(AlbumPolicy::CAN_SHARE, [AbstractAlbum::class, $abstract_album]);
		$this->can_share_with_users = Gate::check(AlbumPolicy::CAN_SHARE_WITH_USERS, [AbstractAlbum::class, $abstract_album]);
		$this->can_download = Gate::check(AlbumPolicy::CAN_DOWNLOAD, [AbstractAlbum::class, $abstract_album]);
		$this->can_upload = Gate::check(AlbumPolicy::CAN_UPLOAD, [AbstractAlbum::class, $abstract_album]);
		$this->can_move = Gate::check(AlbumPolicy::CAN_DELETE, [AbstractAlbum::class, $abstract_album]) && $abstract_album instanceof Album;
		$this->can_delete = Gate::check(AlbumPolicy::CAN_DELETE, [AbstractAlbum::class, $abstract_album]);
		$this->can_transfer = Gate::check(AlbumPolicy::CAN_TRANSFER, [AbstractAlbum::class, $abstract_album]);
		$this->can_access_original = Gate::check(AlbumPolicy::CAN_ACCESS_FULL_PHOTO, [AbstractAlbum::class, $abstract_album]);
		$this->can_pasword_protect = !Configs::getValueAsBool('cache_enabled');
	}
}
