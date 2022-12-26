<?php

namespace App\Http\Resources\Rights;

use App\Contracts\Models\AbstractAlbum;
use App\Http\Resources\JsonResource;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Gate;

class RootAlbumRightsResource extends JsonResource
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
			'can_edit' => Gate::check(AlbumPolicy::CAN_UPLOAD, [AbstractAlbum::class, null]), // Needed to allow interaction such as moving albums
			'can_upload' => Gate::check(AlbumPolicy::CAN_UPLOAD, [AbstractAlbum::class, null]),
			'can_import_from_server' => Gate::check(AlbumPolicy::CAN_IMPORT_FROM_SERVER, [AbstractAlbum::class]),
		];
	}
}
