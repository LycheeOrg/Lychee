<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Traits;

use App\Http\Resources\Models\PhotoResource;
use App\Models\Configs;
use Illuminate\Support\Collection;

/**
 * @property ?Collection<int,PhotoResource> $photos
 */
trait HasPrepPhotoCollection
{
	private function prepPhotosCollection(): void
	{
		$previous_photo = null;
		$this->photos->each(function (PhotoResource &$photo) use (&$previous_photo) {
			if ($previous_photo !== null) {
				$previous_photo->next_photo_id = $photo->id;
			}
			$photo->previous_photo_id = $previous_photo?->id;
			$previous_photo = $photo;
		});

		if ($this->photos->count() > 1 && Configs::getValueAsBool('photos_wraparound')) {
			$this->photos->first()->previous_photo_id = $this->photos->last()->id;
			$this->photos->last()->next_photo_id = $this->photos->first()->id;
		}
	}
}