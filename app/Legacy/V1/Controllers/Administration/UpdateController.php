<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Controllers\Administration;

use App\Actions\Diagnostics\Pipes\Checks\UpdatableCheck;
use App\Actions\InstallUpdate\ApplyUpdate;
use App\Contracts\Exceptions\LycheeException;
use App\Exceptions\VersionControlException;
use App\Legacy\V1\Requests\Settings\MigrateRequest;
use App\Legacy\V1\Requests\Settings\UpdateRequest;
use App\Metadata\Versions\GitHubVersion;
use Illuminate\Http\Response;
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
final class UpdateController extends Controller
{
	protected ApplyUpdate $applyUpdate;

	public function __construct(ApplyUpdate $applyUpdate)
	{
		$this->applyUpdate = $applyUpdate;
	}

	/**
	 * Return if up to date or the number of commits behind
	 * This invalidates the cache for the url.
	 *
	 * @param UpdateRequest $request
	 *
	 * @return array{updateStatus: string}
	 *
	 * @throws VersionControlException
	 */
	public function check(UpdateRequest $request): array
	{
		$gitHubFunctions = resolve(GitHubVersion::class);
		$gitHubFunctions->hydrate(true, false);

		return ['updateStatus' => $gitHubFunctions->getBehindTest()];
	}

	/**
	 * Updates Lychee and returns the messages as a JSON object.
	 *
	 * The method requires PHP to have shell access.
	 * Except for the return type this method is identical to
	 * {@link UpdateController::view()}.
	 *
	 * @param UpdateRequest $request
	 *
	 * @return array{updateMsgs: array<string>}
	 *
	 * @throws LycheeException
	 */
	public function apply(UpdateRequest $request): array
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
	 * @param UpdateRequest $request
	 *
	 * @return View
	 *
	 * @throws LycheeException
	 */
	public function view(UpdateRequest $request): View
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
	 *
	 * @param MigrateRequest $request
	 *
	 * @return View|Response
	 */
	public function migrate(MigrateRequest $request): View|Response
	{
		$output = [];
		$output = $this->applyUpdate->run();

		return view('update.results', ['code' => '200', 'message' => 'Migration results', 'output' => $output]);
	}
}
