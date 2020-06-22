<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\ModelFunctions\PhotoActions;

use App\Assets\Helpers;
use App\Configs;
use App\ModelFunctions\SymLinkFunctions;
use App\Photo;
use Illuminate\Support\Facades\Storage;

class Cast
{
	/**
	 * Returns photo-attributes into a front-end friendly format. Note that some attributes remain unchanged.
	 *
	 * @return array
	 */
	public static function toArray(Photo $photo)
	{
		return [
			'id' => strval($photo->id),
			'title' => $photo->title,
			'description' => $photo->description == null ? '' : $photo->description,
			'tags' => $photo->tags,
			'star' => Helpers::str_of_bool($photo->star),
			'album' => $photo->album_id !== null ? strval($photo->album_id) : null,
			'width' => strval($photo->width),
			'height' => strval($photo->height),
			'type' => $photo->type,
			'size' => $photo->size,
			'iso' => $photo->iso,
			'aperture' => $photo->aperture,
			'make' => $photo->make,
			'model' => $photo->model,
			'shutter' => $photo->get_shutter_str(),
			'focal' => $photo->focal,
			'lens' => $photo->lens,
			'latitude' => $photo->latitude,
			'longitude' => $photo->longitude,
			'altitude' => $photo->altitude,
			'imgDirection' => $photo->imgDirection,
			'location' => $photo->location,
			'livePhotoContentID' => $photo->livePhotoContentID,

			'sysdate' => $photo->created_at->format('d F Y'),
			'takedate' => isset($photo->takestamp) ? $photo->takestamp->format('d F Y \a\t H:i') : '',
			'license' => $photo->license,
		];
	}

	public static function print_license(array &$return, string $license)
	{
		if ($return['license'] == 'none') {
			if ($license != 'none') {
				$return['license'] = $license;
			} else {
				$return['license'] = Configs::get_value('default_license');
			}
		}
	}

	/**
	 * Given a photo, return the proper URL.
	 */
	public static function urls(array &$return, Photo $photo_model): void
	{
		// if this is a video
		if (strpos($photo_model->type, 'video') === 0) {
			$photoUrl = $photo_model->thumbUrl;

			// We need to format the framerate (stored as focal) -> max 2 decimal digits
			$return['focal'] = round($return['focal'], 2);
		} elseif ($photo_model->type == 'raw') {
			// It's a raw file -> we also use jpeg as extension
			$photoUrl = $photo_model->thumbUrl;
		} else {
			$photoUrl = $photo_model->url;
		}
		$photoUrl2x = '';
		if ($photoUrl !== '') {
			$photoUrl2x = explode('.', $photoUrl);
			$photoUrl2x = $photoUrl2x[0] . '@2x.' . $photoUrl2x[1];
		}

		// Parse medium
		if ($photo_model->medium != '') {
			$return['medium'] = Storage::url('medium/' . $photoUrl);
			$return['medium_dim'] = $photo_model->medium;
		} else {
			$return['medium'] = '';
			$return['medium_dim'] = '';
		}

		if ($photo_model->medium2x != '') {
			$return['medium2x'] = Storage::url('medium/' . $photoUrl2x);
			$return['medium2x_dim'] = $photo_model->medium2x;
		} else {
			$return['medium2x'] = '';
			$return['medium2x_dim'] = '';
		}

		if ($photo_model->small != '') {
			$return['small'] = Storage::url('small/' . $photoUrl);
			$return['small_dim'] = $photo_model->small;
		} else {
			$return['small'] = '';
			$return['small_dim'] = '';
		}

		if ($photo_model->small2x != '') {
			$return['small2x'] = Storage::url('small/' . $photoUrl2x);
			$return['small2x_dim'] = $photo_model->small2x;
		} else {
			$return['small2x'] = '';
			$return['small2x_dim'] = '';
		}

		// Parse paths
		$return['thumbUrl'] = Storage::url('thumb/' . $photo_model->thumbUrl);

		if ($photo_model->thumb2x == '1') {
			$thumbUrl2x = explode('.', $photo_model->thumbUrl);
			$thumbUrl2x = $thumbUrl2x[0] . '@2x.' . $thumbUrl2x[1];
			$return['thumb2x'] = Storage::url('thumb/' . $thumbUrl2x);
		} else {
			$return['thumb2x'] = '';
		}

		$path_prefix = $photo_model->type == 'raw' ? 'raw/' : 'big/';
		$return['url'] = Storage::url($path_prefix . $photo_model->url);

		if ($photo_model->livePhotoUrl !== '' && $photo_model->livePhotoUrl !== null) {
			$return['livePhotoUrl'] = Storage::url('big/' . $photo_model->livePhotoUrl);
		} else {
			$return['livePhotoUrl'] = null;
		}
	}

	/**
	 * Given a Photo, returns the thumb version.
	 */
	public static function toThumb(Photo $photo, SymLinkFunctions $symLinkFunctions): Thumb
	{
		$thumb = new Thumb($photo->type, $photo->id);
		$sym = $symLinkFunctions->find($photo);
		if ($sym !== null) {
			$thumb->thumb = $sym->get('thumbUrl');
			// default is '' so if thumb2x does not exist we just reply '' which is the behaviour we want
			$thumb->thumb2x = $sym->get('thumb2x');
		} else {
			$thumb->thumb = Storage::url('thumb/' . $photo->thumbUrl);
			if ($photo->thumb2x == '1') {
				$thumb->set_thumb2x();
			}
		}

		return $thumb;
	}
}
