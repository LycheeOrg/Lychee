<?php

namespace App\Http\Resources\Collections;

use App\Http\Resources\Models\AlbumTreeResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * Resource returned when querying for the full tree of acccessible albums.
 */
class AlbumForestResource extends JsonResource
{
	public function __construct(
		private Collection $albums,
		private ?Collection $sharedAlbums = null
	) {
		// Laravel applies a shortcut when this value === null but not when it is something else.
		parent::__construct('must_not_be_null');

		$this->albums = $albums;
		$this->sharedAlbums = $sharedAlbums ?? new Collection();
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
			'albums' => AlbumTreeResource::collection($this->albums),
			'shared_albums' => AlbumTreeResource::collection($this->sharedAlbums),
		];
	}
}