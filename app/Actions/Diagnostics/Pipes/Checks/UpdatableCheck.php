<?php

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use App\Exceptions\ConfigurationException;
use App\Exceptions\ExternalComponentMissingException;
use App\Exceptions\InsufficientFilesystemPermissions;
use App\Exceptions\VersionControlException;
use App\Facades\Helpers;
use App\Metadata\Versions\GitHubVersion;
use App\Metadata\Versions\LycheeVersion;
use App\Models\Configs;
use function Safe\exec;

class UpdatableCheck implements DiagnosticPipe
{
	private LycheeVersion $lycheeVersion;

	/**
	 * @param LycheeVersion $lycheeVersion
	 */
	public function __construct(
		LycheeVersion $lycheeVersion
	) {
		$this->lycheeVersion = $lycheeVersion;
	}

	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
	{
		if (!$this->lycheeVersion->isRelease()) {
			try {
				self::assertUpdatability();
				// @codeCoverageIgnoreStart
			} catch (ConfigurationException|ExternalComponentMissingException $e) {
				$data[] = 'Warning: ' . $e->getMessage();
			} catch (InsufficientFilesystemPermissions|VersionControlException $e) {
				$data[] = 'Error: ' . $e->getMessage();
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
		$lycheeVersion = resolve(LycheeVersion::class);

		// we bypass this because we don't care about the other conditions as they don't apply to the release
		if ($lycheeVersion->isRelease()) {
			// @codeCoverageIgnoreStart
			return;
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

		$gitHubFunctions = resolve(GitHubVersion::class);
		$gitHubFunctions->hydrate(false);

		if (!$gitHubFunctions->hasPermissions()) {
			// @codeCoverageIgnoreStart
			throw new InsufficientFilesystemPermissions(base_path('.git') . ' (and subdirectories) are not executable, check the permissions');
			// @codeCoverageIgnoreEnd
		}
	}
}