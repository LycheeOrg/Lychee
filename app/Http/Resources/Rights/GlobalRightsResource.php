<?php

namespace App\Http\Resources\Rights;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * This DTO provides the application rights of the user.
 */
class GlobalRightsResource extends JsonResource
{
	public function __construct()
	{
		parent::__construct(null);
	}

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
			'root_album' => RootAlbumRightsResource::make()->toArray($request),
			'settings' => SettingsRightsResource::make()->toArray($request),
			'user_management' => UserManagementRightsResource::make()->toArray($request),
			'user' => UserRightsResource::make()->toArray($request),
		];
	}
}
