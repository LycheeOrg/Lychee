<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy;

use App\Exceptions\ConfigurationException;
use App\Exceptions\Internal\QueryBuilderException;
use App\Models\Configs;
use App\Rules\IntegerIDRule;
use App\Rules\RandomIDRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Stuff we need to delete in the future.
 */
final class Legacy
{
	public static function isLegacyModelID(string $id): bool
	{
		$modernIDRule = new RandomIDRule(true);
		$legacyIDRule = new IntegerIDRule(false);

		return !$modernIDRule->passes('id', $id) &&
			$legacyIDRule->passes('id', $id);
	}

	/**
	 * Translates an ID from legacy format to modern format.
	 *
	 * @param int     $id        the legacy ID
	 * @param string  $tableName the table name which should be used to look
	 *                           up the ID; either `photos` or `base_albums`
	 * @param Request $request   the request which triggered the lookup
	 *                           (required for proper logging)
	 *
	 * @return string|null the modern ID
	 *
	 * @throws QueryBuilderException  thrown by the ORM layer in case of error
	 * @throws ConfigurationException thrown, if the translation between
	 *                                legacy and modern IDs is disabled
	 */
	private static function translateLegacyID(int $id, string $tableName, Request $request): ?string
	{
		try {
			$newID = (string) DB::table($tableName)
				->where('legacy_id', '=', intval($id))
				->value('id');

			if ($newID !== '') {
				$referer = strval($request->header('Referer', '(unknown)'));
				$msg = ' Request for ' . $tableName .
					' with legacy ID ' . $id .
					' instead of new ID ' . $newID .
					' from ' . $referer;
				if (!Configs::getValueAsBool('legacy_id_redirection')) {
					// @codeCoverageIgnoreStart
					$msg .= ' (translation disabled by configuration)';
					throw new ConfigurationException($msg);
					// @codeCoverageIgnoreEnd
				}
				Log::warning(__METHOD__ . ':' . __LINE__ . $msg);

				return $newID;
			}

			// @codeCoverageIgnoreStart
			return null;
		} catch (\InvalidArgumentException $e) {
			throw new QueryBuilderException($e);
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Translates an album ID from legacy format to modern format.
	 *
	 * @param int     $albumID the legacy ID
	 * @param Request $request the request which triggered the lookup
	 *                         (required for proper logging)
	 *
	 * @return string|null the modern ID
	 *
	 * @throws QueryBuilderException  thrown by the ORM layer in case of error
	 * @throws ConfigurationException thrown, if the translation between
	 *                                legacy and modern IDs is disabled
	 */
	public static function translateLegacyAlbumID(int $albumID, Request $request): ?string
	{
		return self::translateLegacyID($albumID, 'base_albums', $request);
	}

	/**
	 * Translates a photo ID from legacy format to modern format.
	 *
	 * @param int     $photoID the legacy ID
	 * @param Request $request the request which triggered the lookup
	 *                         (required for proper logging)
	 *
	 * @return string|null the modern ID
	 *
	 * @throws QueryBuilderException  thrown by the ORM layer in case of error
	 * @throws ConfigurationException thrown, if the translation between
	 *                                legacy and modern IDs is disabled
	 */
	public static function translateLegacyPhotoID(int $photoID, Request $request): ?string
	{
		return self::translateLegacyID($photoID, 'photos', $request);
	}
}
