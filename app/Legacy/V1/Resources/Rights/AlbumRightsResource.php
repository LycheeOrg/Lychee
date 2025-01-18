<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Resources\Rights;

use App\Contracts\Models\AbstractAlbum;
use App\Policies\AlbumPolicy;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;

/**
 * This DTO provides the information whether some actions are available to the user.
 */
final class AlbumRightsResource extends JsonResource
{
	public bool $can_edit;
	public bool $can_share_with_users;
	public bool $can_download;
	public bool $can_upload;

	/**
	 * Given an album, returns the access rights associated to it.
	 *
	 * @param AbstractAlbum $abstractAlbum
	 *
	 * @return void
	 */
	public function __construct(AbstractAlbum $abstractAlbum)
	{
		// Laravel applies a shortcut when this value === null but not when it is something else.
		parent::__construct('must_not_be_null');

		$this->can_edit = Gate::check(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $abstractAlbum]);
		$this->can_share_with_users = Gate::check(AlbumPolicy::CAN_SHARE_WITH_USERS, [AbstractAlbum::class, $abstractAlbum]);
		$this->can_download = Gate::check(AlbumPolicy::CAN_DOWNLOAD, [AbstractAlbum::class, $abstractAlbum]);
		$this->can_upload = Gate::check(AlbumPolicy::CAN_UPLOAD, [AbstractAlbum::class, $abstractAlbum]);
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
			'can_share_with_users' => $this->can_share_with_users,
			'can_download' => $this->can_download,
			'can_upload' => $this->can_upload,
		];
	}
}
