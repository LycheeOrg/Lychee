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
	public function __construct(protected ApplyUpdate $apply_update)
	{
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
	public function get(UpdateRequest $request, VersionInfo $version_info, DockerVersionInfo $docker_version_info): UpdateInfo
	{
		/** @var VersionChannelType $channelName */
		$channel_name = $version_info->getChannelName();
		$info = $version_info->file_version->getVersion()->toString();
		$extra = '';

		if ($channel_name !== VersionChannelType::RELEASE) {
			if ($version_info->github_functions->local_head !== null) {
				$branch = $version_info->github_functions->local_branch ?? '??';
				$commit = $version_info->github_functions->local_head ?? '??';
				$info = sprintf('%s (%s)', $branch, $commit);
				$extra = $version_info->github_functions->getBehindTest();
			} else {
				// @codeCoverageIgnoreStart
				$info = 'No git data found.';
				// @codeCoverageIgnoreEnd
			}
		}

		return new UpdateInfo($info, $extra, $channel_name, $docker_version_info->isDocker());
	}

	/**
	 * Checking if any updates are available.
	 *
	 * @return UpdateCheckInfo
	 */
	public function check(UpdateRequest $request, GitHubVersion $git_hub_functions, VersionInfo $version_info, DockerVersionInfo $docker_version_info): UpdateCheckInfo
	{
		return new UpdateCheckInfo($git_hub_functions->getBehindTest(), !$docker_version_info->isDocker() && (!$git_hub_functions->isUpToDate() || !$version_info->file_version->isUpToDate()));
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

		return ['updateMsgs' => $this->apply_update->run()];
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

		$output = $this->apply_update->run();

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
		$output = $this->apply_update->run();

		return view('update.results', ['code' => '200', 'message' => 'Migration results', 'output' => $output]);
	}
}
