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
		$modern_id_rule = new RandomIDRule(true);
		$legacy_id_rule = new IntegerIDRule(false);

		return !$modern_id_rule->passes('id', $id) &&
			$legacy_id_rule->passes('id', $id);
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
	private static function translateLegacyID(int $id, string $table_name, Request $request): ?string
	{
		try {
			$new_id = (string) DB::table($table_name)
				->where('legacy_id', '=', intval($id))
				->value('id');

			if ($new_id !== '') {
				$referer = strval($request->header('Referer', '(unknown)'));
				$msg = ' Request for ' . $table_name .
					' with legacy ID ' . $id .
					' instead of new ID ' . $new_id .
					' from ' . $referer;
				if (!Configs::getValueAsBool('legacy_id_redirection')) {
					// @codeCoverageIgnoreStart
					$msg .= ' (translation disabled by configuration)';
					throw new ConfigurationException($msg);
					// @codeCoverageIgnoreEnd
				}
				Log::warning(__METHOD__ . ':' . __LINE__ . $msg);

				return $new_id;
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
	public static function translateLegacyAlbumID(int $album_id, Request $request): ?string
	{
		return self::translateLegacyID($album_id, 'base_albums', $request);
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
	public static function translateLegacyPhotoID(int $photo_id, Request $request): ?string
	{
		return self::translateLegacyID($photo_id, 'photos', $request);
	}
}
