<?php

namespace App\Http\Controllers;

use App\Exceptions\ConfigurationException;
use App\Exceptions\Internal\QueryBuilderException;
use App\Http\Requests\Legacy\TranslateAlbumIDRequest;
use App\Http\Requests\Legacy\TranslatePhotoIDRequest;
use App\Legacy\Legacy;
use Illuminate\Routing\Controller;

/**
 * Class LegacyController.
 *
 * API calls which should not exist. ;-)
 */
class LegacyController extends Controller
{
	/**
	 * Translate an album ID from legacy to modern format.
	 *
	 * @param TranslateAlbumIDRequest $request the request
	 *
	 * @return array{photoID: string} the modern photo ID
	 *
	 * @throws ConfigurationException thrown, if translation is disabled by
	 *                                configuration
	 * @throws QueryBuilderException  thrown by the ORM layer in case of an
	 *                                error
	 */
	public function translateLegacyAlbumID(TranslateAlbumIDRequest $request): array
	{
		return [
			'albumID' => Legacy::translateLegacyAlbumID($request->albumID(), $request),
		];
	}

	/**
	 * Translate a photo ID from legacy to modern format.
	 *
	 * @param TranslatePhotoIDRequest $request the request
	 *
	 * @return array{photoID: string} the modern photo ID
	 *
	 * @throws ConfigurationException thrown, if translation is disabled by
	 *                                configuration
	 * @throws QueryBuilderException  thrown by the ORM layer in case of an
	 *                                error
	 */
	public function translateLegacyPhotoID(TranslatePhotoIDRequest $request): array
	{
		return [
			'photoID' => Legacy::translateLegacyPhotoID($request->photoID(), $request),
		];
	}
}
