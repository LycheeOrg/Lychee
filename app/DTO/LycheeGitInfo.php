<?php

namespace App\DTO;

use function Safe\sprintf;

class LycheeGitInfo extends DTO
{
	public string $branch;
	public string $commit;
	public string $additional;

	public function __construct(string $branch, string $commit, string $additional)
	{
		$this->branch = $branch;
		$this->commit = $commit;
		$this->additional = $additional;
	}

	public function toString(): string
	{
		$ret = sprintf('%s (%s) -- %s', $this->branch, $this->commit, $this->additional);

		return $ret;
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return [
			'branch' => $this->branch,
			'commit' => $this->commit,
			'additional' => $this->additional,
		];
	}
}
