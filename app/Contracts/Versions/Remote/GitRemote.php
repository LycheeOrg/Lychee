<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Contracts\Versions\Remote;

interface GitRemote
{
	/**
	 * Fetch remote data.
	 *
	 * @param bool $useCache
	 *
	 * @return object[]
	 */
	public function fetchRemote(bool $useCache): array;

	/**
	 * Count the number of elements between current version and remote HEAD.
	 * Do nothing if no data are available.
	 *
	 * @param object[] $data   fetched from github
	 * @param string   $needle
	 *
	 * @return int|false Number of elements behind or false if not available
	 */
	public function countBehind(array $data, string $needle): int|false;

	/**
	 * Get the name of the remote Head.
	 */
	public function getHead(): ?string;

	/**
	 * Get the sha of the remote Head.
	 */
	public function getHeadSha(): ?string;

	/**
	 * Get the age of the last remote query.
	 */
	public function getAgeText(): ?string;

	/**
	 * Get the type of the remote Head.
	 */
	public function getType(): string;
}
