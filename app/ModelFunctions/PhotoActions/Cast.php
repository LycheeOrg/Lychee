<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\ModelFunctions\PhotoActions;

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
