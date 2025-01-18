<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Resources\Rights;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * This DTO provides the application rights of the user.
 */
final class GlobalRightsResource extends JsonResource
{
	public function __construct()
	{
		// Laravel applies a shortcut when this value === null but not when it is something else.
		parent::__construct('must_not_be_null');
	}

	/**
	 * Transform the resource into an array.
	 *
	 * @param \Illuminate\Http\Request $request
	 *
	 * @return array<string,mixed>|\Illuminate\Contracts\Support\Arrayable<string,mixed>|\JsonSerializable
	 */
	public function toArray($request)
	{
		return [
			'root_album' => RootAlbumRightsResource::make(),
			'settings' => SettingsRightsResource::make(),
			'user_management' => UserManagementRightsResource::make(),
			'user' => UserRightsResource::make(),
		];
	}
}
