<?php

namespace App\Http\Controllers\Administration;

use App\Actions\Update\Apply as ApplyUpdate;
use App\Actions\Update\Check as CheckUpdate;
use App\Facades\AccessControl;
use App\Http\Controllers\Controller;
use App\Http\Middleware\Checks\IsMigrated;
use App\Response;
use Exception;
use Illuminate\Http\Request;

/**
 * Class UpdateController.
 */
class UpdateController extends Controller
{
	/**
	 * Return if up to date or the number of commits behind
	 * This invalidates the cache for the url.
	 *
	 * @return string
	 */
	public function check(CheckUpdate $checkUpdate)
	{
		try {
			return Response::json($checkUpdate->getText());
			// @codeCoverageIgnoreStart
		} catch (Exception $e) {
			return Response::error($e->getMessage()); // Not master
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * This requires a php to have a shell access.
	 * This method execute the update (git pull).
	 *
	 * @return array|string
	 */
	public function apply(CheckUpdate $checkUpdate, ApplyUpdate $applyUpdate)
	{
		try {
			$checkUpdate->canUpdate();
			// @codeCoverageIgnoreStart
		} catch (Exception $e) {
			return Response::error($e->getMessage());
		}
		// @codeCoverageIgnoreEnd

		// @codeCoverageIgnoreStart
		return $applyUpdate->run();
	}

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
