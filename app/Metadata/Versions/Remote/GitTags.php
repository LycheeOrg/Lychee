<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Metadata\Versions\Remote;

use App\Contracts\Versions\Remote\GitRemote;
use App\Metadata\Json\JsonRequestFunctions;
use App\Metadata\Json\TagsRequest;
use App\Metadata\Versions\Trimable;

/**
 * Here we fetch the tags, get the head, check how behind we are.
 *
 * Feched data looks like:
 * [{
 *    "name": "v4.6.3-RC1",
 *    "zipball_url": "https://api.github.com/repos/LycheeOrg/Lychee/zipball/refs/tags/v4.6.3-RC1",
 *    "tarball_url": "https://api.github.com/repos/LycheeOrg/Lychee/tarball/refs/tags/v4.6.3-RC1",
 *    "commit": {
 *      "sha": "4fe60a1b5c2dd9dff730168062728c83089e89a2",
 *      "url": "https://api.github.com/repos/LycheeOrg/Lychee/commits/4fe60a1b5c2dd9dff730168062728c83089e89a2"
 *    },
 *    "node_id": "MDM6UmVmMTQzOTc1ODA0OnJlZnMvdGFncy92NC42LjMtUkMx"
 *  },]
 */
class GitTags extends AbstractGitRemote implements GitRemote
{
	use Trimable;

	/**
	 * {@inheritDoc}
	 */
	public function getType(): string
	{
		return 'tags';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getRequest(): JsonRequestFunctions
	{
		return resolve(TagsRequest::class);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function dataToName(object $data): string
	{
		// Tags have a name
		return $data->name;  // @phpstan-ignore-line : Access to an undefined property object::$name
	}

	/**
	 * {@inheritDoc}
	 */
	protected function dataToSha(object $data): string
	{
		// In this specific case we
		return $this->trim($data->commit->sha);  // @phpstan-ignore-line : Access to an undefined property object::$commit
	}

	/**
	 * Given array and sha returns the name of the tag associated to the sha.
	 *
	 * @param object[] $data
	 * @param string   $sha
	 *
	 * @return string
	 */
	public function getTagName(array $data, string $sha): string
	{
		foreach ($data as $d) {
			if ($this->dataToSha($d) === $sha) {
				return $this->dataToName($d);
			}
		}

		return '';
	}
}