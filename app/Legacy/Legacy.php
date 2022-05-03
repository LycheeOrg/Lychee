<?php

namespace App\Legacy;

use App\Exceptions\ConfigurationException;
use App\Exceptions\Internal\InvalidConfigOption;
use App\Exceptions\Internal\QueryBuilderException;
use App\Models\Configs;
use App\Models\Logs;
use App\Rules\IntegerIDRule;
use App\Rules\RandomIDRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

/**
 * Stuff we need to delete in the future.
 */
class Legacy
{
	/**
	 * @throws QueryBuilderException
	 */
	public static function resetAdmin(): void
	{
		Configs::query()
			->where('key', '=', 'username')
			->orWhere('key', '=', 'password')
			->update(['value' => '']);
	}

	/**
	 * @throws InvalidConfigOption
	 */
	public static function SetPassword(string $hashedUsername, string $hashedPassword): bool
	{
		$configs = Configs::get();

		if (Configs::get_value('version', '040000') < '040008') {
			if ($configs['password'] === '' && $configs['username'] === '') {
				Configs::set('username', $hashedUsername);
				Configs::set('password', $hashedPassword);

				return true;
			}
		}

		return false;
	}

	public static function noLogin(): bool
	{
		// LEGACY STUFF
		$configs = Configs::get();

		if (Configs::get_value('version', '040000') <= '040008') {
			// Check if login credentials exist and login if they don't
			if (
				isset($configs['username']) && $configs['username'] === '' &&
				isset($configs['password']) && $configs['password'] === ''
			) {
				Session::put('login', true);
				Session::put('UserID', 0);

				return true;
			}
		}

		return false;
	}

	public static function log_as_admin(string $username, string $password, string $ip): bool
	{
		$configs = Configs::get();

		if (Hash::check($username, $configs['username']) && Hash::check($password, $configs['password'])) {
			Session::put('login', true);
			Session::put('UserID', 0);
			Logs::notice(__METHOD__, __LINE__, 'User (' . $username . ') has logged in from ' . $ip . ' (legacy)');

			return true;
		}

		return false;
	}

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
			$newID = DB::table($tableName)
				->where('legacy_id', '=', intval($id))
				->value('id');

			if ($newID) {
				$referer = $request->header('Referer', '(unknown)');
				$msg = 'Request for ' . $tableName .
					' with legacy ID ' . $id .
					' instead of new ID ' . $newID .
					' from ' . $referer;
				if (Configs::get_value('legacy_id_redirection', '0') !== '1') {
					$msg .= ' (translation disabled by configuration)';
					throw new ConfigurationException($msg);
				}
				Logs::warning(__METHOD__, __LINE__, $msg);
			}

			return $newID;
		} catch (\InvalidArgumentException $e) {
			throw new QueryBuilderException($e);
		}
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
