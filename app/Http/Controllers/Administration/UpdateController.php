<?php

namespace App\Http\Controllers\Administration;

use App\Actions\Diagnostics\Pipes\Checks\UpdatableCheck;
use App\Actions\InstallUpdate\Apply as ApplyUpdate;
use App\Actions\InstallUpdate\Check as CheckUpdate;
use App\Contracts\LycheeException;
use App\Exceptions\VersionControlException;
use App\Legacy\AdminAuthentication;
use App\Policies\UserPolicy;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

/**
 * Class UpdateController.
 *
 * Most likely, this controller should be liquidated and its methods become
 * integrated into the controllers below `App\Http\Controllers\Install\`
 * as far as the methods are not already covered.
 *
 * For example, {@link UpdateController::migrate()} serves a similar purpose
 * as {@link \App\Http\Controllers\Install\MigrationController::view()}.
 * An initial installation and an upgrade share many similar steps.
 * There is no (technical) need why the DB migration after an upgrade and
 * the DB migration after an initial installation should be distinct.
 *
 * For example, we check that the server meets the requirements during
 * an initial installation
 * (see {@link \App\Http\Controllers\Install\RequirementsController}),
 * but we don't check if the server meets the requirements before an upgrade.
 * This has already raised some bug reports.
 *
 * The main obstacle of such a refactoring is that the individual steps of
 * an installation are not properly decomposed such that they are not
 * generally usable.
 * For example,
 * {@link \App\Http\Controllers\Install\MigrationController::view()}
 * does not only migrate the DB (as the name suggests),
 * but also generates a new API key.
 * However, the latter is only necessary for an initial installation.
 *
 * TODO: Revise and refactor the whole logic around installation/upgrade/migration.
 */
class UpdateController extends Controller
{
	protected CheckUpdate $checkUpdate;
	protected ApplyUpdate $applyUpdate;

	public function __construct(CheckUpdate $checkUpdate, ApplyUpdate $applyUpdate)
	{
		$this->checkUpdate = $checkUpdate;
		$this->applyUpdate = $applyUpdate;
	}

	/**
	 * Return if up to date or the number of commits behind
	 * This invalidates the cache for the url.
	 *
	 * @return array{updateStatus: string}
	 *
	 * @throws VersionControlException
	 */
	public function check(): array
	{
		return ['updateStatus' => $this->checkUpdate->getText()];
	}

	/**
	 * Updates Lychee and returns the messages as a JSON object.
	 *
	 * The method requires PHP to have shell access.
	 * Except for the return type this method is identical to
	 * {@link UpdateController::view()}.
	 *
	 * @return array{updateMsgs: array<string>}
	 *
	 * @throws LycheeException
	 */
	public function apply(): array
	{
		UpdatableCheck::assertUpdatability();

		return ['updateMsgs' => $this->applyUpdate->run()];
	}

	/**
	 * Updates Lychee and returns the messages as an HTML view.
	 *
	 * The method requires PHP to have shell access.
	 * Except for the return type this method is identical to
	 * {@link UpdateController::apply()}.
	 *
	 * @return View
	 *
	 * @throws LycheeException
	 */
	public function view(): View
	{
		UpdatableCheck::assertUpdatability();

		$output = $this->applyUpdate->run();

		return view('update.results', ['code' => '200', 'message' => 'Upgrade results', 'output' => $output]);
	}

	/**
	 * Migrates the Lychee DB and returns a HTML view.
	 *
	 * **TODO:** Consolidate with {@link \App\Http\Controllers\Install\MigrationController::view()}.
	 *
	 * **ATTENTION:** This method serves a somewhat similar purpose as
	 * `MigrationController::view()` except that the latter does not only
	 * trigger a migration, but also generates a new API key.
	 * Also note, that this method internally uses
	 * {@link ApplyUpdate::migrate()} while `MigrationController::view`
	 * uses {@link \App\Actions\InstallUpdate\ApplyMigration::migrate()}.
	 * However, both methods are very similar, too.
	 * The whole code around installation/upgrade/migration should
	 * thoroughly be revised an refactored.
	 */
	public function migrate(Request $request): View|Response
	{
		// This conditional code makes use of lazy boolean evaluation: a || b does not execute b if a is true.
		// 1. Check whether the user is already logged in properly
		// 2. Check if the admin user is registered and login as admin, if not
		// 3. Attempt to login as an admin user using the legacy method: hash(username) + hash(password).
		// 4. Try to login the normal way.
		//
		// TODO: Step 2 will become unnecessary once admin registration has become part of the installation routine; after that the case that no admin is registered cannot occur anymore
		// TODO: Step 3 will become unnecessary once the admin user of any existing installation has at least logged in once and the admin user has therewith migrated to use a non-hashed user name
		$isLoggedIn = Auth::check();
		$isLoggedIn = $isLoggedIn || AdminAuthentication::loginAsAdminIfNotRegistered();
		$isLoggedIn = $isLoggedIn || AdminAuthentication::loginAsAdmin($request->input('username', ''), $request->input('password', ''), $request->ip());
		$isLoggedIn = $isLoggedIn || Auth::attempt(['username' => $request->input('username', ''), 'password' => $request->input('password', '')]);

		// Check if logged in AND is admin
		if (Gate::check(UserPolicy::IS_ADMIN)) {
			$output = [];
			$this->applyUpdate->migrate($output);
			$this->applyUpdate->filter($output);

			if (AdminAuthentication::isAdminNotRegistered()) {
				Auth::logout();
				Session::flush();
			}

			return view('update.results', ['code' => '200', 'message' => 'Migration results', 'output' => $output]);
		}

		// Rather than returning a view directly (which implies code 200, we use response in order to ensure code 403)
		return response()->view('update.error', ['code' => '403', 'message' => 'Incorrect username or password'], 403);
	}
}
