<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Metadata\Versions\Remote;

use App\Contracts\Versions\Remote\GitRemote;
use App\Metadata\Json\CommitsRequest;
use App\Metadata\Json\CompareRequest;
use App\Metadata\Json\JsonRequestFunctions;
use App\Metadata\Versions\Trimable;

/**
 * Here we fetch the commits, get the head, check how behind we are.
 *
 * Feched data looks like:
 * [{
 *    "sha": "403a083b35425ba76be2409b5ec7fc2ac3f7ddf7",
 *    "node_id": "C_kwDOCJTlfNoAKDQwM2EwODNiMzU0MjViYTc2YmUyNDA5YjVlYzdmYzJhYzNmN2RkZjc",
 *    "commit": {
 *      "author": {
 *        "name": "Benoît Viguier",
 *        "email": "ildyria@users.noreply.github.com",
 *        "date": "2022-12-12T19:24:28Z"
 *      },
 *      "committer": {
 *        "name": "GitHub",
 *        "email": "noreply@github.com",
 *        "date": "2022-12-12T19:24:28Z"
 *      },
 *      "message": "add downloads (#1635)",
 *  ...
 * },]
 */
class GitCommits extends AbstractGitRemote implements GitRemote
{
	use Trimable;

	/**
	 * {@inheritDoc}
	 */
	public function getType(): string
	{
		return 'commits';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getRequest(): JsonRequestFunctions
	{
		return resolve(CommitsRequest::class);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function dataToName(object $data): string
	{
		return $this->trim($data->sha);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function dataToSha(object $data): string
	{
		return $this->trim($data->sha);
	}

	/**
	 * The local commit is not in the list: it is either far behind, ahead of master,
	 * or newer than our cached list. Ask GitHub for the exact distance instead.
	 *
	 * {@inheritDoc}
	 */
	protected function countBehindNotFound(array $data, string $needle): int|false
	{
		$compare = resolve(CompareRequest::class, ['local_sha' => $needle]);

		return self::behindFromCompare($compare->get_json($this->use_cache));
	}

	/**
	 * Extract the number of commits master is ahead of the local commit.
	 */
	private static function behindFromCompare(mixed $json): int|false
	{
		if (!is_object($json) || !isset($json->ahead_by) || !is_int($json->ahead_by)) {
			return false;
		}

		return $json->ahead_by;
	}
}