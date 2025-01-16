<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Rights;

use App\Models\Photo;
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

	/**
	 * Given a photo, returns the access rights associated to it.
	 *
	 * @param Photo $photo
	 *
	 * @return void
	 */
	public function __construct(Photo $photo)
	{
		$this->can_edit = Gate::check(PhotoPolicy::CAN_EDIT, [Photo::class, $photo]);
		$this->can_download = Gate::check(PhotoPolicy::CAN_DOWNLOAD, [Photo::class, $photo]);
		$this->can_access_full_photo = Gate::check(PhotoPolicy::CAN_ACCESS_FULL_PHOTO, [Photo::class, $photo]);
	}
}