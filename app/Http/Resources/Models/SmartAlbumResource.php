<?php

namespace App\Http\Resources\Models;

use App\DTO\AlbumProtectionPolicy;
use App\Http\Resources\Collections\PhotoCollectionResource;
use App\Http\Resources\Rights\AlbumRightsResource;
use App\SmartAlbums\BaseSmartAlbum;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Converts a Smart album into a resource with all the required data.
 */
class SmartAlbumResource extends JsonResource
{
	public function __construct(BaseSmartAlbum $smartAlbum)
	{
		parent::__construct($smartAlbum);
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
			// basic
			'id' => $this->resource->id,
			'title' => $this->resource->title,

			// We use getPhotos() to be sure to not execute and cache the photos.
			// Some of the tests do check what is the value of the thumb id as a result,
			// if the id is not in thumb (intended behaviour we want to check)
			// but still in the photos (supposed to be null), this fail the test.
			'photos' => $this->whenLoaded('photos', PhotoCollectionResource::make($this->resource->getPhotos() ?? []), null),

			// thumb
			'thumb' => $this->resource->thumb,

			// security
			'policy' => AlbumProtectionPolicy::ofSmartAlbum($this->resource)->toArray(),
			'rights' => AlbumRightsResource::make($this->resource)->toArray($request),
		];
	}
}
