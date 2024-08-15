<?php

namespace App\Http\Controllers\Gallery;

use App\Http\Requests\Photo\GetPhotoRequest;
use App\Http\Resources\Models\PhotoResource;
use Illuminate\Routing\Controller;

/**
 * Controller responsible for fetching Photo Data.
 */
class PhotoController extends Controller
{
	/**
	 * Provided an albumID, returns the album.
	 *
	 * @param GetPhotoRequest $request
	 *
	 * @return PhotoResource
	 */
	public function get(GetPhotoRequest $request): PhotoResource
	{
		return new PhotoResource($request->photo());
	}

	public function upload(): void
	{
	}
}