<?php

namespace App\Http\Resources\Rights;

use App\Contracts\Models\AbstractAlbum;
use App\Policies\AlbumPolicy;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;

/**
 * Data Transfer Object (DTO) to transmit the rights of an user at the root level.
 */
class RootAlbumRightsResource extends JsonResource
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
			// Needed to allow interaction such as moving albums
			'can_edit' => Gate::check(AlbumPolicy::CAN_UPLOAD, [AbstractAlbum::class, null]),
			// Needed to allow upload at root level (into unsorted)
			'can_upload' => Gate::check(AlbumPolicy::CAN_UPLOAD, [AbstractAlbum::class, null]),
			'can_import_from_server' => Gate::check(AlbumPolicy::CAN_IMPORT_FROM_SERVER, [AbstractAlbum::class]),
		];
	}
}
