<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Rights;

use App\Assets\Features;
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
	public bool $can_pasword_protect = false;
	public bool $can_import_from_server = false;
	public bool $can_make_purchasable = false;
	public bool $can_view_album_people = false;
	public bool $can_trigger_scan = false;
	public bool $can_assign_face = false;
	public bool $can_batch_face_ops = false;

	/**
	 * Given an album, returns the access rights associated to it.
	 */
	public function __construct(
		?AbstractAlbum $abstract_album)
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
		$this->can_pasword_protect = !request()->configs()->getValueAsBool('cache_enabled');
		$this->can_import_from_server = Gate::check(AlbumPolicy::CAN_IMPORT_FROM_SERVER, [AbstractAlbum::class]);
		$this->can_make_purchasable = $this->canMakePurchasable($abstract_album);

		if (Features::active('ai-vision')) {
			$this->can_view_album_people = Gate::check(AlbumPolicy::CAN_VIEW_ALBUM_PEOPLE, [AbstractAlbum::class, $abstract_album]);
			$this->can_trigger_scan = Gate::check(AlbumPolicy::CAN_TRIGGER_SCAN_ON_ALBUM, [AbstractAlbum::class, $abstract_album]);
			$this->can_assign_face = Gate::check(AlbumPolicy::CAN_ASSIGN_FACE_IN_ALBUM, [AbstractAlbum::class, $abstract_album]);
			$this->can_batch_face_ops = Gate::check(AlbumPolicy::CAN_BATCH_FACE_OPS, [AbstractAlbum::class, $abstract_album]);
		}
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
		if (config('features.webshop') === false) {
			return false;
		}

		if (!$abstract_album instanceof Album) {
			return false;
		}

		if (request()->configs()->getValueAsBool('webshop_enabled') === false) {
			return false;
		}

		if (!request()->verify()->is_pro()) {
			return false;
		}

		return Gate::check(AlbumPolicy::CAN_MAKE_PURCHASABLE, [AbstractAlbum::class]);
	}
}
