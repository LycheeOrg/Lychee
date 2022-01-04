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
	public function translateLegacyModelIDs(Request $request): array
	{
		$request->validate([
			'albumID' => 'sometimes|required_without:photoID|integer',
			'photoID' => 'sometimes|required_without:albumID|integer',
		]);
		/** @var int $legacyAlbumID */
		$legacyAlbumID = $request->get('albumID', 0);
		/** @var int $legacyPhotoID */
		$legacyPhotoID = $request->get('photoID', 0);

		$return = [];
		if ($legacyAlbumID !== 0) {
			$return['albumID'] = Legacy::isLegacyModelID($legacyAlbumID) ?
				Legacy::translateLegacyAlbumID($legacyAlbumID, $request) :
				null;
		}
		if ($legacyPhotoID !== 0) {
			$return['photoID'] = Legacy::isLegacyModelID($legacyPhotoID) ?
				Legacy::translateLegacyPhotoID($legacyPhotoID, $request) :
				null;
		}

		return $return;
	}
}
