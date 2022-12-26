<?php

namespace App\Http\Resources\Rights;

use App\Http\Resources\JsonResource;

class GlobalRightsResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param \Illuminate\Http\Request $request
	 *
	 * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
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
