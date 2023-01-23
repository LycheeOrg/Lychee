<?php

namespace App\Http\Controllers;

use App\Actions\Photo\Strategies\RotateStrategy;
use App\Contracts\Exceptions\LycheeException;
use App\Exceptions\ConfigurationException;
use App\Http\Requests\Photo\RotatePhotoRequest;
use App\Http\Resources\Models\PhotoResource;
use App\Models\Configs;
use Illuminate\Routing\Controller;

class PhotoEditorController extends Controller
{
	/**
	 * Given a photoID and a direction (+1: 90° clockwise, -1: 90° counterclockwise) rotate an image.
	 *
	 * @param RotatePhotoRequest $request
	 *
	 * @return PhotoResource
	 *
	 * @throws LycheeException
	 */
	public function rotate(RotatePhotoRequest $request): PhotoResource
	{
		if (!Configs::getValueAsBool('editor_enabled')) {
			throw new ConfigurationException('support for rotation disabled by configuration');
		}

		$rotateStrategy = new RotateStrategy($request->photo(), $request->direction());
		$photo = $rotateStrategy->do();

		return PhotoResource::make($photo);
	}
}
