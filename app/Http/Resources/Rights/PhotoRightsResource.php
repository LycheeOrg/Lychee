<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Rights;

use App\Assets\Features;
use App\Contracts\Models\AbstractAlbum;
use App\Models\Photo;
use App\Policies\AlbumPolicy;
use App\Policies\PhotoPolicy;
use Illuminate\Support\Facades\Gate;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class PhotoRightsResource extends Data
{
	public bool $can_edit;
	public bool $can_download;
	public bool $can_access_full_photo;
	public bool $can_view_face_overlays = false;
	public bool $can_dismiss_face = false;
	public bool $can_assign_face = false;
	public bool $can_trigger_scan = false;

	/**
	 * Given a photo, returns the access rights associated to it.
	 *
	 * @param ?AbstractAlbum $album
	 * @param ?Photo         $photo
	 *
	 * @return void
	 */
	public function __construct(?AbstractAlbum $album, ?Photo $photo = null)
	{
		$this->can_edit = Gate::check(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $album]);
		$this->can_download = Gate::check(AlbumPolicy::CAN_DOWNLOAD, [AbstractAlbum::class, $album]);
		$this->can_access_full_photo = Gate::check(AlbumPolicy::CAN_ACCESS_FULL_PHOTO, [AbstractAlbum::class, $album]);

		if (Features::active('ai-vision') && $photo !== null) {
			$this->can_view_face_overlays = Gate::check(PhotoPolicy::CAN_VIEW_FACE_OVERLAYS, $photo);
			$this->can_dismiss_face = Gate::check(PhotoPolicy::CAN_DISMISS_FACE, $photo);
			$this->can_assign_face = Gate::check(PhotoPolicy::CAN_ASSIGN_FACE_ON_PHOTO, $photo);
			$this->can_trigger_scan = Gate::check(PhotoPolicy::CAN_TRIGGER_SCAN_ON_PHOTO, $photo);
		}
	}
}