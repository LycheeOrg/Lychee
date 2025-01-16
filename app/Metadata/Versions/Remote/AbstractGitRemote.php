<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Metadata\Versions\Remote;

use App\Contracts\Versions\Remote\GitRemote;
use App\Metadata\Json\JsonRequestFunctions;

abstract class AbstractGitRemote implements GitRemote
{
	protected ?string $head = null;
	protected ?string $headSha = null;
	protected ?string $age = null;

	/**
	 * Get the request object.
	 *
	 * @return JsonRequestFunctions
	 */
	abstract protected function getRequest(): JsonRequestFunctions;

	/**
	 * Given a data return the associated name for the remote HEAD info.
	 *
	 * @param object $data
	 *
	 * @return string
	 */
	abstract protected function dataToName(object $data): string;

	/**
	 * Given a data returns the associated sha commit.
	 *
	 * @param object $data
	 *
	 * @return string
	 */
	abstract protected function dataToSha(object $data): string;

	/**
	 * {@inheritDoc}
	 */
	public function fetchRemote(bool $useCache): array
	{
		$request = $this->getRequest();

		// We fetch the commits
		$data = $request->get_json($useCache);
		if (!is_array($data) || count($data) === 0) {
			// if $gitData is null we already logged the problem
			// @codeCoverageIgnoreStart
			return [];
			// @codeCoverageIgnoreEnd
		}

		$this->head = $this->dataToName($data[0]);
		$this->headSha = $this->dataToSha($data[0]);
		$this->age = $request->get_age_text();

		return $data;
	}

	/**
	 * {@inheritDoc}
	 */
	public function countBehind(array $data, string $needle): int|false
	{
		if (count($data) === 0) {
			return false;
		}

		$i = 0;
		while ($i < count($data)) {
			if ($this->dataToSha($data[$i]) === $needle) {
				return $i;
			}
			$i++;
		}

		return $i;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @codeCoverageIgnore
	 */
	public function getAgeText(): string
	{
		return $this->age;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @codeCoverageIgnore
	 */
	public function getHead(): ?string
	{
		return $this->head;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @codeCoverageIgnore
	 */
	public function getHeadSha(): ?string
	{
		return $this->headSha;
	}
}