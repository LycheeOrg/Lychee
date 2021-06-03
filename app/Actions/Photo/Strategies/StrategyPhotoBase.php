<?php

namespace App\Actions\Photo\Strategies;

use App\Actions\Photo\Create;
use App\Actions\Photo\Extensions\Metadata;
use App\Contracts\AddPhotoStrategyInterface;
use App\Facades\AccessControl;
use App\Models\Photo;

abstract class StrategyPhotoBase implements AddPhotoStrategyInterface
{
	use Metadata;

	abstract public function storeFile(Create $create);

	abstract public function hydrate(Create &$create, ?Photo &$existing = null, ?array $file = null);

	abstract public function generate_thumbs(Create &$create, bool &$skip_db_entry_creation, bool &$no_error);

	public function loadMetadata(Create &$create, array $file)
	{
		$info = $this->getMetadata($file, $create->path, $create->kind, $create->extension);

		$create->photo->title = $info['title'];
		$create->photo->filename = $create->photo_filename;
		$create->photo->description = $info['description'];
		$create->photo->tags = $info['tags'];
		$create->photo->width = $info['width'] ? $info['width'] : 0;
		$create->photo->height = $info['height'] ? $info['height'] : 0;
		$create->photo->type = ($info['type'] ? $info['type'] : $create->mimeType);
		$create->photo->filesize = $info['filesize'];
		$create->photo->iso = $info['iso'];
		$create->photo->aperture = $info['aperture'];
		$create->photo->make = $info['make'];
		$create->photo->model = $info['model'];
		$create->photo->lens = $info['lens'];
		$create->photo->shutter = $info['shutter'];
		$create->photo->focal = $info['focal'];
		$create->photo->taken_at = $info['taken_at'];
		$create->photo->latitude = $info['latitude'];
		$create->photo->longitude = $info['longitude'];
		$create->photo->altitude = $info['altitude'];
		$create->photo->imgDirection = $info['imgDirection'];
		$create->photo->location = $info['location'];
		$create->photo->live_photo_content_id = $info['live_photo_content_id'];
		$create->photo->public = $create->public;
		$create->photo->star = $create->star;

		$create->info = $info;
	}

	public function getMetadata($file, $path, $kind, $extension): array
	{
		// forward call to trait.
		return $this->getFileMetadata($file, $path, $kind, $extension);
	}

	public function setParentAndOwnership(Create &$create)
	{
		if ($create->parentAlbum !== null) {
			$create->photo->album_id = $create->albumID;
			$create->photo->owner_id = $create->parentAlbum->owner_id;
		} else {
			$create->photo->album_id = null;
			$create->photo->owner_id = AccessControl::id();
		}
	}

	public function findLivePartner(Create &$create)
	{
		$livePhotoPartner = null;
		if ($create->photo->live_photo_content_id) {
			// Todo: We need to search for pairs (Video + Photo)
			// Photo+Photo or Video+Video does not work

			$livePhotoPartner = Photo::query()
				->where('live_photo_content_id', '=', $create->photo->live_photo_content_id)
				->where('album_id', '=', $create->photo->album_id)
				->whereNull('live_photo_filename')->first();
		}

		if ($livePhotoPartner != null) {
			// if both are a photo or a video -> it's not a live photo
			if (in_array($create->photo->type, $create->validVideoTypes, true) === in_array($livePhotoPartner->type, $create->validVideoTypes, true)) {
				$livePhotoPartner = null;
			}
		}

		if ($livePhotoPartner != null) {
			// I'm uploading a photo, video already exists
			if (!(in_array($create->photo->type, $create->validVideoTypes, true))) {
				$create->photo->live_photo_filename = $create->livePhotoPartner->filename;
				$create->photo->live_photo_checksum = $create->livePhotoPartner->checksum;
				// Todo: Delete the livePhotoPartner

				$create->livePhotoPartner->predelete(true);
				$create->livePhotoPartner->delete();
			}
		}

		$create->livePhotoPartner = $livePhotoPartner;
	}
}
