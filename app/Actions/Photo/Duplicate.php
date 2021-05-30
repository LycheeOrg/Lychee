<?php

namespace App\Actions\Photo;

use App\Actions\Photo\Extensions\Save;
use App\Facades\Helpers;
use App\Factories\AlbumFactory;
use App\Models\Photo;

class Duplicate
{
	use Save;

	private $albumFactory;

	public function __construct(AlbumFactory $albumFactory)
	{
		$this->albumFactory = $albumFactory;
	}

	public function do(array $photoIds, ?string $albumID)
	{
		$photos = Photo::query()->whereIn('id', $photoIds)->get();

		$duplicate = null;
		/** @var Photo $photo */
		foreach ($photos as $photo) {
			$duplicate = new Photo();
			$duplicate->id = Helpers::generateID();
			$duplicate->title = $photo->title;
			$duplicate->description = $photo->description;
			$duplicate->url = $photo->url;
			$duplicate->tags = $photo->tags;
			$duplicate->public = $photo->public;
			$duplicate->type = $photo->type;
			$duplicate->width = $photo->width;
			$duplicate->height = $photo->height;
			$duplicate->filesize = $photo->filesize;
			$duplicate->iso = $photo->iso;
			$duplicate->aperture = $photo->aperture;
			$duplicate->make = $photo->make;
			$duplicate->model = $photo->model;
			$duplicate->lens = $photo->lens;
			$duplicate->shutter = $photo->shutter;
			$duplicate->focal = $photo->focal;
			$duplicate->latitude = $photo->latitude;
			$duplicate->longitude = $photo->longitude;
			$duplicate->altitude = $photo->altitude;
			$duplicate->imgDirection = $photo->imgDirection;
			$duplicate->location = $photo->location;
			$duplicate->taken_at = $photo->taken_at;
			$duplicate->star = $photo->star;
			$duplicate->thumbUrl = $photo->thumbUrl;
			$duplicate->thumb2x = $photo->thumb2x;
			$duplicate->album_id = $albumID ?? $photo->album_id;
			if ($duplicate->album_id === '0') {
				$duplicate->album_id = null;
			}
			$duplicate->checksum = $photo->checksum;
			$duplicate->medium_width = $photo->medium_width;
			$duplicate->medium_height = $photo->medium_height;
			$duplicate->medium2x_width = $photo->medium2x_width;
			$duplicate->medium2x_height = $photo->medium2x_height;
			$duplicate->small_width = $photo->small_width;
			$duplicate->small_height = $photo->small_height;
			$duplicate->small2x_width = $photo->small2x_width;
			$duplicate->small2x_height = $photo->small2x_height;
			$duplicate->owner_id = $photo->owner_id;
			$duplicate->livePhotoContentID = $photo->livePhotoContentID;
			$duplicate->livePhotoUrl = $photo->livePhotoUrl;
			$duplicate->livePhotoChecksum = $photo->livePhotoChecksum;
			$this->save($duplicate);
		}
	}
}
