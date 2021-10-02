<?php

namespace App\Http\Controllers;

use App\Actions\Photo\Rotate;
use App\Contracts\LycheeException;
use App\Exceptions\ConfigurationException;
use App\Http\Requests\Photo\RotatePhotoRequest;
use App\Models\Configs;
use App\Models\Photo;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PhotoEditorController extends Controller
{
	/**
	 * Given a photoID and a direction (+1: 90° clockwise, -1: 90° counterclockwise) rotate an image.
	 *
	 * @param RotatePhotoRequest $request
	 * @param Rotate             $rotate
	 *
	 * @return Photo
	 *
	 * @throws LycheeException
	 * @throws ModelNotFoundException
	 */
	public function rotate(RotatePhotoRequest $request, Rotate $rotate): Photo
	{
		if (!Configs::get_value('editor_enabled', '0')) {
			throw new ConfigurationException('support for rotation disabled by configuration');
		}
		/** @var Photo $photo */
		$photo = Photo::query()->findOrFail($request->photoID());

		return $rotate->do($photo, $request->direction());
	}
}
