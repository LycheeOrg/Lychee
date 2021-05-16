<?php

namespace App\Models\Extensions;

use App\ModelFunctions\SymLinkFunctions;
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
		return [
			'id' => strval($this->id),
			'title' => $this->title,
			'description' => $this->description == null ? '' : $this->description,
			'tags' => $this->tags,
			'star' => Helpers::str_of_bool($this->star),
			'public' => $this->get_public(),
			'album' => $this->album_id !== null ? strval($this->album_id) : null,
			'width' => strval($this->width),
			'width_raw' => $this->width !== null ? $this->width : -1,
			'height' => strval($this->height),
			'height_raw' => $this->height !== null ? $this->height : -1,
			'type' => $this->type,
			'filesize' => $this->filesize,
			'iso' => $this->iso,
			'aperture' => $this->aperture,
			'make' => $this->make,
			'model' => $this->model,
			'shutter' => $this->get_shutter_str(),
			'focal' => $this->focal,
			'lens' => $this->lens,
			'latitude' => $this->latitude,
			'longitude' => $this->longitude,
			'altitude' => $this->altitude,
			'imgDirection' => $this->imgDirection,
			'location' => $this->location,
			'livePhotoContentID' => $this->livePhotoContentID,

			'sysdate' => $this->created_at->format('d F Y \a\t H:i'),
			'created_at_raw' => $this->created_at->timestamp,
			'takedate' => isset($this->takestamp) ? $this->takestamp->format('d F Y \a\t H:i') : '',
			'takestamp_raw' => isset($this->takestamp) ? $this->takestamp->timestamp : null,
			'updated_at_raw' => isset($this->updated_at) ? $this->updated_at->timestamp : null,
			'license' => $this->license,
		];
	}

	/**
	 * ! how is this different than Cast::to_array ?
	 * Returns photo-attributes into a front-end friendly format. Note that some attributes remain unchanged.
	 *
	 * @return array returns photo-attributes in a normalized structure
	 */
	public function prepareLocationData()
	{
		// Init
		$photo = [];

		// Set unchanged attributes
		$photo['id'] = strval($this->id);
		$photo['title'] = $this->title;
		$photo['album'] = $this->album_id !== null ? strval($this->album_id) : null;
		$photo['latitude'] = $this->latitude;
		$photo['longitude'] = $this->longitude;

		// if this is a video
		if (strpos($this->type, 'video') === 0) {
			$photoUrl = $this->thumbUrl;
		} else {
			$photoUrl = $this->url;
		}

		$photoUrl2x = '';
		if ($photoUrl !== '') {
			$photoUrl2x = Helpers::ex2x($photoUrl);
		}

		if ($this->small != '') {
			$photo['small'] = Storage::url('small/' . $photoUrl);
		} else {
			$photo['small'] = '';
		}

		if ($this->small2x != '') {
			$photo['small2x'] = Storage::url('small/' . $photoUrl2x);
		} else {
			$photo['small2x'] = '';
		}

		// Parse paths
		$photo['thumbUrl'] = Storage::url('thumb/' . $this->thumbUrl);

		if ($this->thumb2x == '1') {
			$thumbUrl2x = Helpers::ex2x($this->thumbUrl);
			$photo['thumb2x'] = Storage::url('thumb/' . $thumbUrl2x);
		} else {
			$photo['thumb2x'] = '';
		}

		$path_prefix = $this->type == 'raw' ? 'raw/' : 'big/';
		$photo['url'] = Storage::url($path_prefix . $this->url);

		if (isset($this->takestamp) && $this->takestamp != null) {
			// Use takestamp
			$photo['takedate'] = $this->takestamp->format('d F Y \a\t H:i');
		} else {
			$photo['takedate'] = '';
		}

		return $photo;
	}

	/**
	 * Given a photo, return the proper URL.
	 */
	public function urls(array &$return): void
	{
		// if this is a video
		if (strpos($this->type, 'video') === 0) {
			$photoUrl = $this->thumbUrl;

			// We need to format the framerate (stored as focal) -> max 2 decimal digits
			$return['focal'] = round($return['focal'], 2);
		} elseif ($this->type == 'raw') {
			// It's a raw file -> we also use jpeg as extension
			$photoUrl = $this->thumbUrl;
		} else {
			$photoUrl = $this->url;
		}
		$photoUrl2x = ($photoUrl !== '') ? Helpers::ex2x($photoUrl) : '';

		$sizes = [
			'medium' => $photoUrl,
			'medium2x' => $photoUrl2x,
			'small' => $photoUrl,
			'small2x' => $photoUrl2x,
		];

		foreach ($sizes as $size => $url) {
			if ($this->$size != '') {
				$return[$size] = Storage::url(str_replace('2x', '', $size) . '/' . $url);
				$return[$size . '_dim'] = $this->$size;
			} else {
				$return[$size] = '';
				$return[$size . '_dim'] = '';
			}
		}

		// Parse paths
		$return['thumbUrl'] = Storage::url('thumb/' . $this->thumbUrl);
		$return['thumb2x'] = ($this->thumb2x == '1') ? Storage::url('thumb/' . Helpers::ex2x($this->thumbUrl)) : '';

		$path_prefix = ($this->type == 'raw') ? 'raw/' : 'big/';
		$return['url'] = Storage::url($path_prefix . $this->url);

		if ($this->livePhotoUrl !== '' && $this->livePhotoUrl !== null) {
			$return['livePhotoUrl'] = Storage::url('big/' . $this->livePhotoUrl);
		} else {
			$return['livePhotoUrl'] = null;
		}
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
			($return['medium2x'] != '' || $return['medium'] != '')
		) {
			$return['url'] = '';
		}
	}
}
