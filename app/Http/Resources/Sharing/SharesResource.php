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
		parent::__construct(null);
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
			'shared' => SharedAlbumResource::collection($this->shared)->toArray($request),
			'albums' => ListedAlbumsResource::collection($this->albums)->toArray($request),
			'users' => UserSharedResource::collection($this->users)->toArray($request),
		];
	}
}
