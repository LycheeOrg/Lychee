<?php

namespace App\Http\Controllers\Administration;

use App\Actions\Update\Apply as ApplyUpdate;
use App\Actions\Update\Check as CheckUpdate;
use App\Contracts\LycheeException;
use App\Exceptions\VersionControlException;
use App\Facades\AccessControl;
use App\Http\Middleware\Checks\IsMigrated;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Class UpdateController.
 */
class UpdateController extends Controller
{
	/**
	 * Return if up to date or the number of commits behind
	 * This invalidates the cache for the url.
	 *
	 * @param CheckUpdate $checkUpdate
	 *
	 * @return array{updateStatus: string}
	 *
	 * @throws VersionControlException
	 */
	public function check(CheckUpdate $checkUpdate): array
	{
		return ['updateStatus' => $checkUpdate->getText()];
	}

	/**
	 * This requires PHP to have shell access.
	 * This method executes the update (git pull).
	 *
	 * @param CheckUpdate $checkUpdate
	 * @param ApplyUpdate $applyUpdate
	 *
	 * @return array{updateMsgs: array<string>}
	 *
	 * @throws LycheeException
	 */
	public function apply(CheckUpdate $checkUpdate, ApplyUpdate $applyUpdate): array
	{
		$checkUpdate->assertUpdatability();

		return ['updateMsgs' => $applyUpdate->run()];
	}

	/**
	 * Who calls this method under what circumstances?
	 * It does not seem to be used by the front-end.
	 * Why do we check for admin authentication explicitly here?
	 *
	 * TODO: Clean up this method and also return a consistent return type/value after we figured out under what circumstances this method is called.
	 */
	public function force(Request $request, IsMigrated $isMigrated, ApplyUpdate $applyUpdate)
	{
		if ($isMigrated->assert()) {
			return redirect()->route('home');
		}

		if (
			AccessControl::is_admin() || AccessControl::noLogin() ||
			AccessControl::log_as_admin($request['username'] ?? '', $request['password'] ?? '', $request->ip())
		) {
			$output = [];
			$applyUpdate->artisan($output);
			$applyUpdate->filter($output);

			if (AccessControl::noLogin()) {
				AccessControl::logout();
			}

			return '<pre>' . implode("\n", $output) . '</pre>';
		} else {
			return view('error.update', ['code' => '403', 'message' => 'Incorrect username or password']);
		}
	}
}
