<?php

namespace App\Relations;

use App\Exceptions\Internal\IllegalOrderOfOperationException;
use App\Models\Configs;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class LinkedPhotoCollection.
 *
 * If serialized to JSON, each element of the resulting array contains two
 * JSON attributes which link to the previous and next element.
 */
class LinkedPhotoCollection extends Collection
{
	/**
	 * @throws IllegalOrderOfOperationException
	 */
	public function toArray(): array
	{
		$photos = [];
		$i = 0;

		if ($this->isEmpty()) {
			return $photos;
		}

		/** @var Photo $photo the photo */
		foreach ($this->items as $photo) {
			$photos[] = $photo->toArray();
			if ($i > 0) {
				$photos[$i - 1]['next_photo_id'] = $photos[$i]['id'];
				$photos[$i]['previous_photo_id'] = $photos[$i - 1]['id'];
			}
			$i++;
		}

		$count = count($photos);

		if ($count > 1 && Configs::get_value('photos_wraparound', '1') === '1') {
			$photos[0]['previous_photo_id'] = $photos[$count - 1]['id'];
			$photos[$count - 1]['next_photo_id'] = $photos[0]['id'];
		} else {
			$photos[0]['previous_photo_id'] = null;
			$photos[$count - 1]['next_photo_id'] = null;
		}

		return $photos;
	}
}
