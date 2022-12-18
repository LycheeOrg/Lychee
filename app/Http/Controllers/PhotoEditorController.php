<?php

namespace App\Http\Controllers;

use App\Actions\Photo\Strategies\RotateStrategy;
use App\Contracts\Exceptions\LycheeException;
use App\Exceptions\ConfigurationException;
use App\Http\Requests\Photo\RotatePhotoRequest;
use App\Models\Configs;
use App\Models\Photo;
use Illuminate\Routing\Controller;

class PhotoEditorController extends Controller
{
	/**
	 * Given a photoID and a direction (+1: 90° clockwise, -1: 90° counterclockwise) rotate an image.
	 *
	 * @param RotatePhotoRequest $request
	 *
	 * @return Photo
	 *
	 * @throws LycheeException
	 */
	public function rotate(RotatePhotoRequest $request): Photo
	{
		if (!Configs::getValueAsBool('editor_enabled')) {
			throw new ConfigurationException('support for rotation disabled by configuration');
		}

		$rotateStrategy = new RotateStrategy($request->photo(), $request->direction());

		return $rotateStrategy->do();
	}
}
