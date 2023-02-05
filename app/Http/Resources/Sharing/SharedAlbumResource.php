<?php

namespace App\Http\Resources\Sharing;

use App\Actions\Sharing\SharedAlbum;
use Illuminate\Http\Resources\Json\JsonResource;

class SharedAlbumResource extends JsonResource
{
	/**
	 * @param SharedAlbum $albumShared
	 *
	 * @return void
	 */
	public function __construct(object $albumShared)
	{
		parent::__construct($albumShared);
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
			'id' => $this->resource->id,
			'user_id' => $this->resource->user_id,
			'album_id' => $this->resource->album_id,
			'username' => $this->resource->username,
			'title' => $this->resource->title,
		];
	}
}
