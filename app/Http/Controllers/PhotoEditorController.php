<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers;

use App\Actions\Photo\Rotate;
use App\Http\Requests\PhotoRequests\PhotoIDRequest;
use App\Models\Configs;
use App\Models\Photo;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class PhotoEditorController extends Controller
{
	/**
	 * Given a photoID and a direction (+1: 90° clockwise, -1: 90° counterclockwise) rotate an image.
	 *
	 * @param PhotoIDRequest $request
	 * @param Rotate         $rotate
	 *
	 * @return Photo
	 */
	public function rotate(PhotoIDRequest $request, Rotate $rotate): Photo
	{
		if (!Configs::get_value('editor_enabled', '0')) {
			throw new UnprocessableEntityHttpException('support for rotation disabled by configuration');
		}
		$request->validate([
			'direction' => [
				'integer',
				'required',
				Rule::in([-1, 1]),
			],
		]);
		/** @var Photo $photo */
		$photo = Photo::query()->findOrFail($request['photoID']);

		return $rotate->do($photo, intval($request['direction']));
	}
}
