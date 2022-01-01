<?php

namespace App\Http\Controllers;

use App\Legacy\Legacy;
use Illuminate\Http\Request;

/**
 * Class LegacyController.
 *
 * API calls which should not exist. ;-)
 */
class LegacyController extends Controller
{
	public function translateLegacyAlbumID(Request $request): array
	{
		$legacyAlbumID = ($request->validate(['albumID' => 'required|integer']))['albumID'];

		return [
			'albumID' => Legacy::isLegacyModelID($legacyAlbumID) ?
				Legacy::translateLegacyAlbumID($legacyAlbumID, $request) :
				null,
		];
	}

	public function translateLegacyPhotoID(Request $request): array
	{
		$legacyPhotoID = ($request->validate(['photoID' => 'required|integer']))['photoID'];

		return [
			'photoID' => Legacy::isLegacyModelID($legacyPhotoID) ?
				Legacy::translateLegacyPhotoID($legacyPhotoID, $request) :
				null,
		];
	}
}
