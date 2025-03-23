<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Actions\Diagnostics\Pipes\Infos\DockerVersionInfo;
use App\Contracts\DiagnosticPipe;
use App\DTO\DiagnosticData;
use App\Exceptions\ConfigurationException;
use App\Exceptions\ExternalComponentMissingException;
use App\Exceptions\InsufficientFilesystemPermissions;
use App\Exceptions\VersionControlException;
use App\Facades\Helpers;
use App\Metadata\Versions\GitHubVersion;
use App\Metadata\Versions\InstalledVersion;
use App\Models\Configs;
use Illuminate\Support\Facades\Schema;
use function Safe\exec;

/**
 * Check whether or not it is possible to update this installation.
 */
class UpdatableCheck implements DiagnosticPipe
{
	/**
	 * @param InstalledVersion $installedVersion
	 */
	public function __construct(
		private InstalledVersion $installed_version,
		private DockerVersionInfo $docker_version_info,
	) {
	}

	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
	{
		if (!$this->installed_version->isRelease() && !$this->docker_version_info->isDocker()) {
			try {
				self::assertUpdatability();
				// @codeCoverageIgnoreStart
			} catch (ExternalComponentMissingException $e) {
				$data[] = DiagnosticData::info($e->getMessage(), self::class);
			} catch (ConfigurationException $e) {
				$data[] = DiagnosticData::warn($e->getMessage(), self::class);
			} catch (InsufficientFilesystemPermissions|VersionControlException $e) {
				$data[] = DiagnosticData::error($e->getMessage(), self::class);
			}
			// @codeCoverageIgnoreEnd
		}

		return $next($data);
	}

	/**
	 * Here we throw an exception if we cannot apply an update.
	 *
	 * @return void
	 */
	public static function assertUpdatability(): void
	{
		$installed_version = resolve(InstalledVersion::class);

		// we bypass this because we don't care about the other conditions as they don't apply to the release
		if ($installed_version->isRelease()) {
			// @codeCoverageIgnoreStart
			return;
			// @codeCoverageIgnoreEnd
		}
		if (!Schema::hasTable('configs')) {
			// @codeCoverageIgnoreStart
			throw new ConfigurationException('Migration is not run');
			// @codeCoverageIgnoreEnd
		}

		if (!Configs::getValueAsBool('allow_online_git_pull')) {
			throw new ConfigurationException('Online updates are disabled by configuration');
		}

		// When going with the CI, .git is always executable
		if (Helpers::isExecAvailable() && exec('command -v git') === '') {
			// @codeCoverageIgnoreStart
			throw new ExternalComponentMissingException('git (software) is not available.');
			// @codeCoverageIgnoreEnd
		}

		$git_hub_functions = resolve(GitHubVersion::class);
		$git_hub_functions->hydrate(false);

		if (!$git_hub_functions->hasPermissions()) {
			// @codeCoverageIgnoreStart
			throw new InsufficientFilesystemPermissions(Helpers::censor(base_path('.git'), 1 / 4) . ' (and subdirectories) are not executable, check the permissions');
			// @codeCoverageIgnoreEnd
		}
	}
}