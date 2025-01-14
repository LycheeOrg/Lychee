<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Rights;

use App\Contracts\Models\AbstractAlbum;
use App\Models\Album;
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

	/**
	 * Given an album, returns the access rights associated to it.
	 */
	public function __construct(?AbstractAlbum $abstractAlbum)
	{
		$this->can_edit = Gate::check(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $abstractAlbum]);
		$this->can_share = Gate::check(AlbumPolicy::CAN_SHARE, [AbstractAlbum::class, $abstractAlbum]);
		$this->can_share_with_users = Gate::check(AlbumPolicy::CAN_SHARE_WITH_USERS, [AbstractAlbum::class, $abstractAlbum]);
		$this->can_download = Gate::check(AlbumPolicy::CAN_DOWNLOAD, [AbstractAlbum::class, $abstractAlbum]);
		$this->can_upload = Gate::check(AlbumPolicy::CAN_UPLOAD, [AbstractAlbum::class, $abstractAlbum]);
		$this->can_move = Gate::check(AlbumPolicy::CAN_DELETE, [AbstractAlbum::class, $abstractAlbum]) && $abstractAlbum instanceof Album;
		$this->can_delete = Gate::check(AlbumPolicy::CAN_DELETE, [AbstractAlbum::class, $abstractAlbum]);
		$this->can_transfer = Gate::check(AlbumPolicy::CAN_TRANSFER, [AbstractAlbum::class, $abstractAlbum]);
		$this->can_access_original = Gate::check(AlbumPolicy::CAN_ACCESS_FULL_PHOTO, [AbstractAlbum::class, $abstractAlbum]);
	}
}
