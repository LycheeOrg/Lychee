<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Metadata\Versions;

use App\Metadata\Json\CompareRequest;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * GitHubVersion contains the following informations:
 * - the local branch (null on a detached HEAD),
 * - the local commit ID (7 hex format),
 * - when on master: how many commits we are behind GitHub master.
 *
 * The distance is given by https://api.github.com/repos/LycheeOrg/Lychee/compare/<local>...master
 */
class GitHubVersion
{
	use Trimable;

	public const MASTER = 'master';

	public ?string $local_branch = null;
	public ?string $local_head = null;
	private bool $is_git = false;
	private int|false $count_behind = false;
	private ?string $age = null;

	/**
	 * Read the local git state and, if requested and on master, compare it with GitHub.
	 */
	public function hydrate(bool $with_remote = true, bool $use_cache = true): void
	{
		$this->hydrateLocal();

		if ($with_remote && $this->isMasterBranch() && $this->local_head !== null) {
			$this->hydrateRemote($use_cache);
		}
	}

	/**
	 * We are on a detached HEAD (e.g. a tag checkout).
	 */
	public function isDetached(): bool
	{
		return $this->is_git && $this->local_branch === null;
	}

	public function isMasterBranch(): bool
	{
		return $this->local_branch === self::MASTER;
	}

	/**
	 * Up to date unless we know we are behind.
	 */
	public function isUpToDate(): bool
	{
		return $this->count_behind === 0 || $this->count_behind === false;
	}

	/**
	 * Number of commits the local HEAD is behind master, false if unknown.
	 */
	public function getCountBehind(): int|false
	{
		return $this->count_behind;
	}

	public function getBehindTest(): string
	{
		return match ($this->count_behind) {
			false => 'Could not compare.',
			0 => sprintf('Up to date (%s).', $this->age ?? '??'),
			default => sprintf('%d commits behind %s (%s)', $this->count_behind, self::MASTER, $this->age ?? '??'),
		};
	}

	/**
	 * .git/HEAD contains either "ref: refs/heads/<branch>" or a commit id (detached HEAD).
	 */
	private function hydrateLocal(): void
	{
		$head = $this->readGitFile('HEAD');
		$this->is_git = $head !== null;
		if ($head === null) {
			return;
		}

		if (!Str::startsWith($head, 'ref:')) {
			$this->local_branch = null;
			$this->local_head = $this->trim($head);

			return;
		}

		$this->local_branch = trim(explode('/', $head, 3)[2]);
		$commit_id = $this->readGitFile('refs/heads/' . $this->local_branch);
		$this->local_head = $commit_id === null ? null : $this->trim($commit_id);
	}

	private function hydrateRemote(bool $use_cache): void
	{
		$request = resolve(CompareRequest::class, ['local_sha' => $this->local_head]);
		$this->count_behind = self::behindFromCompare($request->get_json($use_cache));
		$this->age = $request->get_age_text();
	}

	/**
	 * Number of commits master is ahead of the local commit, false if unknown.
	 */
	private static function behindFromCompare(mixed $json): int|false
	{
		if (!is_object($json) || !isset($json->ahead_by) || !is_int($json->ahead_by)) {
			return false;
		}

		return $json->ahead_by;
	}

	private function readGitFile(string $path): ?string
	{
		$full_path = base_path('.git/' . $path);
		if (!File::isReadable($full_path)) {
			Log::warning(__METHOD__ . ':' . __LINE__ . ' Could not read ' . $full_path);

			return null;
		}

		return File::get($full_path);
	}
}
