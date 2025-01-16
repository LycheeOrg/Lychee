<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Admin;

use App\Actions\Diagnostics\Pipes\Checks\UpdatableCheck;
use App\Actions\Diagnostics\Pipes\Infos\DockerVersionInfo;
use App\Actions\Diagnostics\Pipes\Infos\VersionInfo;
use App\Actions\InstallUpdate\ApplyUpdate;
use App\Contracts\Exceptions\LycheeException;
use App\Enum\VersionChannelType;
use App\Http\Requests\Maintenance\MigrateRequest;
use App\Http\Requests\Maintenance\UpdateRequest;
use App\Http\Resources\Diagnostics\UpdateCheckInfo;
use App\Http\Resources\Diagnostics\UpdateInfo;
use App\Metadata\Versions\GitHubVersion;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

/**
 * This module takes care of displaying updates
 * and checkimg if new versions are available.
 */
class UpdateController extends Controller
{
	protected ApplyUpdate $applyUpdate;

	public function __construct(ApplyUpdate $applyUpdate)
	{
		$this->applyUpdate = $applyUpdate;
	}

	/**
	 * Retrieve Update data from the server.
	 *
	 * @param UpdateRequest     $request
	 * @param VersionInfo       $versionInfo
	 * @param DockerVersionInfo $dockerVersionInfo
	 *
	 * @return UpdateInfo
	 */
	public function get(UpdateRequest $request, VersionInfo $versionInfo, DockerVersionInfo $dockerVersionInfo): UpdateInfo
	{
		/** @var VersionChannelType $channelName */
		$channelName = $versionInfo->getChannelName();
		$info = $versionInfo->fileVersion->getVersion()->toString();
		$extra = '';

		if ($channelName !== VersionChannelType::RELEASE) {
			if ($versionInfo->gitHubFunctions->localHead !== null) {
				$branch = $versionInfo->gitHubFunctions->localBranch ?? '??';
				$commit = $versionInfo->gitHubFunctions->localHead ?? '??';
				$info = sprintf('%s (%s)', $branch, $commit);
				$extra = $versionInfo->gitHubFunctions->getBehindTest();
			} else {
				// @codeCoverageIgnoreStart
				$info = 'No git data found.';
				// @codeCoverageIgnoreEnd
			}
		}

		return new UpdateInfo($info, $extra, $channelName, $dockerVersionInfo->isDocker());
	}

	/**
	 * Checking if any updates are available.
	 *
	 * @return UpdateCheckInfo
	 */
	public function check(UpdateRequest $request, GitHubVersion $gitHubFunctions, VersionInfo $versionInfo, DockerVersionInfo $dockerVersionInfo): UpdateCheckInfo
	{
		return new UpdateCheckInfo($gitHubFunctions->getBehindTest(), !$dockerVersionInfo->isDocker() && (!$gitHubFunctions->isUpToDate() || !$versionInfo->fileVersion->isUpToDate()));
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
	 * The whole code around installation/upgrade/migration should be
	 * thoroughly revised and refactored.
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
