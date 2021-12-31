<?php

namespace App\Legacy;

use App\Models\Configs;
use App\Models\Logs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

/**
 * Stuff we need to delete in the future.
 */
class Legacy
{
	public static function resetAdmin(): void
	{
		Configs::where('key', '=', 'username')->orWhere('key', '=', 'password')->update(['value' => '']);
	}

	public static function SetPassword($request)
	{
		$configs = Configs::get();
		if (Configs::get('version', '040000') < '040008') {
			if ($configs['password'] === '' && $configs['username'] === '') {
				Configs::set('username', bcrypt($request['username']));
				Configs::set('password', bcrypt($request['password']));

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
		return filter_var($id, FILTER_VALIDATE_INT) !== false;
	}

	public static function isRandomModelID(string $id): bool
	{
		return preg_match('/^[-_a-zA-Z0-9]{24}$/', $id) === 1;
	}

	private static function translateLegacyID(string $id, string $tableName, Request $request): ?string
	{
		if (!self::isLegacyModelID($id)) {
			return null;
		}

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
				$newID = null;
			}
			Logs::warning(__METHOD__, __LINE__, $msg);
		}

		return $newID;
	}

	public static function translateLegacyAlbumID(string $albumID, Request $request): ?string
	{
		return self::translateLegacyID($albumID, 'base_albums', $request);
	}

	public static function translateLegacyPhotoID(string $photoID, Request $request): ?string
	{
		return self::translateLegacyID($photoID, 'photos', $request);
	}
}
