<?php

namespace App\Http\Controllers\Administration;

use App\Actions\Update\Apply as ApplyUpdate;
use App\Actions\Update\Check as CheckUpdate;
use App\Contracts\LycheeException;
use App\Exceptions\VersionControlException;
use App\Facades\AccessControl;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
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
		$this->checkUpdate->assertUpdatability();

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
		$this->checkUpdate->assertUpdatability();
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
	 * uses {@link \App\Actions\Install\ApplyMigration::migrate()}.
	 * However, both methods are very similar, too.
	 * The whole code around installation/upgrade/migration should
	 * thoroughly be revised an refactored.
	 */
	public function migrate(Request $request): View
	{
		if (
			AccessControl::is_admin() || AccessControl::noLogin() ||
			AccessControl::log_as_admin($request['username'] ?? '', $request['password'] ?? '', $request->ip())
		) {
			$output = [];
			$this->applyUpdate->migrate($output);
			$this->applyUpdate->filter($output);

			if (AccessControl::noLogin()) {
				AccessControl::logout();
			}

			return view('update.results', ['code' => '200', 'message' => 'Migration results', 'output' => $output]);
		} else {
			return view('update.error', ['code' => '403', 'message' => 'Incorrect username or password']);
		}
	}
}
