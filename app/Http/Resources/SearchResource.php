<?php

namespace App\Http\Resources;

use App\Http\Resources\Models\AlbumResource;
use App\Http\Resources\Models\PhotoResource;
use App\Http\Resources\Models\TagAlbumResource;
use App\Models\Album;
use App\Models\Photo;
use App\Models\TagAlbum;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class SearchResource extends JsonResource
{
	public function __construct(
		public Collection $albums,
		public Collection $tag_albums,
		public Collection $photos,
	) {
		// Laravel applies a shortcut when this value === null but not when it is something else.
		parent::__construct('must_not_be_null');
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
		$albumIDs = $this->albums->reduce(fn (string $carry, Album $item) => $carry . $item->id, '');
		$tagAlbumsIds = $this->tag_albums->reduce(fn (string $carry, TagAlbum $item) => $carry . $item->id, '');
		$photosIds = $this->photos->reduce(fn (string $carry, Photo $item) => $carry . $item->id, '');
		// The checksum is used by the web front-end as an efficient way to
		// avoid rebuilding the GUI, if two subsequent searches return the
		// same result.
		// The front-end performs a live search, while the user is typing
		// a term.
		// If the GUI was rebuilt every time after the user had typed the
		// next character of a search term although the search result might
		// stay the same, the GUI would flicker.
		// The checksum is just over the id, we do not need a full conversion of the data.
		$checksum = md5($albumIDs . $tagAlbumsIds . $photosIds);

		return [
			'albums' => AlbumResource::collection($this->albums)->toArray($request),
			'tag_albums' => TagAlbumResource::collection($this->tag_albums)->toArray($request),
			'photos' => PhotoResource::collection($this->photos)->toArray($request),
			'checksum' => $checksum,
		];
	}
}
