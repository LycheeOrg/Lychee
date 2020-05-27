<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\ModelFunctions\PhotoActions;

use App\Album;
use App\ModelFunctions\SymLinkFunctions;
use App\Photo;
use Illuminate\Support\Facades\Storage;

class Cast
{
	/**
	 * Returns album-attributes into a front-end friendly format. Note that some attributes remain unchanged.
	 *
	 * @return array
	 */
	public static function toArray(Photo $photo)
	{
		// TODO: coming soon...
	}

	// TODO: optimize the sym queries
	public static function toThumb(Photo $photo, SymLinkFunctions $symLinkFunctions)
	{
		$thumbs = [];
		$sym = $symLinkFunctions->find($photo);
		if ($sym !== null) {
			$thumbs['thumbs'] = $sym->get('thumbUrl');
			// default is '' so if thumb2x does not exist we just reply '' which is the behaviour we want
			$thumbs['thumbs2x'] = $sym->get('thumb2x');
		} else {
			$thumbs['thumbs'] = Storage::url('thumb/' . $photo->thumbUrl);
			if ($photo->thumb2x == '1') {
				$thumbs['thumbs2x'] = Storage::url('thumb/' . self::ex2x($photo->thumbUrl));
			} else {
				$thumbs['thumbs2x'] = '';
			}
		}
		$thumbs['types'] = $photo->type;
		$thumbs['thumbIDs'] = $photo->id;

		return $thumbs;
	}

	public static function ex2x($url)
	{
		$thumbUrl2x = explode('.', $url);
		$thumbUrl2x = $thumbUrl2x[0] . '@2x.' . $thumbUrl2x[1];

		return $thumbUrl2x;
	}
}
