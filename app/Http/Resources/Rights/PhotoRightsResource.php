<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Rights;

use App\Contracts\Models\AbstractAlbum;
use App\Models\Photo;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Gate;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class PhotoRightsResource extends Data
{
	public bool $can_edit;
	public bool $can_download;
	public bool $can_access_full_photo;

	/**
	 * Given a photo, returns the access rights associated to it.
	 *
	 * @param ?AbstractAlbum $album
	 *
	 * @return void
	 */
	public function __construct(?AbstractAlbum $album)
	{
		$this->can_edit = Gate::check(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $album]);
		$this->can_download = Gate::check(AlbumPolicy::CAN_DOWNLOAD, [AbstractAlbum::class, $album]);
		$this->can_access_full_photo = Gate::check(AlbumPolicy::CAN_ACCESS_FULL_PHOTO, [AbstractAlbum::class, $album]);
	}
}