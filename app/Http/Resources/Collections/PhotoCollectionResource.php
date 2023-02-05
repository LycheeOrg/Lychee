<?php

namespace App\Http\Resources\Collections;

use App\Http\Resources\Models\PhotoResource;
use App\Models\Configs;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * While we could lever on the PhotoResource::collection(...)
 * It does not provides with the next/previous photo connection.
 * This aims to solve this problem.
 */
class PhotoCollectionResource extends ResourceCollection
{
	/**
	 * The resource that this resource collects.
	 *
	 * @var string
	 */
	public $collects = PhotoResource::class;

	/**
	 * Transform the resource collection into an array.
	 *
	 * @param \Illuminate\Http\Request $request
	 *
	 * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
	 */
	public function toArray($request)
	{
		if ($this->collection->count() === 0) {
			return [];
		}

		$photos = [];
		$i = 0;

		/** @var PhotoResource $photoResource the photo */
		foreach ($this->collection as $photoResource) {
			// We need to specify the return type to inform Phpstan that the appropriate property exists.
			// Alternatively we could document properly the PhotoResource::toArray() but then the phpdoc
			// of returns becomes a bit too messy.
			/** @var array{id:string} $photoArray */
			$photoArray = $photoResource->toArray($request);
			$photos[] = $photoArray;
			if ($i > 0) {
				$photos[$i - 1]['next_photo_id'] = $photos[$i]['id'];
				$photos[$i]['previous_photo_id'] = $photos[$i - 1]['id'];
			}
			$i++;
		}

		$count = count($photos);

		if ($count > 1 && Configs::getValueAsBool('photos_wraparound')) {
			$photos[0]['previous_photo_id'] = $photos[$count - 1]['id'];
			$photos[$count - 1]['next_photo_id'] = $photos[0]['id'];
		} else {
			$photos[0]['previous_photo_id'] = null;
			$photos[$count - 1]['next_photo_id'] = null;
		}

		return $photos;
	}
}
