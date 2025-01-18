<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Controllers;

use App\Exceptions\ConfigurationException;
use App\Exceptions\Internal\QueryBuilderException;
use App\Legacy\Legacy;
use App\Legacy\V1\Requests\Legacy\TranslateIDRequest;
use Illuminate\Routing\Controller;

/**
 * Class LegacyController.
 *
 * API calls which should not exist. ;-)
 */
final class LegacyController extends Controller
{
	/**
	 * Translates IDs from legacy to modern format.
	 *
	 * @param TranslateIDRequest $request the request
	 *
	 * @return array{albumID: ?string, photoID: ?string} the modern IDs
	 *
	 * @throws ConfigurationException thrown, if translation is disabled by
	 *                                configuration
	 * @throws QueryBuilderException  thrown by the ORM layer in case of an
	 *                                error
	 */
	public function translateLegacyModelIDs(TranslateIDRequest $request): array
	{
		$legacyAlbumID = $request->albumID();
		$legacyPhotoID = $request->photoID();

		$return = ['albumID' => null, 'photoID' => null];
		if ($legacyAlbumID !== null) {
			$return['albumID'] = Legacy::translateLegacyAlbumID($request->albumID(), $request);
		}
		if ($legacyPhotoID !== null) {
			$return['photoID'] = Legacy::translateLegacyPhotoID($legacyPhotoID, $request);
		}

		return $return;
	}
}
