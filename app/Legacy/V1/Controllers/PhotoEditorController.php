<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Controllers;

use App\Actions\Photo\Rotate;
use App\Contracts\Exceptions\LycheeException;
use App\Exceptions\ConfigurationException;
use App\Legacy\V1\Requests\Photo\RotatePhotoRequest;
use App\Legacy\V1\Resources\Models\PhotoResource;
use App\Models\Configs;
use Illuminate\Routing\Controller;

final class PhotoEditorController extends Controller
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

		$rotateStrategy = new Rotate($request->photo(), $request->direction());
		$photo = $rotateStrategy->do();

		return PhotoResource::make($photo);
	}
}
