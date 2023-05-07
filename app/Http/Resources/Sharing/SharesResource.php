<?php

namespace App\Http\Resources\Sharing;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * Data Transfer Object (DTO) to transmit the list of shares to the client.
 */
class SharesResource extends JsonResource
{
	public function __construct(
		public Collection $shared,
		public Collection $albums,
		public Collection $users)
	{
		// Laravel applies a shortcut when this value === null but not when it is something else.
		parent::__construct('must_not_be_null');
	}

	/**
	 * Transform the resource into an array.
	 *
	 * @param \Illuminate\Http\Request $request
	 *
	 * @return array
	 */
	public function toArray($request): array
	{
		return [
			'shared' => SharedAlbumResource::collection($this->shared),
			'albums' => ListedAlbumsResource::collection($this->albums),
			'users' => UserSharedResource::collection($this->users),
		];
	}
}
