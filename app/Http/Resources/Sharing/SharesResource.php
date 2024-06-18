<?php

declare(strict_types=1);

namespace App\Http\Resources\Sharing;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * Data Transfer Object (DTO) to transmit the list of shares to the client.
 */
class SharesResource extends JsonResource
{
	/**
	 * @param Collection<int,\App\Models\AccessPermission> $shared
	 * @param Collection<int,\App\Models\Album>            $albums
	 * @param Collection<int,\App\Models\User>             $users
	 *
	 * @return void
	 */
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
	 * @return array<string,mixed>
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
