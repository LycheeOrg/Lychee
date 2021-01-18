<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers;

use App\Actions\Photo\Rotate;
use App\Http\Requests\PhotoRequests\PhotoIDRequest;
use App\Models\Configs;
use App\Models\Photo;

class PhotoEditorController extends Controller
{
	/**
	 * Given a photoID and a direction (+1: 90° clockwise, -1: 90° counterclockwise) rotate an image.
	 *
	 * @param PhotoIDRequest $request
	 *
	 * @return tring
	 */
	public function rotate(PhotoIDRequest $request, Rotate $rotate)
	{
		if (!Configs::get_value('editor_enabled', '0')) {
			return 'false';
		}

		$request->validate(['direction' => 'integer|required']);

		$photo = Photo::findOrFail($request['photoID']);

		return $rotate->do($photo, intval($request['direction'])) ? 'true' : 'false';
	}
}
