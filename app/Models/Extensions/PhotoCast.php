<?php

namespace App\Models\Extensions;

use App\ModelFunctions\SymLinkFunctions;
use App\Models\Photo;
use Helpers;
use Illuminate\Support\Facades\Storage;

trait PhotoCast
{
	/**
	 * Returns photo-attributes into a front-end friendly format. Note that some attributes remain unchanged.
	 *
	 * @return array
	 */
	public function toReturnArray(): array
	{
		if (strpos($this->type, 'video') === 0) {
			$baseFileName = $this->thumbUrl;
		} elseif ($this->type == 'raw') {
			// It's a raw file -> we also use jpeg as extension
			$baseFileName = $this->thumbUrl;
		} else {
			$baseFileName = $this->url;
		}
		$baseFileName2x = ($baseFileName !== '') ? Helpers::ex2x($baseFileName) : '';
		$thumbFileName2x = $this->thumb2x === '1' ? Helpers::ex2x($this->thumbUrl) : null;

		$sizeVariants = [
			'thumb' => $this->serializeSizeVariant('thumb', $this->thumbUrl, Photo::THUMBNAIL_DIM, Photo::THUMBNAIL_DIM),
			'thumb2x' => $this->serializeSizeVariant('thumb', $thumbFileName2x, Photo::THUMBNAIL2X_DIM, Photo::THUMBNAIL2X_DIM),
			'small' => $this->serializeSizeVariant('small', $baseFileName, $this->small_width, $this->small_height),
			'small2x' => $this->serializeSizeVariant('small', $baseFileName2x, $this->small2x_width, $this->small2x_height),
			'medium' => $this->serializeSizeVariant('medium', $baseFileName, $this->medium_width, $this->medium_height),
			'medium2x' => $this->serializeSizeVariant('medium', $baseFileName2x, $this->medium2x_width, $this->medium2x_height),
		];

		return [
			'id' => strval($this->id),
			'title' => $this->title,
			'description' => $this->description == null ? '' : $this->description,
			'tags' => $this->tags,
			'star' => Helpers::str_of_bool($this->star),
			'public' => $this->get_public(),
			'album' => $this->album_id !== null ? strval($this->album_id) : null,
			'url' => ($this->type == 'raw') ? Storage::url('raw/' . $this->url) : Storage::url('big/' . $this->url),
			'width' => strval($this->width),
			'width_raw' => $this->width !== null ? $this->width : -1,
			'height' => strval($this->height),
			'height_raw' => $this->height !== null ? $this->height : -1,
			'type' => $this->type,
			'size' => $this->size,
			'iso' => $this->iso,
			'aperture' => $this->aperture,
			'make' => $this->make,
			'model' => $this->model,
			'shutter' => $this->get_shutter_str(),
			// We need to format the framerate (stored as focal) -> max 2 decimal digits
			'focal' => (strpos($this->type, 'video') === 0) ? round($this->focal, 2) : $this->focal,
			'lens' => $this->lens,
			'latitude' => $this->latitude,
			'longitude' => $this->longitude,
			'altitude' => $this->altitude,
			'imgDirection' => $this->imgDirection,
			'location' => $this->location,
			'livePhotoContentID' => $this->livePhotoContentID,
			'livePhotoUrl' => ($this->livePhotoUrl !== '' && $this->livePhotoUrl !== null) ? Storage::url('big/' . $this->livePhotoUrl) : null,
			'sysdate' => $this->created_at->format('d F Y \a\t H:i'),
			'created_at_raw' => $this->created_at->timestamp,
			'takedate' => isset($this->takestamp) ? $this->takestamp->format('d F Y \a\t H:i') : '',
			'takestamp_raw' => isset($this->takestamp) ? $this->takestamp->timestamp : null,
			'updated_at_raw' => isset($this->updated_at) ? $this->updated_at->timestamp : null,
			'license' => $this->license,
			'sizeVariants' => $sizeVariants,
		];
	}

	/**
	 * Returns a front-end friendly array which describes a particular size variant of a media file.
	 *
	 * @param string      $pathPrefix   The prefix (aka directory= where the file is located
	 * @param string|null $baseFileName The base filename
	 * @param int|null    $width        The width of this variant
	 * @param int|null    $height       The height of this variant
	 *
	 * @return array|null An associative array with the following attributes "url", "width" and "height" or null, if
	 *                    any of the parameters is null
	 */
	protected function serializeSizeVariant(string $pathPrefix, ?string $baseFileName, ?int $width, ?int $height): ?array
	{
		return ($width === null || $height === null || $baseFileName === null || $baseFileName === '') ? null : [
			'url' => Storage::url($pathPrefix . '/' . $baseFileName),
			'width' => $width,
			'height' => $height,
		];
	}

	/**
	 * Given a Photo, returns the thumb version.
	 */
	public function toThumb(): Thumb
	{
		$symLinkFunctions = resolve(SymLinkFunctions::class);

		$thumb = new Thumb($this->type, $this->id);
		// maybe refactor?
		$sym = $symLinkFunctions->find($this);
		if ($sym !== null) {
			$thumb->thumb = $sym->get('thumbUrl');
			// default is '' so if thumb2x does not exist we just reply '' which is the behaviour we want
			$thumb->thumb2x = $sym->get('thumb2x');
		} else {
			$thumb->thumb = Storage::url('thumb/' . $this->thumbUrl);
			if ($this->thumb2x == '1') {
				$thumb->set_thumb2x();
			}
		}

		return $thumb;
	}

	/**
	 * Downgrade the quality of the pictures.
	 *
	 * @param array $return
	 */
	public function downgrade(array &$return)
	{
		if (
			$this->isVideo() === false &&
			($return['sizeVariants']['medium2x'] !== null || $return['sizeVariants']['medium'] !== null)
		) {
			$return['url'] = '';
		}
	}
}
