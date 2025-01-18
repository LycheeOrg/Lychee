<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Resources\Rights;

use App\Models\Photo;
use App\Policies\PhotoPolicy;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;

/**
 * This DTO provides the information whether some actions are available to the user.
 */
final class PhotoRightsResource extends JsonResource
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
		// Laravel applies a shortcut when this value === null but not when it is something else.
		parent::__construct('must_not_be_null');
		$this->can_edit = Gate::check(PhotoPolicy::CAN_EDIT, [Photo::class, $photo]);
		$this->can_download = Gate::check(PhotoPolicy::CAN_DOWNLOAD, [Photo::class, $photo]);
		$this->can_access_full_photo = Gate::check(PhotoPolicy::CAN_ACCESS_FULL_PHOTO, [Photo::class, $photo]);
	}

	/**
	 * Transform the resource into an array.
	 *
	 * @param \Illuminate\Http\Request $request
	 *
	 * @return array<string,bool>|\Illuminate\Contracts\Support\Arrayable<string,bool>|\JsonSerializable
	 */
	public function toArray($request)
	{
		return [
			'can_edit' => $this->can_edit,
			'can_download' => $this->can_download,
			'can_access_full_photo' => $this->can_access_full_photo,
		];
	}
}