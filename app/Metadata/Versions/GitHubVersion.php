<?php

namespace App\Metadata\Versions;

use App\Contracts\Versions\VersionControl;
use App\Facades\Helpers;
use App\Metadata\Json\GitRequest;
use App\Models\Logs;
use Illuminate\Support\Facades\File;

class GitHubVersion implements VersionControl
{
	public const MASTER = 'master';

	public ?string $localBranch = null;
	public ?string $localHead = null;
	public ?string $remoteHead = null;
	public int|false $countBehind = false;
	public string $age = '';

	/**
	 * {@inheritDoc}
	 */
	public function hydrate(bool $withRemote = true, bool $useCache = true): void
	{
		$this->hydrateLocalBranch();

		$this->hydrateLocalHead();

		if ($withRemote) {
			$commits = $this->hydrateRemoteHead($useCache);

			$this->countBehind($commits);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function isMasterBranch(): bool
	{
		return $this->localBranch === self::MASTER;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isUpToDate(): bool
	{
		return $this->countBehind === 0 || $this->countBehind === false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getBehindTest(): string
	{
		return match ($this->countBehind) {
			false => 'Could not compare.',
			0 => sprintf('Up to date (%s).', $this->age),
			30 => sprintf('More than 30 commits behind master (%s).', $this->age),
			default => sprintf('%d commits behind master %s (%s)', $this->countBehind, $this->remoteHead ?? '??', $this->age ?? '??')
		};
	}

	/**
	 * {@inheritDoc}
	 */
	public function hasPermissions(): bool
	{
		return Helpers::hasFullPermissions(base_path('.git')) && (
			$this->localBranch === null ||
			Helpers::hasPermissions(base_path('.git/refs/heads/' . $this->localBranch))
		);
	}

	/**
	 * We fetch the branch head.
	 * This will return false in the case of :
	 * - .git not accessible
	 * - release.
	 *
	 * @return void
	 */
	private function hydrateLocalBranch(): void
	{
		// We get the branch name
		$branch_path = base_path('.git/HEAD');
		if (!File::exists($branch_path) &&
			!File::isReadable($branch_path)) {
			Logs::warning(__METHOD__, __LINE__, 'could not read ' . $branch_path);

			return;
		}
		$branch = File::get($branch_path);
		$branch = explode('/', $branch, 3);
		$this->localBranch = trim($branch[2]);
	}

	/**
	 * We fetch the commit head.
	 * This will return false in the case of :
	 * - .git not accessible
	 * - release.
	 *
	 * @return void
	 */
	private function hydrateLocalHead(): void
	{
		if ($this->localBranch === null) {
			return;
		}

		// We get the branch commit ID
		$commit_path = base_path('.git/refs/heads/' . $this->localBranch);
		if (!File::exists($commit_path) &&
			!File::isReadable($commit_path)) {
			Logs::warning(__METHOD__, __LINE__, 'could not read ' . $commit_path);

			return;
		}
		$commitID = File::get($commit_path);
		$this->localHead = self::trim($commitID);
	}

	/**
	 * Fetch the commits on master branch.
	 *
	 * @param bool $useCache
	 *
	 * @return array
	 */
	private function hydrateRemoteHead(bool $useCache): array
	{
		// We do not fetch when local branch is not master.
		if ($this->localBranch !== 'master') {
			return [];
		}

		$gitRequest = resolve(GitRequest::class);
		// We fetch the commits
		$commits = $gitRequest->get_json($useCache);
		if (!is_array($commits) || count($commits) === 0) {
			// if $gitData is null we already logged the problem
			return [];
		}

		$this->remoteHead = self::trim($commits[0]->sha);
		$this->age = $gitRequest->get_age_text();

		return $commits;
	}

	/**
	 * Count the number of commits between current version and master/HEAD.
	 * Do nothing if no data are available.
	 *
	 * @param array $commits fetched from github
	 */
	private function countBehind(array $commits): void
	{
		if ($this->localBranch !== 'master'
			|| $this->localHead === null
			|| count($commits) === 0
		) {
			return;
		}

		$i = 0;
		while ($i < count($commits)) {
			if (self::trim($commits[$i]->sha) === $this->localHead) {
				break;
			}
			$i++;
		}

		$this->countBehind = $i;
	}

	/**
	 * Given a commit id, return the 7 first characters (7 hex digits) and trim it to remove \n.
	 *
	 * @param string $commit_id
	 *
	 * @return string
	 */
	private static function trim(string $commit_id): string
	{
		return trim(substr($commit_id, 0, 7));
	}
}
