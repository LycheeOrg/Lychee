<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Metadata\Versions;

use App\Contracts\Versions\HasIsRelease;
use App\Contracts\Versions\Remote\GitRemote;
use App\Contracts\Versions\VersionControl;
use App\Facades\Helpers;
use App\Metadata\Versions\Remote\GitCommits;
use App\Metadata\Versions\Remote\GitTags;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * GitHubVersion contains the following informations:
 * - verfies that .git is present (and subsequent required rights)
 * - the commit ID (7 hex format)
 * - wether we are a release: pinned to a commit via tag or attached to a branch.
 *
 * If we are attached to a branch then we provide the branch name.
 * If we are pinned to a a commit (detached head), we give the tag name (if available)
 *
 * Up-to-date is checked against the release data in https://api.github.com/repos/LycheeOrg/Lychee/[tags|commits]
 * This part is done via the GitRemote interface.
 */
class GitHubVersion implements VersionControl, HasIsRelease
{
	use Trimable;

	public const MASTER = 'master';

	public ?string $localBranch = null;
	public ?string $localHead = null;
	private int|false $countBehind = false;
	private ?GitRemote $remote = null;

	/**
	 * {@inheritDoc}
	 */
	public function hydrate(bool $withRemote = true, bool $useCache = true): void
	{
		// Firs we check if we are tag or commit mode.
		// If we could not even access .git/HEAD. So we probably are in file release mode.
		if (!$this->isGit()) {
			// @codeCoverageIgnoreStart
			return;
			// @codeCoverageIgnoreEnd
		}

		// Let's fetch the HEAD & branch if available.
		$this->hydrateLocalBranch();
		$this->hydrateLocalHead(); // Only if GitCommits

		if ($withRemote) {
			$this->hydrateRemote($useCache);
		}
	}

	/**
	 * We are a release if the localBranch is a tag.
	 *
	 * @return bool
	 */
	public function isRelease(): bool
	{
		return $this->remote instanceof GitTags && $this->localBranch !== null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isMasterBranch(): bool
	{
		return $this->remote instanceof GitTags || ($this->remote instanceof GitCommits && $this->localBranch === self::MASTER);
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
			// @codeCoverageIgnoreStart
			false => 'Could not compare.',
			0 => sprintf('Up to date (%s).', $this->remote->getAgeText() ?? '??'),
			30 => sprintf('More than 30 %s behind (%s).',
				$this->remote->getType(),
				$this->remote->getAgeText() ?? '??'),
			// @codeCoverageIgnoreEnd
			default => sprintf('%d %s behind %s (%s)',
				$this->countBehind,
				$this->remote->getType(),
				$this->remote->getHead() ?? '??',
				$this->remote->getAgeText() ?? '??'),
		};
	}

	/**
	 * {@inheritDoc}
	 */
	public function hasPermissions(): bool
	{
		return Helpers::hasFullPermissions(base_path('.git')) &&
			$this->remote instanceof GitCommits &&
			Helpers::hasPermissions(base_path('.git/refs/heads/' . $this->localBranch)
			);
	}

	/**
	 * Set current mode.
	 * We determines if we are in commit mode or in tags.
	 */
	private function isGit(): bool
	{
		// We get the branch name
		$branch_path = base_path('.git/HEAD');
		if (!File::exists($branch_path) &&
			!File::isReadable($branch_path)) {
			// @codeCoverageIgnoreStart
			Log::warning(__METHOD__ . ':' . __LINE__ . ' Could not read ' . $branch_path);

			return false;
			// @codeCoverageIgnoreEnd
		}

		$branch = File::get($branch_path);
		// Init remote request
		if (Str::startsWith($branch, 'ref:')) {
			$this->remote = resolve(GitCommits::class);
		} else {
			// @codeCoverageIgnoreStart
			$this->remote = resolve(GitTags::class);
			// @codeCoverageIgnoreEnd
		}

		return true;
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
		// Remote is not set: exit early
		if ($this->remote === null) {
			// @codeCoverageIgnoreStart
			return;
			// @codeCoverageIgnoreEnd
		}

		// We get the branch name
		$branch_path = base_path('.git/HEAD');
		$branchOrCommit = File::get($branch_path);

		if ($this->remote instanceof GitCommits) {
			// This is "normal" behaviour
			$branch = explode('/', $branchOrCommit, 3);
			$this->localBranch = trim($branch[2]);
		} else {
			// @codeCoverageIgnoreStart
			// This is tagged/CICD behaviour
			// we leave localBranch as null so that we know that we are not on master
			$this->localHead = $this->trim($branchOrCommit);
			// @codeCoverageIgnoreEnd
		}
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
		// Remote is not set: exit early
		if (
			$this->remote === null ||
			$this->remote instanceof GitTags ||
			$this->localBranch === null
		) {
			// @codeCoverageIgnoreStart
			return;
			// @codeCoverageIgnoreEnd
		}

		// We get the branch commit ID
		$commit_path = base_path('.git/refs/heads/' . $this->localBranch);
		if (!File::exists($commit_path) &&
			!File::isReadable($commit_path)) {
			// @codeCoverageIgnoreStart
			Log::warning(__METHOD__ . ':' . __LINE__ . ' Could not read ' . $commit_path);

			return;
			// @codeCoverageIgnoreEnd
		}
		$commitID = File::get($commit_path);
		$this->localHead = $this->trim($commitID);
	}

	/**
	 * Fetch the commits on master branch.
	 *
	 * @param bool $useCache
	 *
	 * @return void
	 *
	 * @codeCoverageIgnore the code path here depends whether you are on a PR or on master...
	 */
	private function hydrateRemote(bool $useCache): void
	{
		// We do not fetch when local branch is not master.
		// We do not fetch when the localHead is not set.
		if ($this->remote === null || $this->localHead === null || !$this->isMasterBranch()) {
			return;
		}

		$data = $this->remote->fetchRemote($useCache);
		$this->countBehind = $this->remote->countBehind($data, $this->localHead);

		if ($this->remote instanceof GitTags) {
			$this->localBranch = $this->remote->getTagName($data, $this->localHead);
		}
	}
}
