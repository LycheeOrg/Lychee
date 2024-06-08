<?php

namespace App\Http\Resources\Models;

use App\Models\Album;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Slimmed-down when requesting the tree of accessible albums.
 */
class AlbumTreeResource extends JsonResource
{
	/**
	 * Album to slim down.
	 *
	 * @param Album $album
	 *
	 * @return void
	 */
	public function __construct(Album $album)
	{
		parent::__construct($album);
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
			// basic
			'id' => $this->resource->id,
			'title' => $this->resource->title,
			'parent_id' => $this->resource->parent_id,
			'thumb' => $this->resource->thumb,
			'albums' => AlbumTreeResource::collection($this->whenLoaded('children')),
		];
	}
}