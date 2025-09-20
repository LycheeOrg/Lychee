<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Rights;

use App\Contracts\Models\AbstractAlbum;
use App\Models\Album;
use App\Models\Configs;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use LycheeVerify\Verify;
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
	public bool $can_import_from_server = false;
	public bool $can_make_purchasable = false;

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
		$this->can_import_from_server = Auth::check() && Auth::id() === Configs::getValueAsInt('owner_id');
		$this->can_make_purchasable = $this->canMakePurchasable($abstract_album);
	}

	/**
	 * Check if the user can make the album purchasable.
	 * This requires the user to be a supporter and have the relevant permission.
	 *
	 * @param AbstractAlbum|null $abstract_album
	 *
	 * @return bool
	 */
	public function canMakePurchasable(?AbstractAlbum $abstract_album): bool
	{
		if (!$abstract_album instanceof Album) {
			return false;
		}

		$verify = resolve(Verify::class);
		if (!$verify->is_supporter()) {
			return false;
		}

		return Gate::check(AlbumPolicy::CAN_MAKE_PURCHASABLE, [AbstractAlbum::class]);
	}
}
