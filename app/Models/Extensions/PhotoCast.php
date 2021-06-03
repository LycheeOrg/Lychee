<?php

namespace App\Models\Extensions;

use App\ModelFunctions\SymLinkFunctions;
use App\Models\Photo;
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
		return [
			'id' => strval($this->id),
			'title' => $this->title,
			'description' => $this->description,
			'tags' => $this->tags,
			'star' => $this->star,
			'public' => $this->public,
			'album' => $this->album_id !== null ? strval($this->album_id) : null,
			'url' => $this->url,
			'width' => $this->width,
			'height' => $this->height,
			'type' => $this->type,
			'filesize' => $this->filesize,
			'iso' => $this->iso,
			'aperture' => $this->aperture,
			'make' => $this->make,
			'model' => $this->model,
			'shutter' => $this->shutter,
			'focal' => $this->focal,
			'lens' => $this->lens,
			'latitude' => $this->latitude,
			'longitude' => $this->longitude,
			'altitude' => $this->altitude,
			'imgDirection' => $this->imgDirection,
			'location' => $this->location,
			'live_photo_content_id' => $this->live_photo_content_id,
			'live_photo_url' => $this->live_photo_url,
			'created_at' => $this->created_at->format(\DateTimeInterface::ATOM),
			'updated_at' => $this->updated_at->format(\DateTimeInterface::ATOM),
			'taken_at' => (!empty($this->taken_at)) ? $this->taken_at->format(\DateTimeInterface::ATOM) : null,
			'taken_at_orig_tz' => $this->taken_at_orig_tz,
			'license' => $this->license,
			'sizeVariants' => $this->size_variants->jsonSerialize(),
		];
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
			$thumb->thumb = $sym->get(SizeVariant::VARIANT_THUMB);
			// default is '' so if thumb2x does not exist we just reply '' which is the behaviour we want
			$thumb->thumb2x = $sym->get(SizeVariant::VARIANT_THUMB2X);
		} else {
			$thumb->thumb = Storage::url(
				SizeVariant::VARIANT_2_PATH_PREFIX[SizeVariant::VARIANT_THUMB] . '/' . $this->thumb_filename
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
