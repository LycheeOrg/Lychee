<?php

namespace App\Relations;

use App\Models\Configs;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class LinkedPhotoCollection.
 *
 * If this instance is serialized to JSON, the each element the result array
 * contains two JSON attributes which link to the previous and next element.
 */
class LinkedPhotoCollection extends Collection
{
	public function toArray(): array
	{
		$photos = $this->all();
		$count = count($photos);

		for ($i = 0; $i !== $count; $i++) {
			/** @var Photo $photo */
			$photo = $photos[$i];
			$photos[$i] = $photo->toArray();
			$photos[$i]['previousPhoto'] = $i > 0 ? $photos[$i - 1]['id'] : null;
			$photos[$i]['nextPhoto'] = $i + 1 < $count ? $photos[$i + 1]->id : null;
		}

		if ($count > 1 && Configs::get_value('photos_wraparound', '1') === '1') {
			$photos[0]['previousPhoto'] = $photos[$count - 1]['id'];
			$photos[$count - 1]['nextPhoto'] = $photos[0]['id'];
		}

		return $photos;
	}
}
