<?php

namespace App\Legacy\V1\Resources\Sharing;

use App\Actions\Sharing\ListedAlbum;
use Illuminate\Http\Resources\Json\JsonResource;

class ListedAlbumsResource extends JsonResource
{
	/**
	 * @param ListedAlbum $albumListed
	 *
	 * @return void
	 */
	public function __construct(object $albumListed)
	{
		parent::__construct($albumListed);
	}

	/**
	 * Transform the resource into an array.
	 *
	 * @param \Illuminate\Http\Request $request
	 *
	 * @return array<string,string>
	 */
	public function toArray($request): array
	{
		return [
			'id' => $this->resource->id,
			'title' => $this->resource->title,
		];
	}
}
