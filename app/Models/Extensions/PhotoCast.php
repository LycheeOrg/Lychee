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
		if ($this->isVideo()) {
			$filename = $this->thumbUrl;
		} elseif ($this->type == 'raw') {
			// It's a raw file -> we also use jpeg as extension
			$filename = $this->thumbUrl;
		} else {
			$filename = $this->url;
		}
		$filename2x = ($filename !== '') ? Helpers::ex2x($filename) : '';
		$thumbFileName2x = $this->thumb2x === '1' ? Helpers::ex2x($this->thumbUrl) : null;

		// The original size is not stored in this sub-array but on the root level of the JSON response
		// TODO: Maybe harmonize and put original variant into this array, too? This would also avoid an ugly if branch in SymLink#override.
		$sizeVariants = [
			Photo::VARIANT_THUMB => $this->serializeSizeVariant(
				Photo::VARIANT_THUMB, $this->thumbUrl, Photo::THUMBNAIL_DIM, Photo::THUMBNAIL_DIM
			),
			Photo::VARIANT_THUMB2X => $this->serializeSizeVariant(
				Photo::VARIANT_THUMB2X, $thumbFileName2x, Photo::THUMBNAIL2X_DIM, Photo::THUMBNAIL2X_DIM
			),
			Photo::VARIANT_SMALL => $this->serializeSizeVariant(
				Photo::VARIANT_SMALL, $filename, $this->small_width, $this->small_height
			),
			Photo::VARIANT_SMALL2X => $this->serializeSizeVariant(
				Photo::VARIANT_SMALL2X, $filename2x, $this->small2x_width, $this->small2x_height
			),
			Photo::VARIANT_MEDIUM => $this->serializeSizeVariant(
				Photo::VARIANT_MEDIUM, $filename, $this->medium_width, $this->medium_height
			),
			Photo::VARIANT_MEDIUM2X => $this->serializeSizeVariant(
				Photo::VARIANT_MEDIUM2X, $filename2x, $this->medium2x_width, $this->medium2x_height
			),
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
			'width' => $this->width !== null ? $this->width : 0,
			'height' => $this->height !== null ? $this->height : 0,
			'type' => $this->type,
			'filesize' => $this->filesize,
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
			'created_at' => $this->created_at->toJSON(),
			'updated_at' => $this->updated_at->toJSON(),
			'takestamp' => (isset($this->takestamp) && $this->takestamp != null) ? $this->takestamp->toJSON() : null,
			'license' => $this->license,
			'sizeVariants' => $sizeVariants,
		];
	}

	/**
	 * Returns a front-end friendly array which describes a particular size variant of a media file.
	 *
	 * @param string      $sizeVariant The name of the size variant which is being serialized; used to determine the correct path prefix
	 * @param string|null $fileName    The filename
	 * @param int|null    $width       The width of this variant
	 * @param int|null    $height      The height of this variant
	 *
	 * @return array|null An associative array with the following attributes "url", "width" and "height" or null, if
	 *                    any of the parameters is null
	 */
	protected function serializeSizeVariant(string $sizeVariant, ?string $fileName, ?int $width, ?int $height): ?array
	{
		if ($width === null || $height === null || $fileName === null || $fileName === '') {
			return null;
		} else {
			return [
				'url' => Storage::url(Photo::VARIANT_2_PATH_PREFIX[$sizeVariant] . '/' . $fileName),
				'width' => $width,
				'height' => $height,
			];
		}
	}

	/**
	 * Given a Photo, returns the thumb version.
	 */
	public function toThumb(): Thumb
	{
		/* @var $symLinkFunctions ?SymLinkFunctions */
		$symLinkFunctions = resolve(SymLinkFunctions::class);

		$thumb = new Thumb($this->type, $this->id);
		// maybe refactor?
		$sym = $symLinkFunctions->find($this);
		if ($sym !== null) {
			$thumb->thumb = $sym->get(Photo::VARIANT_THUMB);
			// default is '' so if thumb2x does not exist we just reply '' which is the behaviour we want
			$thumb->thumb2x = $sym->get(Photo::VARIANT_THUMB2X);
		} else {
			$thumb->thumb = Storage::url(
				Photo::VARIANT_2_PATH_PREFIX[Photo::VARIANT_THUMB] . '/' . $this->thumbUrl
			);
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
